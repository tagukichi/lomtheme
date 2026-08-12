# 独自コンポーネント棚卸し

既存サイトには `jaycee-*` を接頭辞とする独自コンポーネント群が実装されている。
**これが「これまで培ったノウハウ」の実体**であり、配布テーマの中核になる。

すべて**ショートコード経由**で Elementor に差し込まれている
（`shortcode` ウィジェットの使用14回）。
つまりレイアウトはElementorに依存しているが、**機能そのものはPHP側にある**。
これがクラシックテーマへ移植できる根拠になる。

---

## 一覧

| ハンドル | 役割 | 取得ページでの使用 |
|---|---|---|
| `jaycee-arc` | タブ絞り込み付きアーカイブ（AJAX） | 最新情報 |
| `jaycee-archive` | カードグリッド・アーカイブ（旧世代） | HOME |
| `jaycee-sb` | サイドバー「最新情報」リスト | 全ページ |
| `jaycee-single` | 投稿個別ページのレイアウト | 投稿個別 |
| `jaycee-sosiki` | 組織図 | **未取得** |
| `jaycee-keikaku` | 事業計画 | **未取得** |
| `jaycee-sanjo` | 賛助会員（ロゴ・特典・会社名） | **未取得** |
| `jc-acf-link-buttons` | ACF連動のリンクボタン | 全ページ |
| `mvts` | メインビジュアル・スライダー | HOME |

`sosiki` / `keikaku` / `sanjo` は**CSSは全ページで読み込まれているが、
取得した6ページにマークアップが出てこない**。これらを使うページが未取得。

---

## 詳細

### `jaycee-arc` — タブ絞り込みアーカイブ（新世代・これを採用する）

```
div.jaycee-arc-wrap
  div.jaycee-arc-filter
    button.jaycee-arc-tab.is-active  "ALL"
    button.jaycee-arc-tab            "お知らせ"
    button.jaycee-arc-tab            "開催案内"
    button.jaycee-arc-tab            "開催報告"
  div.jaycee-arc-spinner.sf-hidden   "読み込み中…"
  div.jaycee-arc-grid
    article.jaycee-arc-card.is-news
      a.jaycee-arc-link[href]
        div.jaycee-arc-thumb
          img[alt=記事タイトル]
        div.jaycee-arc-body
          div.jaycee-arc-meta
          h3.jaycee-arc-title
  div.jaycee-arc-pager
```

- スピナーがあるので**AJAXで絞り込み**している
- カードに `is-news` のような分類modifierが付く
- **alt に記事タイトルが入っている**（実装が丁寧）
- マークアップが素直で、`<a>` の入れ子も正常

### `jaycee-archive` — カードグリッド（旧世代・統合して廃止する）

```
div.jaycee-archive-wrap
  div.jaycee-archive-grid          3カラム / gap 20px 30px
    article.jaycee-archive-card    角丸12px, box-shadow
      div.jaycee-archive-card-thumb   aspect-ratio 3/4, object-fit cover
        img
        span.jaycee-archive-thumb-badge   分類バッジ（左下オーバーレイ）
      div.jaycee-archive-card-body
        div.jaycee-archive-date    "2025.11.15"
        h3.jaycee-archive-title
  div.jaycee-archive-pagination
```

レスポンシブ: 1024px以下 2カラム / 767px以下 2カラム

**問題点**（`01-existing-site-findings.md` 参照）:
- wpautop でマークアップが壊れている
- `alt` が空
- ページネーションのカレント色が `#1DA1F2`（X/Twitterの青）で、
  ブランドカラー `#0097D7` と不一致

`jaycee-arc` と役割が重複している。**配布テーマでは1つに統合する。**

### `jaycee-sb` — サイドバー「最新情報」

```
div.jaycee-sb-wrap
  div.jaycee-sb-list
    div.jaycee-sb-item
      div.jaycee-sb-thumb
      div.jaycee-sb-main
        div.jaycee-sb-top
        div.jaycee-sb-date
        div.jaycee-sb-title
        span.jaycee-sb-badge
```

全ページの右カラムに出る。添付いただいた埼玉中央JCのサイドバーと同じ構成。
**JCサイトの定番なので配布テーマの標準ウィジェットにする。**

### `mvts` — メインビジュアル・スライダー

```
div.mvts
  div.mvts__viewport
    a.mvts__slide.is-active[href]   スライドにリンクを張れる
    div.mvts__slide
    div.mvts__dots
      button.mvts__dot.is-active
  div.mvts__thumbs
    button.mvts__thumb.is-active
```

BEM記法で書かれており、`jaycee-*` 群とは別系統。
ドット + サムネイルの二重ナビゲーション付き。スライドにリンクを設定できる。

### `jc-acf-link-buttons`

ACF連動のリンクボタン。**既存サイトで既に ACF が使われている**ことの裏付け。
クラシックテーマ + ACF という方針は、既存実装の延長線上にある。

---

## 配布テーマへの移植方針

| 現行 | 配布テーマ |
|---|---|
| ショートコード + Elementorウィジェットで配置 | テンプレートパーツ + ACFで配置 |
| CSSを全ページ無条件 enqueue | 使用時のみ条件付き enqueue |
| `jaycee-arc` と `jaycee-archive` が併存 | `jaycee-arc` に統合 |
| wpautop でマークアップ破損 | PHPテンプレートで直接出力 |
| 分類はカテゴリー | `お知らせ / 開催案内 / 開催報告` を初期タクソノミーとして同梱 |

---

## 未取得のため要確認

`sosiki` / `keikaku` / `sanjo` は、JC サイトで需要が高い機能でありながら
実物を確認できていない。以下のページのHTMLを追加でいただきたい。

- [ ] 組織図のページ
- [ ] 事業計画のページ
- [ ] 賛助会員のページ

これらが揃うと、配布テーマに載せるコンポーネントが確定する。
