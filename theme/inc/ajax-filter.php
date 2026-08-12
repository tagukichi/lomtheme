<?php
/**
 * アーカイブの分類タブ絞り込み（AJAX）
 *
 * JS が無効でもタブはカテゴリーアーカイブへのリンクとして機能する
 * （template-parts/archive-filter.php を参照）。ここは上乗せの体験。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1ページあたりの表示件数。
 */
function lomtheme_posts_per_page(): int {
	return (int) apply_filters( 'lomtheme_posts_per_page', 12 );
}

/**
 * 分類タブの絞り込みリクエストを処理する。
 */
function lomtheme_ajax_filter_posts(): void {
	check_ajax_referer( 'lomtheme_filter', 'nonce' );

	$slug  = isset( $_POST['term'] ) ? sanitize_title( wp_unslash( (string) $_POST['term'] ) ) : '';
	$paged = isset( $_POST['paged'] ) ? max( 1, absint( wp_unslash( (string) $_POST['paged'] ) ) ) : 1;

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => lomtheme_posts_per_page(),
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
	);

	if ( '' !== $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term instanceof WP_Term ) {
			wp_send_json_error( array( 'message' => __( '分類が見つかりません。', 'lomtheme' ) ), 404 );
		}
		$args['cat'] = $term->term_id;
	}

	$query = new WP_Query( $args );

	wp_send_json_success( array(
		'html'      => lomtheme_render_cards( $query ),
		'maxPages'  => (int) $query->max_num_pages,
		'found'     => (int) $query->found_posts,
	) );
}
add_action( 'wp_ajax_lomtheme_filter_posts', 'lomtheme_ajax_filter_posts' );
add_action( 'wp_ajax_nopriv_lomtheme_filter_posts', 'lomtheme_ajax_filter_posts' );
