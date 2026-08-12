# 既存サイト（tamanojc.com）解析結果

更新日: 2026-08-12
対象: SingleFile で取得した6ページ（`reference/`）
生成物: `docs/site-analysis/`（`tools/extract_structure.py` による自動生成）

---

## 構成

| 項目 | 内容 |
|---|---|
| 親テーマ | `hello-elementor` |
| ページビルダー | Elementor **Pro**（Theme Builder / Loop Builder を使用） |
| SEO | Yoast SEO（`yoast-schema-graph` を出力） |
| アイコン | Font Awesome 7 + Elementor Icons（eicons） |
| 和文フォント | M PLUS 1（Google Fonts） |
| ブランドカラー | `#0097D7` |

### Elementor Pro の使用が確定

テンプレート種別が全ページに出力されている。

| 種別 | 用途 |
|---|---|
| `header` / `footer` | 全ページ共通のヘッダー・フッター |
| `single-page` | 固定ページ用テンプレート |
| `single-post` | 投稿用テンプレート |
| `archive` | アーカイブ用テンプレート |
| `loop-item` / `loop-grid.post` | ループグリッド（Pro専用） |

Theme Builder と Loop Builder はいずれも **Elementor Pro 専用機能**。
配布テーマではこれらをPHPテンプレートに置き換える。

### 使用ウィジェット

`heading` 46 / `shortcode` **14** / `text-editor` 13 / `theme-site-logo` 12 /
`icon` 6 / `divider` 6 / `social-icons` 6 / `breadcrumbs` 5 /
`theme-post-content` 4 / `image` 2 / `spacer` 2 / `loop-grid.post` 1 / `button` 1

`shortcode` が14回使われている点が重要。**独自機能はすべてショートコード経由で
Elementor に差し込まれている**（詳細は `02-component-inventory.md`）。

---

## サイト構成

### グローバルナビ

`HOME` / `最新情報` / `理事長あいさつ` / `活動について` / `入会のご案内`
＋ ヘッダー右に「入会案内」CTAボタン、ハンバーガー内に検索フォーム

### 投稿の分類

`お知らせ` / `開催案内` / `開催報告` の3分類。
アーカイブのタブと、カードのバッジ表示に使われている。
**JC共通で使える分類なので、配布テーマの初期タクソノミーとして採用する。**

### 固定ページ

- HOME（page-id-95）
- 理事長あいさつ
- 活動について ← **中身が空**
- 最新情報
- 入会のご案内 ← **中身が空**
- `/about/サンプル１/`、`/about/サンプル２/` ← 削除し忘れのサンプル

---

## テーマ化にあたって直すべき点

### 1. パーマリンクに年が無い（最優先）

```
/%monthnum%/%day%/%postname%/
```

実例: `/11/15/lifestyle-market.../`、`/09/19/【9月例会】.../`

**年が入っていない。** 同じ月日に同じスラッグの投稿を作ると衝突する。
`/09/19/` の投稿が既に2件あり、実際に危険な状態。年別アーカイブも成立しない。
配布テーマでは `/news/%year%/%monthnum%/%postname%/` 等を初期値とする。

### 2. h1 が存在しない

| ページ | h1 |
|---|---|
| HOME | **なし** |
| 理事長あいさつ | **なし** |
| 活動について | **なし** |
| 最新情報 | **なし** |
| 入会のご案内 | **なし** |
| 投稿個別 | あり |

固定ページのタイトルがすべて **h2** で出力されている。
さらにヘッダーのサイト名が `h3 一般社団法人` ＋ `h2 玉野青年会議所` として
出力されるため、**h1 が無いまま h3 → h2 と始まる**壊れた見出し順序になっている。

配布テーマではテンプレート側で h1 を1つだけ必ず出力し、サイト名は見出しにしない。

### 3. 日本語スラッグ

`/理事長あいさつ/` は `/%e7%90%86%e4%ba%8b%e9%95%b7.../` にエンコードされ、
共有・解析・外部連携で扱いづらい。英語スラッグ＋日本語タイトルへ。

### 4. alt属性の未設定

トップページは画像15点中 **14点が alt 空**。
ただし後述の `jaycee-arc` では alt が入っており、実装世代で差がある。

### 5. コンポーネントCSSの無条件読み込み

8つの独自スタイルが、使っていないページも含めて**全ページで読み込まれている**。
配布テーマではショートコード実行時にのみ enqueue する。

### 6. ショートコード出力が wpautop で壊れている

トップページの `jaycee-archive` は、テキストエディタウィジェット経由で
出力されているため wpautop が介入し、`<a>` の中に空の `<p>` が挿入され、
アンカーが二重になっている。

```
article.jaycee-archive-card
  a[href]
    p            ← 不要
    div.jaycee-archive-card-thumb
  p              ← 不要
    a[href]      ← 重複
  div.jaycee-archive-card-body
```

配布テーマではPHPテンプレートで直接出力し、この問題を構造的に発生させない。
