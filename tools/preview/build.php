<?php
/**
 * 静的プレビューの生成
 *
 * テーマの実テンプレートを実行して preview/*.html に書き出す。
 * 静的HTMLを別途手で書くとテーマ本体と必ず乖離するため、
 * 出力元をテンプレートそのものにしている。
 *
 * 使い方:
 *     php tools/preview/build.php
 *     open preview/index.html
 *
 * @package LomTheme\Preview
 */

declare( strict_types = 1 );

require_once __DIR__ . '/wp-stubs.php';

$theme_dir = dirname( __DIR__, 2 ) . '/theme';
$out_dir   = dirname( __DIR__, 2 ) . '/preview';

// preview/ から見たテーマの位置。file:// でもそのまま開ける。
$GLOBALS['pv']['theme_uri'] = '../theme';

require_once __DIR__ . '/fixtures.php';

// テーマ本体を読み込む（add_action はスタブなので副作用は無い）
define( 'LOMTHEME_VERSION', 'preview' );
define( 'LOMTHEME_DIR', $theme_dir );
define( 'LOMTHEME_URI', $GLOBALS['pv']['theme_uri'] );

require_once $theme_dir . '/inc/setup.php';
require_once $theme_dir . '/inc/assets.php';
require_once $theme_dir . '/inc/template-tags.php';
require_once $theme_dir . '/inc/news.php';

if ( ! is_dir( $out_dir ) ) {
	mkdir( $out_dir, 0o755, true );
}

/**
 * 1画面ぶんをレンダリングして書き出す。
 */
function pv_render( string $slug, string $template, array $state ): string {
	$defaults = array(
		'conds'          => array(),
		'posts'          => array(),
		'index'          => -1,
		'post'           => null,
		'fields'         => array(),
		'rowstack'       => array(),
		'styles'         => array(),
		'scripts'        => array(),
		'search'         => '',
		'title'          => '',
		'doc_title'      => '',
		'queried_id'     => 0,
		'queried_object' => null,
	);

	$merged = array_merge( $defaults, $state );
	foreach ( $merged as $key => $value ) {
		$GLOBALS['pv'][ $key ] = $value;
	}

	// 本番の WordPress では、ループに入る前から対象の投稿が参照できる。
	// is_singular('post') などが enqueue 時点で正しく効くよう合わせる。
	$GLOBALS['pv']['post'] = $merged['posts'][0] ?? null;

	// テンプレートがページ送りのために参照するメインクエリ。
	$query                = new WP_Query();
	$query->posts         = $merged['posts'];
	$query->found_posts   = (int) ( $state['found'] ?? count( $merged['posts'] ) );
	$query->max_num_pages = (int) ( $state['max_pages'] ?? 1 );
	$GLOBALS['wp_query']  = $query;

	// どのCSS/JSを読むかはテーマ本体に決めさせる。
	lomtheme_enqueue_assets();

	ob_start();
	include dirname( __DIR__, 2 ) . '/theme/' . $template;
	$html = (string) ob_get_clean();

	$html = pv_inject_bar( $html, $slug );

	$path = dirname( __DIR__, 2 ) . '/preview/' . $slug . '.html';
	file_put_contents( $path, $html );

	return $path;
}

/**
 * 画面切り替え用のバーを差し込む。
 */
function pv_inject_bar( string $html, string $current ): string {
	$screens = pv_screens();
	$links   = '';
	foreach ( $screens as $slug => $label ) {
		$links .= sprintf(
			'<a href="%s.html"%s>%s</a>',
			$slug,
			$slug === $current ? ' class="is-current"' : '',
			htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' )
		);
	}

	$bar = '<div class="pv-bar"><span class="pv-bar__label">PREVIEW</span>' . $links . '</div>';

	return (string) preg_replace( '/(<body[^>]*>)/', '$1' . $bar, $html, 1 );
}

/**
 * 生成する画面の一覧。
 */
function pv_screens(): array {
	return array(
		'front'         => 'トップ',
		'page-activity' => '活動について',
		'page-join'     => '入会のご案内',
		'page-greeting' => '理事長あいさつ',
		'archive'       => '一覧',
		'single'        => '記事',
		'search'        => '検索結果',
		'404'           => '404',
	);
}

/* ===========================================================================
 * 各画面
 * ======================================================================== */

$posts = $GLOBALS['pv']['all_posts'];
$pages = pv_pages();
$built = array();

// --- トップ ---------------------------------------------------------------
$front = pv_front_page();
$built[] = pv_render( 'front', 'front-page.php', array(
	'conds'      => array( 'front_page' => true, 'page' => true, 'singular' => true ),
	'posts'      => array( $front ),
	'fields'     => $front['fields'],
	'queried_id' => $front['id'],
	'doc_title'  => 'HOME',
) );

// --- 固定ページ ------------------------------------------------------------
foreach ( $pages as $slug => $page ) {
	$built[] = pv_render( $slug, 'page.php', array(
		'conds'      => array( 'page' => true, 'singular' => true ),
		'posts'      => array( $page ),
		'fields'     => $page['fields'],
		'queried_id' => $page['id'],
		'doc_title'  => $page['title'],
	) );
}

// --- 一覧 -----------------------------------------------------------------
$built[] = pv_render( 'archive', 'archive.php', array(
	'conds'     => array( 'archive' => true, 'home' => true ),
	'posts'     => $posts,
	'title'     => '最新情報',
	'doc_title' => '最新情報',
	'max_pages' => 5,   // ページ送りの見た目を確認するため
	'found'     => 52,
) );

// --- 記事個別 --------------------------------------------------------------
$single = $posts[0];
$built[] = pv_render( 'single', 'single.php', array(
	'conds'      => array( 'singular' => true ),
	'posts'      => array( $single ),
	'queried_id' => $single['id'],
	'doc_title'  => $single['title'],
) );

// --- 検索結果 --------------------------------------------------------------
$built[] = pv_render( 'search', 'search.php', array(
	'conds'     => array( 'search' => true ),
	'posts'     => array_slice( $posts, 0, 4 ),
	'search'    => '例会',
	'doc_title' => '検索結果',
) );

// --- 404 ------------------------------------------------------------------
$built[] = pv_render( '404', '404.php', array(
	'conds'     => array( 'is404' => true ),
	'doc_title' => 'ページが見つかりません',
) );

/* ===========================================================================
 * 目次と補助ファイル
 * ======================================================================== */

$rows = '';
foreach ( pv_screens() as $slug => $label ) {
	$rows .= sprintf(
		'<li><a href="%1$s.html"><strong>%2$s</strong><span>%1$s.html</span></a></li>',
		$slug,
		htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' )
	);
}

$index = <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LOM Theme プレビュー</title>
<link rel="stylesheet" href="preview-bar.css">
<style>
  body { font-family: "M PLUS 1", system-ui, sans-serif; margin: 0; background: #f2f2f2; color: #333; }
  .wrap { max-width: 760px; margin: 0 auto; padding: 48px 20px 64px; }
  h1 { font-size: 22px; margin: 0 0 8px; color: #111; }
  p.note { font-size: 13px; line-height: 1.9; color: #666; margin: 0 0 28px; }
  ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
  a { display: flex; justify-content: space-between; align-items: baseline; gap: 12px;
      padding: 16px 20px; background: #fff; border-radius: 12px; text-decoration: none;
      color: #111; box-shadow: 0 6px 16px rgba(0,0,0,.05); }
  a:hover { outline: 2px solid #0097d7; }
  a span { font-size: 12px; color: #999; font-family: ui-monospace, monospace; }
  .warn { margin-top: 28px; padding: 14px 18px; border-left: 3px solid #e8a33d;
          background: #fff; font-size: 13px; line-height: 1.9; border-radius: 0 8px 8px 0; }
</style>
</head>
<body>
<div class="wrap">
  <h1>LOM Theme プレビュー</h1>
  <p class="note">
    テーマの実テンプレート（<code>theme/*.php</code>）をそのまま実行して書き出した静的HTMLです。
    CSSとJSもテーマ本体を直接参照しているので、ここで見えているものが実際の出力です。
  </p>
  <ul>$rows</ul>
  <div class="warn">
    <strong>注意</strong><br>
    画像はすべてその場で生成したプレースホルダです。<br>
    分類タブのAJAX絞り込みはサーバーが必要なため動きません（タブのリンク遷移は無効化されています）。<br>
    メインビジュアルのスライダーとハンバーガーメニューは動作します。
  </div>
</div>
</body>
</html>
HTML;

file_put_contents( $out_dir . '/index.html', $index );

$bar_css = <<<CSS
/* プレビュー用の画面切り替えバー。テーマ本体とは無関係。 */
.pv-bar {
  position: sticky; top: 0; z-index: 9999;
  display: flex; flex-wrap: wrap; align-items: center; gap: 4px;
  padding: 8px 12px; background: #111; color: #fff;
  font-family: system-ui, sans-serif; font-size: 12px;
}
.pv-bar__label { font-weight: 700; letter-spacing: .08em; color: #e8a33d; margin-right: 8px; }
.pv-bar a { color: #ccc; text-decoration: none; padding: 5px 10px; border-radius: 4px; }
.pv-bar a:hover { background: #333; color: #fff; }
.pv-bar a.is-current { background: #0097d7; color: #fff; }
/* テーマのヘッダーも sticky なので、バーの下に来るよう押し下げる */
.lom-header { top: 37px; }
CSS;

file_put_contents( $out_dir . '/preview-bar.css', $bar_css );

echo "生成しました:\n";
foreach ( $built as $path ) {
	printf( "  %-58s %6.1f KB\n", str_replace( dirname( __DIR__, 2 ) . '/', '', $path ), filesize( $path ) / 1024 );
}
echo "  preview/index.html\n";
