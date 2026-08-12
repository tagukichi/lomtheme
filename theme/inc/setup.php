<?php
/**
 * テーマの基本設定
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * テーマサポートとメニューの登録。
 */
function lomtheme_setup(): void {
	load_theme_textdomain( 'lomtheme', LOMTHEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 61,
		'width'       => 134,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );

	register_nav_menus( array(
		'primary' => __( 'グローバルナビ', 'lomtheme' ),
		'footer'  => __( 'フッターナビ', 'lomtheme' ),
	) );

	// カード表示用。既存サイトのサムネイル比率 3:4 に合わせる。
	add_image_size( 'lomtheme-card', 480, 640, true );
	// 記事個別ページの主画像用。
	add_image_size( 'lomtheme-hero', 1200, 900, false );
}
add_action( 'after_setup_theme', 'lomtheme_setup' );

/**
 * コンテンツ幅。
 */
function lomtheme_content_width(): void {
	$GLOBALS['content_width'] = 960;
}
add_action( 'after_setup_theme', 'lomtheme_content_width', 0 );

/**
 * サイドバー（ウィジェットエリア）の登録。
 */
function lomtheme_widgets_init(): void {
	register_sidebar( array(
		'name'          => __( 'サイドバー', 'lomtheme' ),
		'id'            => 'sidebar-1',
		'description'   => __( '「最新情報」の下に表示されます。', 'lomtheme' ),
		'before_widget' => '<section id="%1$s" class="lom-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="lom-widget__title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'lomtheme_widgets_init' );

/**
 * ACF が有効でない場合に管理画面で警告する。
 *
 * フレキシブルコンテンツと繰り返しフィールドは ACF Pro 専用機能のため、
 * 無いとページのレイアウト機能が動作しない。テンプレート側は
 * function_exists() で握り潰してあるので、致命的エラーにはならない。
 */
function lomtheme_admin_notice_acf(): void {
	if ( function_exists( 'get_field' ) ) {
		return;
	}
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__(
			'LOM Theme はページのレイアウト機能に Advanced Custom Fields Pro を使用します。有効化してください。',
			'lomtheme'
		)
	);
}
add_action( 'admin_notices', 'lomtheme_admin_notice_acf' );
