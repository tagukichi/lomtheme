#!/usr/bin/env python3
"""SingleFile で保存した HTML から WordPress サイトの構造情報を抽出する。

SingleFile は画像・フォントを base64 データURIとして HTML に埋め込むため、
1ファイルが数MBになり、そのままでは読めない。このスクリプトは
データURIを捨てた上で、テーマ化に必要な情報だけを抜き出す。

使い方:
    python3 tools/extract_structure.py reference/
    python3 tools/extract_structure.py reference/home.html

出力:
    docs/site-analysis/<名前>.md    ページごとのレポート
    docs/site-analysis/_summary.md  全ページ横断のサマリ（実質のサイトマップ）
"""

from __future__ import annotations

import re
import sys
from collections import Counter, OrderedDict
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import urlparse

# --- 事前に潰しておく巨大なノイズ -------------------------------------------

DATA_URI_RE = re.compile(r"data:[a-zA-Z0-9.+/-]*;base64,[A-Za-z0-9+/=\s]{80,}")

# --- サイト内の指紋 ---------------------------------------------------------

PLUGIN_RE = re.compile(r"/wp-content/plugins/([A-Za-z0-9_.-]+)")
THEME_RE = re.compile(r"/wp-content/themes/([A-Za-z0-9_.-]+)")
SINGLEFILE_URL_RE = re.compile(r"^\s*url:\s*(\S+)\s*$", re.MULTILINE)
GENERATOR_RE = re.compile(r'name=["\']generator["\'][^>]*content=["\']([^"\']+)', re.I)

# SingleFile は CSS/JS をインライン化するため /wp-content/plugins/ のパスが消える。
# 代わりに WordPress の enqueue ハンドル（style/script の id 属性）が残るので、
# そこからプラグインを割り出す。
HANDLE_SUFFIX_RE = re.compile(
    r"-(?:inline-)?(?:css|js)(?:-extra|-before|-after|-translations)?$"
)

# 投稿パーマリンクの構造推定に使う
PERMALINK_PATTERNS = [
    (re.compile(r"^/\d{4}/\d{2}/\d{2}/[^/]+/?$"), "/%year%/%monthnum%/%day%/%postname%/"),
    (re.compile(r"^/\d{2}/\d{2}/[^/]+/?$"), "/%monthnum%/%day%/%postname%/"),
    (re.compile(r"^/\d{4}/\d{2}/[^/]+/?$"), "/%year%/%monthnum%/%postname%/"),
    (re.compile(r"^/archives/\d+/?$"), "/archives/%post_id%/"),
    (re.compile(r"^/\?p=\d+$"), "デフォルト（?p=ID）"),
]

# WordPress コア由来のハンドル。プラグイン特定のノイズになるので分けて表示する。
CORE_HANDLE_PREFIXES = (
    "wp-", "admin-bar", "dashicons", "jquery", "classic-theme-styles",
    "global-styles", "wp-emoji", "regenerator-runtime", "moment",
)


def normalize_handle(handle: str) -> str:
    return HANDLE_SUFFIX_RE.sub("", handle)


def is_core_handle(handle: str) -> bool:
    return handle.startswith(CORE_HANDLE_PREFIXES)

VOID_TAGS = {
    "area", "base", "br", "col", "embed", "hr", "img", "input",
    "link", "meta", "param", "source", "track", "wbr",
}
SKIP_TEXT_TAGS = {"script", "style", "noscript", "svg", "template"}
HEADING_TAGS = {"h1", "h2", "h3", "h4", "h5", "h6"}


def squash(text: str) -> str:
    return re.sub(r"\s+", " ", text).strip()


class SiteParser(HTMLParser):
    """テーマ化の判断に効く要素だけを拾う、寛容なパーサ。"""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.stack: list[tuple[str, dict]] = []
        self.title = ""
        self.meta: dict[str, str] = {}
        self.body_classes: list[str] = []
        self.headings: list[tuple[int, str]] = []
        self.links: list[tuple[str, str, bool]] = []  # (href, text, in_nav)
        self.images: list[tuple[str, str]] = []       # (src, alt)
        self.widgets: Counter = Counter()             # Elementor ウィジェット種別
        self.el_templates: Counter = Counter()        # Elementor テンプレート種別
        self.forms: list[dict] = []
        self.shortcode_hosts: Counter = Counter()
        self.asset_handles: list[str] = []

        self._heading: dict | None = None
        self._link: dict | None = None
        self._in_title = False

    # -- 補助 ---------------------------------------------------------------

    def _skipping(self) -> bool:
        return any(tag in SKIP_TEXT_TAGS for tag, _ in self.stack)

    def _in_nav(self) -> bool:
        for tag, attrs in self.stack:
            if tag in ("nav", "header"):
                return True
            if attrs.get("role") == "navigation":
                return True
            cls = attrs.get("class", "")
            if "menu" in cls or "nav" in cls:
                return True
        return False

    def _pop_to(self, tag: str) -> None:
        for i in range(len(self.stack) - 1, -1, -1):
            if self.stack[i][0] == tag:
                del self.stack[i:]
                return
        # 対応する開始タグが無ければ何もしない（壊れたHTMLへの保険）

    # -- HTMLParser のフック -------------------------------------------------

    def handle_starttag(self, tag: str, attrs_list) -> None:
        attrs = {k.lower(): (v or "") for k, v in attrs_list}

        if tag == "title":
            self._in_title = True
        elif tag == "body":
            self.body_classes = attrs.get("class", "").split()
        elif tag == "meta":
            key = attrs.get("name") or attrs.get("property")
            if key and "content" in attrs:
                self.meta.setdefault(key.lower(), attrs["content"])
        elif tag == "link" and attrs.get("rel") == "canonical":
            self.meta.setdefault("canonical", attrs.get("href", ""))
        elif tag == "img":
            src = attrs.get("src", "")
            if src.startswith("data:"):
                src = "(inline)"
            self.images.append((src, attrs.get("alt", "")))
        elif tag == "form":
            self.forms.append({
                "action": attrs.get("action", ""),
                "class": attrs.get("class", ""),
                "id": attrs.get("id", ""),
                "fields": [],
            })
        elif tag in ("input", "select", "textarea") and self.forms:
            name = attrs.get("name", "")
            if name:
                self.forms[-1]["fields"].append(
                    f"{name} ({attrs.get('type', tag)})"
                )
        elif tag in HEADING_TAGS:
            self._heading = {"level": int(tag[1]), "parts": []}
        elif tag == "a":
            self._link = {"href": attrs.get("href", ""), "parts": [],
                          "nav": self._in_nav()}

        if tag in ("style", "script", "link") and attrs.get("id"):
            self.asset_handles.append(normalize_handle(attrs["id"]))

        if "data-widget_type" in attrs:
            self.widgets[attrs["data-widget_type"]] += 1
        if "data-elementor-type" in attrs:
            self.el_templates[attrs["data-elementor-type"]] += 1

        # ショートコード由来のラッパを拾う（CF7 / MW WP Form など）
        cls = attrs.get("class", "")
        for marker in ("wpcf7", "mw_wp_form", "wpforms", "snow-monkey",
                       "swell", "biz-calendar", "tribe-events", "vk_"):
            if marker in cls:
                self.shortcode_hosts[marker] += 1

        if tag not in VOID_TAGS:
            self.stack.append((tag, attrs))

    def handle_endtag(self, tag: str) -> None:
        if tag == "title":
            self._in_title = False
        elif tag in HEADING_TAGS and self._heading:
            text = squash("".join(self._heading["parts"]))
            if text:
                self.headings.append((self._heading["level"], text))
            self._heading = None
        elif tag == "a" and self._link:
            self.links.append((
                self._link["href"],
                squash("".join(self._link["parts"])),
                self._link["nav"],
            ))
            self._link = None

        if tag not in VOID_TAGS:
            self._pop_to(tag)

    def handle_data(self, data: str) -> None:
        if self._in_title:
            self.title += data
            return
        if self._skipping():
            return
        if self._heading is not None:
            self._heading["parts"].append(data)
        if self._link is not None:
            self._link["parts"].append(data)


class PageReport:
    def __init__(self, path: Path, raw: str) -> None:
        self.path = path
        text = DATA_URI_RE.sub("data:(stripped)", raw)

        self.source_url = ""
        m = SINGLEFILE_URL_RE.search(text[:4000])
        if m:
            self.source_url = m.group(1)

        self.generators = sorted(set(GENERATOR_RE.findall(text)))

        parser = SiteParser()
        parser.feed(text)
        parser.close()
        self.p = parser

        # テーマ名は body class の wp-theme-* からも取れる（SingleFile でパスが
        # 消えてもこちらは残る）
        themes = set(THEME_RE.findall(text))
        for cls in parser.body_classes:
            if cls.startswith("wp-theme-"):
                themes.add(cls[len("wp-theme-"):])
            elif cls.startswith("wp-child-theme-"):
                themes.add(cls[len("wp-child-theme-"):] + "（子テーマ）")
        self.themes = sorted(themes)

        self.plugins = sorted(set(PLUGIN_RE.findall(text)))

        handles = sorted(set(h for h in parser.asset_handles if h))
        self.core_handles = [h for h in handles if is_core_handle(h)]
        self.other_handles = [h for h in handles if not is_core_handle(h)]

        # elementor-kit-N / elementor-page-N など、識別に効く body class
        self.el_ids = [c for c in parser.body_classes
                       if c.startswith(("elementor-kit-", "elementor-page-",
                                        "page-id-", "postid-"))]

        self.url = (self.source_url or parser.meta.get("canonical")
                    or parser.meta.get("og:url") or "")
        self.host = urlparse(self.url).netloc if self.url else ""

    # -- リンク分類 ----------------------------------------------------------

    def internal_links(self) -> list[str]:
        out = set()
        for href, _text, _nav in self.p.links:
            if not href or href.startswith(("#", "mailto:", "tel:", "javascript:")):
                continue
            parsed = urlparse(href)
            if parsed.scheme and parsed.scheme not in ("http", "https"):
                continue
            if parsed.netloc and self.host and parsed.netloc != self.host:
                continue
            if not parsed.netloc and not href.startswith("/"):
                continue
            out.add(parsed._replace(fragment="").geturl())
        return sorted(out)

    def external_domains(self) -> Counter:
        c: Counter = Counter()
        for href, _text, _nav in self.p.links:
            parsed = urlparse(href)
            if parsed.netloc and parsed.netloc != self.host:
                c[parsed.netloc] += 1
        return c

    def nav_items(self) -> list[tuple[str, str]]:
        seen: OrderedDict[tuple[str, str], None] = OrderedDict()
        for href, text, nav in self.p.links:
            if nav and text:
                seen.setdefault((text, href), None)
        return list(seen.keys())

    # -- 出力 ----------------------------------------------------------------

    def to_markdown(self) -> str:
        p = self.p
        L: list[str] = [f"# {self.path.name}", ""]
        L.append(f"- 取得元URL: `{self.url or '不明'}`")
        L.append(f"- `<title>`: {squash(p.title) or '(なし)'}")
        desc = p.meta.get("description", "")
        L.append(f"- meta description: {squash(desc) or '(なし)'}")
        if self.generators:
            L.append(f"- generator: {', '.join(self.generators)}")
        if self.themes:
            L.append(f"- テーマ: {', '.join(self.themes)}")
        L.append(f"- body class: `{' '.join(p.body_classes) or '(なし)'}`")
        L.append("")

        def section(title: str, lines: list[str]) -> None:
            L.append(f"## {title}")
            L.extend(lines if lines else ["(なし)"])
            L.append("")

        section("読み込みハンドル（プラグイン特定用・コア以外）",
                [f"- `{x}`" for x in self.other_handles])

        section("検出プラグイン（アセットパス由来）",
                [f"- `{x}`" for x in self.plugins])

        section("Elementor テンプレート種別",
                [f"- `{k}` × {v}" for k, v in p.el_templates.most_common()])

        section("Elementor ウィジェット",
                [f"- `{k}` × {v}" for k, v in p.widgets.most_common()])

        section("その他の指紋",
                [f"- `{k}` × {v}" for k, v in p.shortcode_hosts.most_common()])

        section("見出し構成",
                [f"{'  ' * (lv - 1)}- h{lv}: {txt}" for lv, txt in p.headings])

        nav = self.nav_items()
        section("ナビゲーション", [f"- {t} → `{h}`" for t, h in nav])

        forms = []
        for f in p.forms:
            forms.append(f"- action=`{f['action'] or '(自身)'}` "
                         f"class=`{f['class']}` id=`{f['id']}`")
            for field in f["fields"]:
                forms.append(f"  - {field}")
        section("フォーム", forms)

        section("内部リンク", [f"- `{u}`" for u in self.internal_links()])

        section("外部ドメイン",
                [f"- {d} × {n}" for d, n in self.external_domains().most_common()])

        imgs = [f"- `{src.rsplit('/', 1)[-1]}` alt=\"{alt}\""
                for src, alt in p.images]
        L.append(f"## 画像（{len(p.images)}点）")
        L.extend(imgs if imgs else ["(なし)"])
        L.append("")
        no_alt = sum(1 for _s, a in p.images if not a.strip())
        L.append(f"> alt未設定: {no_alt} / {len(p.images)}")
        L.append("")
        return "\n".join(L)


def build_summary(reports: list[PageReport]) -> str:
    plugins: set[str] = set()
    themes: set[str] = set()
    handles: set[str] = set()
    widgets: Counter = Counter()
    templates: Counter = Counter()
    fingerprints: Counter = Counter()
    all_internal: set[str] = set()
    captured: set[str] = set()

    for r in reports:
        plugins.update(r.plugins)
        themes.update(r.themes)
        handles.update(r.other_handles)
        widgets.update(r.p.widgets)
        templates.update(r.p.el_templates)
        fingerprints.update(r.p.shortcode_hosts)
        all_internal.update(r.internal_links())
        if r.url:
            captured.add(r.url.rstrip("/"))

    # 投稿URLからパーマリンク構造を推定する
    permalinks: Counter = Counter()
    for url in all_internal:
        path = urlparse(url).path
        for pattern, label in PERMALINK_PATTERNS:
            if pattern.match(path):
                permalinks[label] += 1
                break

    L = ["# 既存サイト解析サマリ", "",
         f"解析ページ数: {len(reports)}", ""]

    L.append("## ページ一覧（取り込み済み）")
    for r in reports:
        L.append(f"- `{r.path.name}` — {squash(r.p.title) or '(タイトルなし)'} "
                 f"— {r.url or 'URL不明'}")
    L.append("")

    L.append("## テーマ")
    L.extend([f"- `{x}`" for x in sorted(themes)] or ["(なし)"])
    L.append("")

    L.append("## 読み込みハンドル（コア以外・全ページ横断）")
    L.append("")
    L.append("SingleFile は CSS/JS をインライン化するためプラグインのパスが消える。")
    L.append("代わりに残る enqueue ハンドルから、使用プラグインを推定する。")
    L.append("")
    L.extend([f"- `{x}`" for x in sorted(handles)] or ["(なし)"])
    L.append("")

    L.append("## 検出プラグイン（アセットパス由来）")
    L.extend([f"- `{x}`" for x in sorted(plugins)] or ["(なし)"])
    L.append("")

    L.append("## 投稿パーマリンク構造（推定）")
    L.extend([f"- `{k}` — 該当URL {v}件" for k, v in permalinks.most_common()]
             or ["(判定できず)"])
    L.append("")

    L.append("## Elementor テンプレート種別")
    L.extend([f"- `{k}` × {v}" for k, v in templates.most_common()] or ["(なし)"])
    L.append("")

    L.append("## Elementor ウィジェット使用頻度")
    L.extend([f"- `{k}` × {v}" for k, v in widgets.most_common()] or ["(なし)"])
    L.append("")

    L.append("## その他の指紋")
    L.extend([f"- `{k}` × {v}" for k, v in fingerprints.most_common()] or ["(なし)"])
    L.append("")

    L.append("## 発見された内部URL（実質のサイトマップ）")
    L.append("")
    L.append("`*` は未取得のページ。テーマ化の抜け漏れ確認に使う。")
    L.append("")
    for u in sorted(all_internal):
        mark = "" if u.rstrip("/") in captured else " *"
        L.append(f"- `{u}`{mark}")
    L.append("")
    return "\n".join(L)


def main(argv: list[str]) -> int:
    if len(argv) < 2:
        print(__doc__)
        return 1

    target = Path(argv[1])
    if target.is_dir():
        files = sorted(p for p in target.rglob("*.htm*") if p.is_file())
    elif target.is_file():
        files = [target]
    else:
        print(f"見つかりません: {target}", file=sys.stderr)
        return 1

    if not files:
        print(f"HTMLがありません: {target}", file=sys.stderr)
        return 1

    out_dir = Path("docs/site-analysis")
    out_dir.mkdir(parents=True, exist_ok=True)

    reports = []
    for f in files:
        raw = f.read_text(encoding="utf-8", errors="replace")
        report = PageReport(f, raw)
        reports.append(report)
        dest = out_dir / (f.stem + ".md")
        dest.write_text(report.to_markdown(), encoding="utf-8")
        print(f"{f}  ->  {dest}  "
              f"(見出し{len(report.p.headings)} リンク{len(report.p.links)} "
              f"画像{len(report.p.images)})")

    summary = out_dir / "_summary.md"
    summary.write_text(build_summary(reports), encoding="utf-8")
    print(f"\nサマリ: {summary}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
