# LOM Theme（仮称）

日本青年会議所（JC）各 LOM 向けのウェブサイト。
これまで手掛けてきた JC サイト制作のノウハウを1つにまとめ、
各 LOM が短期間で品質の揃ったサイトを立ち上げられるようにすることが目的。

**公開URL**: https://tagukichi.github.io/lomtheme/
**リファレンス実装**: [tamanojc.com](https://tamanojc.com/)（既存サイトの解析元）

---

## いまの中身

```
html/                   ★ サイト本体（素のHTML／これが成果物）
  README.md             各LOMへの展開手順
  index.html ほか8ページ
  assets/css|js|img/

tools/
  check_html.py         リンク切れ・見出し構造の検査
  extract_structure.py  既存サイトHTMLから使用技術を抽出（解析用）
  outline.py            既存サイトHTMLのDOM構造を出力（解析用）
  preview/              旧・WordPressテーマのプレビュー生成（現在は未使用）

docs/
  01-existing-site-findings.md    既存サイトの解析結果
  02-component-inventory.md       既存サイトの独自コンポーネント棚卸し
  03-page-layout-requirements.md  参考レイアウトの分解
  04-theme-structure.md           旧・WordPressテーマの構造
  site-analysis/                  解析レポート（自動生成）

reference/              SingleFile で保存した既存サイトHTML（解析の入力）
theme/                  旧・WordPressテーマ（クラシック+ACF／現在は凍結）
```

### `theme/` について

先に WordPress のクラシックテーマとして実装したが、
**素のHTMLで作り直す方針に切り替えた**ため現在は凍結している。
削除はしていない。WordPress化する段になったら、`html/` を元に
[WordPressテーマへ変換する](docs/04-theme-structure.md)。

---

## 進め方

- [x] 既存サイト（tamanojc.com）の解析
- [x] 独自コンポーネントの棚卸し
- [x] 参考レイアウトの分解
- [x] **静的サイトとして実装（9ページ）**
- [x] GitHub Pages で公開
- [ ] 実データでの調整（文言・写真・組織情報）
- [ ] 組織図・事業計画・賛助会員のページ
- [ ] WordPress化（テーマとして配布する場合）
- [ ] 配布形態の設計（ライセンス・更新配信・導入手順書）

---

## 開発

ビルド工程は無い。`html/index.html` をブラウザで開けばそのまま動く。

```bash
# ローカルで確認（file:// でも動くが、サーバー経由のほうが本番に近い）
python3 -m http.server 8000 --directory html
open http://localhost:8000/

# 検査
python3 tools/check_html.py html/
```

`html/` に push すると、検査を通したうえで自動的に公開される。
検査に落ちたページがあると公開されない。

各LOMへの展開手順は [`html/README.md`](html/README.md) を参照。
