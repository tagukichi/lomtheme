# テーマ構造

`theme/` 以下が配布するテーマ本体。そのまま WordPress の
`wp-content/themes/` に置けば動く。

---

## ファイル構成

```
theme/
├ style.css                  テーマヘッダーの宣言のみ
├ functions.php              inc/ を読み込むだけ
├ header.php / footer.php / sidebar.php / searchform.php
├ front-page.php             トップ（メインビジュアル＋最新情報＋ブロック）
├ page.php                   固定ページ（h1 ＋ フレキシブルコンテンツ）
├ single.php                 投稿個別（2カラムのカード）
├ archive.php / index.php    一覧（分類タブ＋カードグリッド）
├ search.php / 404.php
│
├ inc/
│  ├ setup.php               テーマサポート、メニュー、画像サイズ
│  ├ assets.php              CSS/JSの条件付き読み込み
│  ├ template-tags.php       バッジ・日付・パンくず・カード描画
│  ├ news.php                分類（お知らせ/開催案内/開催報告）と色
│  ├ acf-fields.php          ACFフィールド定義（PHP登録）
│  └ ajax-filter.php         分類タブのAJAX処理
│
├ template-parts/
│  ├ card.php                アーカイブのカード1枚
│  ├ archive-filter.php      分類タブ
│  ├ mainvisual.php          メインビジュアル
│  ├ content-flexible.php    フレキシブルコンテンツの振り分け
│  └ layouts/                7種のレイアウト
│     ├ hero-image.php  ├ lead.php        ├ rich-text.php
│     ├ section-gallery.php  ├ feature-cards.php
│     ├ column-boxes.php     └ info-box.php
│
└ assets/
   ├ css/base.css            デザイントークン＋ベース
   ├ css/components/         chrome / archive / sidebar / single /
   │                         layouts / mainvisual
   ├ js/                     navigation / archive-filter / mainvisual
   └ img/                    SNSアイコン（SVG）
```

---

## 既存サイトからの対応表

CSSのクラス接頭辞を `jaycee-` から `lom-` に統一した。
既存サイトの実装を参照するときはこの表で読み替える。

| 既存サイト | 配布テーマ | 備考 |
|---|---|---|
| `jaycee-arc` / `jaycee-archive` | `lom-grid` / `lom-card` | 新旧2世代を1つに統合 |
| `jaycee-arc-filter` / `-tab` | `lom-filter` / `lom-filter__tab` | JS無効でもリンクとして機能するよう変更 |
| `jaycee-sb` | `lom-sb` | サイドバー「最新情報」 |
| `jaycee-single` | `lom-single` | 2カラムのカード |
| `mvts` | `lom-mv` | メインビジュアル |
| `jc-acf-link-buttons` | `lom-button` | |
| `jaycee-sosiki` | **未実装** | 参考ページ待ち |
| `jaycee-keikaku` | **未実装** | 参考ページ待ち |
| `jaycee-sanjo` | **未実装** | 参考ページ待ち |

---

## 既存サイトの問題への対処

`01-existing-site-findings.md` で挙げた6点をどう解決したか。

| 問題 | 対処 |
|---|---|
| h1 が無い | `page.php` / `single.php` / `archive.php` が h1 を1つだけ出力。ヘッダーのサイト名は見出しタグにしない |
| ショートコードが wpautop で破損 | PHPテンプレートで直接出力。ショートコードを経由しない |
| CSSの全ページ無条件読み込み | `inc/assets.php` が画面ごとに必要なものだけ読み込む |
| alt が空 | `lomtheme_the_thumbnail()` がタイトルを alt に入れる。メインビジュアルは alt 入力を必須にした |
| 全宣言に `!important` | ページビルダーを使わないので不要。すべて削除 |
| パーマリンクに年が無い | テーマからは変更できない設定のため、導入手順書で案内する（未作成） |

---

## 設計上の判断

**ACFフィールドをPHPで登録する。** JSONインポート作業が各LOMで不要になり、
Gitで差分管理でき、担当者が管理画面からフィールドを壊せない。

**分類の色をテーマが持たない。** タームメタに保存し、CSS変数 `--badge` で
渡す。LOMが分類を増やしても破綻しない。

**本文エディタを隠さない。** 既存ページを移行する際、本文に中身が残った
ままエディタだけ消えると編集不能になるため。フレキシブルコンテンツは
エディタの上に表示されるので、通常はそちらが使われる。

**アイコンフォントを同梱しない。** SNSアイコンはSVGをCSSマスクで描画する。
既存サイトは Font Awesome 7 と Elementor Icons の2つを読み込んでいた。

**分類タブはリンクとして書く。** JavaScriptはページ遷移をAJAXに
置き換えるだけの上乗せ。既存サイトは `button` 要素で、JS無効時は
何も起きなかった。

---

## 未着手

- [ ] 組織図・事業計画・賛助会員のコンポーネント（参考ページ待ち）
- [ ] お問い合わせフォーム（実装方法の方針決めから）
- [ ] 会員専用エリア
- [ ] 導入手順書（パーマリンク設定・初期ページ作成・ACF Pro導入）
- [ ] 配布形態（ライセンス・更新配信）
- [ ] 実機での表示確認
