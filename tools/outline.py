#!/usr/bin/env python3
"""SingleFile の HTML から、body の DOM アウトラインを読める形で出力する。

extract_structure.py が「何が使われているか」を出すのに対し、
こちらは「どう組まれているか」を出す。レイアウト再現時に使う。

使い方:
    python3 tools/outline.py reference/01-home.html
    python3 tools/outline.py reference/01-home.html --depth 12
    python3 tools/outline.py reference/01-home.html --grep jaycee
"""

from __future__ import annotations

import argparse
import re
import sys
from html.parser import HTMLParser
from pathlib import Path

DATA_URI_RE = re.compile(r"data:[a-zA-Z0-9.+/-]*;base64,[A-Za-z0-9+/=\s]{80,}")

VOID_TAGS = {
    "area", "base", "br", "col", "embed", "hr", "img", "input",
    "link", "meta", "param", "source", "track", "wbr",
}
DROP_TAGS = {"script", "style", "noscript", "svg", "path", "template", "head"}
# 構造把握に効かないインライン要素は畳む
INLINE_TAGS = {"span", "b", "i", "em", "strong", "small", "br", "sup", "sub"}


def squash(text: str) -> str:
    return re.sub(r"\s+", " ", text).strip()


class Node:
    __slots__ = ("tag", "attrs", "children", "text")

    def __init__(self, tag: str, attrs: dict) -> None:
        self.tag = tag
        self.attrs = attrs
        self.children: list[Node] = []
        self.text: list[str] = []

    def own_text(self) -> str:
        return squash(" ".join(self.text))

    def all_text(self) -> str:
        parts = list(self.text)
        for c in self.children:
            parts.append(c.all_text())
        return squash(" ".join(parts))


class OutlineParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.root = Node("#root", {})
        self.stack = [self.root]
        self.drop_depth = 0

    def handle_starttag(self, tag, attrs_list):
        if self.drop_depth:
            if tag not in VOID_TAGS:
                self.drop_depth += 1
            return
        if tag in DROP_TAGS:
            if tag not in VOID_TAGS:
                self.drop_depth = 1
            return

        attrs = {k.lower(): (v or "") for k, v in attrs_list}
        node = Node(tag, attrs)
        self.stack[-1].children.append(node)
        if tag not in VOID_TAGS:
            self.stack.append(node)

    def handle_endtag(self, tag):
        if self.drop_depth:
            self.drop_depth -= 1
            return
        if tag in VOID_TAGS:
            return
        for i in range(len(self.stack) - 1, 0, -1):
            if self.stack[i].tag == tag:
                del self.stack[i:]
                return

    def handle_data(self, data):
        if self.drop_depth:
            return
        if data.strip():
            self.stack[-1].text.append(data)


def label(node: Node) -> str:
    """1ノードを1行で表す。"""
    out = node.tag
    cls = node.attrs.get("class", "").strip()
    if cls:
        # Elementor の自動生成クラスはノイズなので削る
        keep = [c for c in cls.split()
                if not re.fullmatch(r"elementor-element-[0-9a-f]+", c)
                and not re.fullmatch(r"e-con-(boxed|full)", c)]
        if keep:
            out += "." + ".".join(keep[:4])
            if len(keep) > 4:
                out += f"(+{len(keep) - 4})"

    wt = node.attrs.get("data-widget_type")
    if wt:
        out += f"  «{wt}»"
    et = node.attrs.get("data-elementor-type")
    if et:
        out += f"  «template:{et}»"

    if node.tag == "img":
        src = node.attrs.get("src", "")
        src = "(inline)" if src.startswith("data:") else src.rsplit("/", 1)[-1]
        out += f'  src={src} alt="{node.attrs.get("alt", "")}"'
    if node.tag == "a":
        out += f'  href={node.attrs.get("href", "")[:70]}'

    txt = node.own_text()
    if txt:
        out += f'  "{txt[:70]}"'
    return out


def is_noise(node: Node) -> bool:
    """中身も意味も無いノードは畳む。"""
    if node.tag in INLINE_TAGS and not node.children:
        return True
    return False


def render(node: Node, depth: int, max_depth: int, out: list[str],
           grep: str | None) -> None:
    for child in node.children:
        if is_noise(child):
            continue
        line = label(child)
        if grep is None or grep in line or grep in child.all_text():
            out.append("  " * depth + line)
        if depth < max_depth:
            render(child, depth + 1, max_depth, out, grep)


def main(argv: list[str]) -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("file")
    ap.add_argument("--depth", type=int, default=10)
    ap.add_argument("--grep", default=None,
                    help="この文字列を含む行とその配下だけ表示")
    args = ap.parse_args(argv[1:])

    path = Path(args.file)
    if not path.is_file():
        print(f"見つかりません: {path}", file=sys.stderr)
        return 1

    text = DATA_URI_RE.sub("data:(stripped)", path.read_text(
        encoding="utf-8", errors="replace"))

    parser = OutlineParser()
    parser.feed(text)
    parser.close()

    out: list[str] = []
    render(parser.root, 0, args.depth, out, args.grep)
    print("\n".join(out))
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
