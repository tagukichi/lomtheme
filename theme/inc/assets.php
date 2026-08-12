<?php
/**
 * CSS / JS の読み込み
 *
 * 既存サイトでは8つのコンポーネントCSSが、使っていないページも含めて
 * 全ページで読み込まれていた。ここでは実際に使う画面でのみ読み込む。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts の URL。
 *
 * 和文ウェブフォントは重く、外部読み込みを避けたいLOMもあるため、
 * フィルタで空文字を返せば読み込みを止められるようにしてある。
 */
function lomtheme_google_fonts_url(): string {
	return (string) apply_filters(
		'lomtheme_google_fonts_url',
		'https://fonts.googleapis.com/css2?family=M+PLUS+1:wght@400;500;700;800&display=swap'
	);
}

/**
 * このリクエストでフレキシブルコンテンツが使われるか。
 *
 * have_rows() ではなく get_field() で判定する。have_rows() が true を
 * 返すとACF内部のループポインタが進んだままになり、後段の実際の
 * ループが1行目を取りこぼす。
 */
function lomtheme_uses_flexible_content(): bool {
	if ( ! is_singular() || ! function_exists( 'get_field' ) ) {
		return false;
	}

	$rows = get_field( 'page_content', get_queried_object_id() );

	return is_array( $rows ) && array() !== $rows;
}

/**
 * このリクエストでメインビジュアルが使われるか。
 */
function lomtheme_uses_mainvisual(): bool {
	if ( ! is_front_page() || ! function_exists( 'get_field' ) ) {
		return false;
	}

	$rows = get_field( 'mainvisual_slides', get_queried_object_id() );

	return is_array( $rows ) && array() !== $rows;
}

/**
 * フロント側のアセットを登録・読み込みする。
 */
function lomtheme_enqueue_assets(): void {
	$fonts = lomtheme_google_fonts_url();
	if ( '' !== $fonts ) {
		wp_enqueue_style( 'lomtheme-fonts', $fonts, array(), null );
	}

	wp_enqueue_style(
		'lomtheme-base',
		LOMTHEME_URI . '/assets/css/base.css',
		array(),
		LOMTHEME_VERSION
	);

	// --- 条件付きで読み込むコンポーネント ---------------------------------

	$components = array(
		'chrome'      => true, // ヘッダーとフッター。全ページで必要。
		'archive'     => is_home() || is_archive() || is_search() || is_front_page(),
		'sidebar'     => lomtheme_show_sidebar(),
		'single'      => is_singular( 'post' ),
		'layouts'     => lomtheme_uses_flexible_content(),
		'mainvisual'  => lomtheme_uses_mainvisual(),
	);

	foreach ( $components as $name => $needed ) {
		if ( ! $needed ) {
			continue;
		}
		wp_enqueue_style(
			"lomtheme-{$name}",
			LOMTHEME_URI . "/assets/css/components/{$name}.css",
			array( 'lomtheme-base' ),
			LOMTHEME_VERSION
		);
	}

	// --- スクリプト -------------------------------------------------------

	wp_enqueue_script(
		'lomtheme-navigation',
		LOMTHEME_URI . '/assets/js/navigation.js',
		array(),
		LOMTHEME_VERSION,
		true
	);

	if ( $components['mainvisual'] ) {
		wp_enqueue_script(
			'lomtheme-mainvisual',
			LOMTHEME_URI . '/assets/js/mainvisual.js',
			array(),
			LOMTHEME_VERSION,
			true
		);
	}

	if ( $components['archive'] ) {
		wp_enqueue_script(
			'lomtheme-archive-filter',
			LOMTHEME_URI . '/assets/js/archive-filter.js',
			array(),
			LOMTHEME_VERSION,
			true
		);
		wp_localize_script( 'lomtheme-archive-filter', 'lomthemeArchive', array(
			'endpoint' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'action'   => 'lomtheme_filter_posts',
			'nonce'    => wp_create_nonce( 'lomtheme_filter' ),
			'loading'  => __( '読み込み中…', 'lomtheme' ),
			'empty'    => __( '該当する投稿がありません。', 'lomtheme' ),
			'error'    => __( '読み込みに失敗しました。時間をおいて再度お試しください。', 'lomtheme' ),
		) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'lomtheme_enqueue_assets' );
