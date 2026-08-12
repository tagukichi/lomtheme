#!/usr/bin/env python3
"""静的サイトの検査。

ページを増やしたり編集したあとに実行する。
ビルド工程は無いので、壊れても誰も教えてくれない。代わりにこれが見る。

    python3 tools/check_html.py html/

検査する内容
    - <html lang> と <title> があるか
    - h1 がちょうど1つか（複数あると読み上げ順序と検索結果が壊れる）
    - img に alt があるか
    - タグの対応が取れているか
    - 内部リンクと画像の参照先が実在するか
    - label の for に対応する id があるか
    - 同じ id が重複していないか
"""

from __future__ import annotations

import sys
from html.parser import HTMLParser
from pathlib import Path

# 終了タグを持たない要素。SVG の図形要素は自己終了で書かれるため、
# HTMLParser が start と end を両方通知してくる。数えないようにする。
VOID = {
    "area", "base", "br", "col", "embed", "hr", "img", "input", "link",
    "meta", "param", "source", "track", "wbr",
    "path", "circle", "rect", "line", "polygon", "polyline", "ellipse",
    "stop", "use",
}

SKIP_LINK_PREFIX = ("http://", "https://", "#", "mailto:", "tel:", "data:", "javascript:")


class PageParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.lang: str | None = None
        self.titles = 0
        self.h1 = 0
        self.refs: list[str] = []
        self.img_missing_alt = 0
        self.ids: list[str] = []
        self.label_for: list[str] = []
        self.stack: list[str] = []
        self.errors: list[str] = []

    def handle_starttag(self, tag: str, attrs) -> None:
        a = dict(attrs)

        if tag == "html":
            self.lang = a.get("lang")
        elif tag == "title":
            self.titles += 1
        elif tag == "h1":
            self.h1 += 1
        elif tag == "img":
            self.refs.append(a.get("src", ""))
            if "alt" not in a:
                self.img_missing_alt += 1
        elif tag in ("a", "link") and a.get("href"):
            self.refs.append(a["href"])
        elif tag == "script" and a.get("src"):
            self.refs.append(a["src"])
        elif tag == "label" and a.get("for"):
            self.label_for.append(a["for"])

        if a.get("id"):
            self.ids.append(a["id"])

        if tag not in VOID:
            self.stack.append(tag)

    def handle_endtag(self, tag: str) -> None:
        if tag in VOID:
            return
        for i in range(len(self.stack) - 1, -1, -1):
            if self.stack[i] == tag:
                if i != len(self.stack) - 1:
                    self.errors.append(
                        f"<{tag}> を閉じる前に {self.stack[i + 1:]} が閉じられていない"
                    )
                del self.stack[i:]
                return
        self.errors.append(f"対応する開始タグの無い </{tag}>")


def check(path: Path, root: Path) -> list[str]:
    parser = PageParser()
    parser.feed(path.read_text(encoding="utf-8"))
    parser.close()

    out = list(parser.errors)

    if parser.lang != "ja":
        out.append(f"<html lang> が ja ではない（{parser.lang}）")
    if parser.titles != 1:
        out.append(f"<title> が {parser.titles} 個")
    if parser.h1 != 1:
        out.append(f"h1 が {parser.h1} 個（1つにする）")
    if parser.img_missing_alt:
        out.append(f"alt の無い img が {parser.img_missing_alt} 個")
    if parser.stack:
        out.append(f"閉じられていないタグ {parser.stack}")

    dup = {i for i in parser.ids if parser.ids.count(i) > 1}
    if dup:
        out.append(f"id の重複 {sorted(dup)}")

    for target in parser.label_for:
        if target not in parser.ids:
            out.append(f'label for="{target}" に対応する id が無い')

    for ref in parser.refs:
        if not ref or ref.startswith(SKIP_LINK_PREFIX):
            continue
        target = (root / ref.split("#")[0].split("?")[0]).resolve()
        if not target.exists():
            out.append(f"参照先が無い {ref}")

    return out


def main(argv: list[str]) -> int:
    root = Path(argv[1] if len(argv) > 1 else "html")
    if not root.is_dir():
        print(f"ディレクトリがありません: {root}", file=sys.stderr)
        return 1

    pages = sorted(root.glob("*.html"))
    if not pages:
        print(f"HTMLがありません: {root}", file=sys.stderr)
        return 1

    failed = 0
    for page in pages:
        problems = check(page, root)
        if problems:
            failed += 1
            print(f"✗ {page.name}")
            for problem in problems:
                print(f"    {problem}")
        else:
            print(f"✓ {page.name}")

    print()
    if failed:
        print(f"{failed} / {len(pages)} ページに問題があります")
        return 1

    print(f"{len(pages)} ページすべて問題なし")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
