<?php
/**
 * 自己完結型プレビューの生成
 *
 * preview/*.html は CSS/JS を ../theme から相対参照しているため、
 * 単体では配れない。このスクリプトは各画面のCSS/JSをすべて埋め込み、
 * 1ファイルだけで完結する preview/standalone.html を作る。
 *
 * 出力は <title> から始まる断片形式。ブラウザで直接開けるし、
 * Artifact としてそのまま公開もできる。
 *
 * 使い方:
 *     php tools/preview/build.php          # 先にこちらを実行
 *     php tools/preview/build-standalone.php
 *
 * @package LomTheme\Preview
 */

declare( strict_types = 1 );

$root       = dirname( __DIR__, 2 );
$preview    = $root . '/preview';
$theme      = $root . '/theme';

$screens = array(
	'front'         => array( 'トップ', 'メインビジュアル・最新情報・紹介セクション' ),
	'page-activity' => array( '活動について', 'セクションの繰り返しで組む固定ページ' ),
	'page-join'     => array( '入会のご案内', '7種のブロックを組み合わせた固定ページ' ),
	'page-greeting' => array( '理事長あいさつ', '本文中心の固定ページ' ),
	'archive'       => array( '一覧', '分類タブ付きのカードグリッド' ),
	'single'        => array( '記事', '投稿個別ページ' ),
	'search'        => array( '検索結果', '検索結果一覧' ),
	'404'           => array( '404', 'ページが見つからない場合' ),
);

/**
 * 外部参照を実ファイルの中身に置き換え、1枚で完結するHTMLにする。
 */
function pv_inline( string $html, string $theme_dir ): string {
	// プレビュー用の画面切り替えバーは、外側のシェルが担うので取り除く
	$html = (string) preg_replace( '#<div class="pv-bar">.*?</div>\s*#s', '', $html );
	$html = str_replace( '<link rel="stylesheet" href="preview-bar.css">' . "\n", '', $html );

	// Google Fonts は外部ホストなので落とす。和文はOS標準にフォールバックする。
	$html = (string) preg_replace( '#<link rel="[^"]*" href="https://fonts\.[^"]*"[^>]*>\s*#', '', $html );

	// CSS を埋め込む
	$html = (string) preg_replace_callback(
		'#<link rel="stylesheet" href="\.\./theme/([^"]+)">#',
		static function ( array $m ) use ( $theme_dir ): string {
			$file = $theme_dir . '/' . $m[1];

			return is_readable( $file )
				? "<style>\n" . file_get_contents( $file ) . "\n</style>"
				: '';
		},
		$html
	);

	// JS を埋め込む
	$html = (string) preg_replace_callback(
		'#<script src="\.\./theme/([^"]+)"></script>#',
		static function ( array $m ) use ( $theme_dir ): string {
			$file = $theme_dir . '/' . $m[1];

			return is_readable( $file )
				? "<script>\n" . file_get_contents( $file ) . "\n</script>"
				: '';
		},
		$html
	);

	return $html;
}

$payload = array();
$nav     = '';

foreach ( $screens as $slug => list( $label, $note ) ) {
	// '404' は配列キーとして int に変換されるため、文字列に戻す。
	$slug = (string) $slug;
	$file = "{$preview}/{$slug}.html";
	if ( ! is_readable( $file ) ) {
		fwrite( STDERR, "先に build.php を実行してください（{$slug}.html がありません）\n" );
		exit( 1 );
	}

	$payload[ $slug ] = array(
		'label' => $label,
		'note'  => $note,
		'html'  => pv_inline( (string) file_get_contents( $file ), $theme ),
	);

	$nav .= sprintf(
		'<button type="button" role="tab" class="tab" data-screen="%s" aria-selected="false">%s</button>',
		htmlspecialchars( $slug, ENT_QUOTES, 'UTF-8' ),
		htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' )
	);
}

// JSON_HEX_TAG が < > をエスケープするので、埋め込んだHTMLの </script> が
// 外側の script ブロックを閉じてしまう事故を防げる。
$json = json_encode(
	$payload,
	JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

$page = <<<'HTML'
<meta charset="utf-8">
<title>LOM Theme</title>
<style>
/* ---------------------------------------------------------------------------
   ビューアの外枠。中に映すテーマが主役なので、枠は徹底して静かに保つ。
   テーマのブランド色（#0097D7）は意図的に使わない。枠と中身が
   混ざって見えると、どこまでがテーマの意匠か判別できなくなる。
   --------------------------------------------------------------------------- */

:root {
  --shell-bg:     #eceef1;
  --shell-panel:  #ffffff;
  --shell-line:   #d6dae0;
  --shell-text:   #1b1f24;
  --shell-dim:    #5f6873;
  --shell-accent: #a96c12;
  --shell-shadow: 0 18px 48px rgba(20, 26, 34, 0.14);
  --shell-radius: 10px;
}

:root:not([data-theme="light"]) {
  color-scheme: light;
}

@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    --shell-bg:     #16191d;
    --shell-panel:  #21252b;
    --shell-line:   #333941;
    --shell-text:   #e7eaee;
    --shell-dim:    #8d959f;
    --shell-accent: #dda94b;
    --shell-shadow: 0 18px 48px rgba(0, 0, 0, 0.5);
    color-scheme: dark;
  }
}

:root[data-theme="dark"] {
  --shell-bg:     #16191d;
  --shell-panel:  #21252b;
  --shell-line:   #333941;
  --shell-text:   #e7eaee;
  --shell-dim:    #8d959f;
  --shell-accent: #dda94b;
  --shell-shadow: 0 18px 48px rgba(0, 0, 0, 0.5);
  color-scheme: dark;
}

* { box-sizing: border-box; }

body {
  margin: 0;
  background: var(--shell-bg);
  color: var(--shell-text);
  font-family: system-ui, -apple-system, "Hiragino Kaku Gothic ProN",
               "Yu Gothic", "Meiryo", sans-serif;
  font-size: 14px;
  line-height: 1.6;
}

/* --- 上部バー ------------------------------------------------------------ */

.bar {
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 20px;
  padding: 12px 20px;
  background: var(--shell-panel);
  border-bottom: 1px solid var(--shell-line);
}

.bar__id {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-right: auto;
}

.bar__mark {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  color: var(--shell-accent);
}

.bar__name {
  font-size: 13px;
  font-weight: 600;
}

.tabs,
.widths {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.tab,
.width {
  padding: 7px 13px;
  border: 1px solid transparent;
  border-radius: 6px;
  background: transparent;
  color: var(--shell-dim);
  font: inherit;
  font-size: 12.5px;
  cursor: pointer;
  transition: background-color 0.15s, color 0.15s, border-color 0.15s;
}

.tab:hover,
.width:hover {
  background: color-mix(in srgb, var(--shell-text) 7%, transparent);
  color: var(--shell-text);
}

.tab[aria-selected="true"],
.width[aria-pressed="true"] {
  border-color: var(--shell-line);
  background: color-mix(in srgb, var(--shell-accent) 14%, transparent);
  color: var(--shell-text);
  font-weight: 600;
}

.tab:focus-visible,
.width:focus-visible {
  outline: 2px solid var(--shell-accent);
  outline-offset: 2px;
}

.widths {
  padding-left: 20px;
  border-left: 1px solid var(--shell-line);
}

/* --- ステージ ------------------------------------------------------------ */

.stage {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 22px 20px 56px;
}

.caption {
  display: flex;
  align-items: baseline;
  gap: 10px;
  font-size: 12.5px;
  color: var(--shell-dim);
}

.caption b {
  color: var(--shell-text);
  font-weight: 600;
}

.viewport {
  position: relative;
  transition: width 0.2s ease, height 0.2s ease;
}

.viewport iframe {
  display: block;
  width: 100%;
  border: 1px solid var(--shell-line);
  border-radius: var(--shell-radius);
  background: #fff;
  box-shadow: var(--shell-shadow);
  transform-origin: top left;
}

.note {
  max-width: 680px;
  margin: 0;
  padding: 14px 18px;
  border-left: 3px solid var(--shell-accent);
  border-radius: 0 8px 8px 0;
  background: var(--shell-panel);
  font-size: 12.5px;
  line-height: 1.85;
  color: var(--shell-dim);
}

.note b { color: var(--shell-text); }

@media (prefers-reduced-motion: reduce) {
  .viewport,
  .tab,
  .width { transition: none; }
}
</style>

<header class="bar">
  <div class="bar__id">
    <span class="bar__mark">LOM THEME</span>
    <span class="bar__name">プレビュー</span>
  </div>
  <nav class="tabs" role="tablist" aria-label="画面を選ぶ">__NAV__</nav>
  <div class="widths" role="group" aria-label="画面幅を変える">
    <button type="button" class="width" data-width="1280" aria-pressed="true">デスクトップ</button>
    <button type="button" class="width" data-width="834" aria-pressed="false">タブレット</button>
    <button type="button" class="width" data-width="390" aria-pressed="false">スマホ</button>
  </div>
</header>

<main class="stage">
  <p class="caption"><b id="caption-label"></b><span id="caption-note"></span></p>
  <div class="viewport" id="viewport">
    <iframe id="frame" title="テーマのプレビュー"></iframe>
  </div>
  <p class="note">
    <b>これはテーマの実テンプレートを実行して書き出した出力です。</b>
    静的HTMLを別途書いたものではないので、ここで見えているものが実際のテーマの出力です。
    画像はすべて生成したプレースホルダ、和文フォントは実サイトでは M PLUS 1 を読み込みます。
    分類タブのAJAX絞り込みだけはサーバーが必要なため動きません。
    メインビジュアルのスライダー、ハンバーガーメニュー、レスポンシブは動作します。
  </p>
</main>

<script type="application/json" id="screens">__JSON__</script>
<script>
(function () {
  'use strict';

  var screens  = JSON.parse(document.getElementById('screens').textContent);
  var frame    = document.getElementById('frame');
  var viewport = document.getElementById('viewport');
  var stage    = document.querySelector('.stage');
  var capLabel = document.getElementById('caption-label');
  var capNote  = document.getElementById('caption-note');
  var tabs     = Array.prototype.slice.call(document.querySelectorAll('.tab'));
  var widths   = Array.prototype.slice.call(document.querySelectorAll('.width'));

  var current = tabs[0].dataset.screen;
  var width   = 1280;

  // iframe の中身の高さに外枠を合わせる。入れ子のスクロールバーを避ける。
  function fit() {
    var available = stage.clientWidth - 40;
    var scale     = Math.min(1, available / width);
    var doc       = frame.contentDocument;
    var height    = doc ? doc.documentElement.scrollHeight : 900;

    frame.style.width     = width + 'px';
    frame.style.height    = height + 'px';
    frame.style.transform = 'scale(' + scale + ')';
    viewport.style.width  = (width * scale) + 'px';
    viewport.style.height = (height * scale) + 'px';
  }

  function show(slug) {
    var screen = screens[slug];
    if (!screen) { return; }

    current = slug;
    capLabel.textContent = screen.label;
    capNote.textContent  = screen.note;

    tabs.forEach(function (tab) {
      tab.setAttribute('aria-selected', tab.dataset.screen === slug ? 'true' : 'false');
    });

    frame.addEventListener('load', function once() {
      frame.removeEventListener('load', once);
      // 画像とレイアウトが落ち着いてから高さを測る
      requestAnimationFrame(function () { requestAnimationFrame(fit); });
    });

    frame.srcdoc = screen.html;
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () { show(tab.dataset.screen); });
  });

  widths.forEach(function (button) {
    button.addEventListener('click', function () {
      width = parseInt(button.dataset.width, 10);
      widths.forEach(function (other) {
        other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
      });
      fit();
    });
  });

  window.addEventListener('resize', fit);
  show(current);
}());
</script>
HTML;

$page = str_replace( array( '__NAV__', '__JSON__' ), array( $nav, (string) $json ), $page );

file_put_contents( $preview . '/standalone.html', $page );

printf(
	"preview/standalone.html を生成しました（%d画面 / %.1f KB）\n",
	count( $payload ),
	strlen( $page ) / 1024
);
