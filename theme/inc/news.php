<?php
/**
 * お知らせの分類
 *
 * 既存サイトの分類（お知らせ / 開催案内 / 開催報告）は JC 共通で使えるため、
 * テーマ有効化時に標準カテゴリーとして用意する。
 *
 * カスタムタクソノミーではなく通常のカテゴリーを使う理由:
 * 各LOMの担当者にとって「カテゴリー」は既知の概念であり、
 * 独自タクソノミーを増やすと引き継ぎ時に迷いが生じるため。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 標準で用意する分類。
 *
 * @return array<string, array{name: string, color: string}>
 */
function lomtheme_default_terms(): array {
	return apply_filters( 'lomtheme_default_terms', array(
		'news'         => array(
			'name'  => __( 'お知らせ', 'lomtheme' ),
			'color' => '#888888',
		),
		'event-info'   => array(
			'name'  => __( '開催案内', 'lomtheme' ),
			'color' => '#e8a33d',
		),
		'event-report' => array(
			'name'  => __( '開催報告', 'lomtheme' ),
			'color' => '#0097d7',
		),
	) );
}

/**
 * テーマ有効化時に標準分類を作成する。
 *
 * 既に同じスラッグがあれば触らない。色はタームメタに保存するので、
 * 管理画面から後で変更できる。
 */
function lomtheme_seed_terms(): void {
	foreach ( lomtheme_default_terms() as $slug => $term ) {
		$existing = get_term_by( 'slug', $slug, 'category' );

		if ( $existing instanceof WP_Term ) {
			$term_id = $existing->term_id;
		} else {
			$created = wp_insert_term( $term['name'], 'category', array( 'slug' => $slug ) );
			if ( is_wp_error( $created ) ) {
				continue;
			}
			$term_id = (int) $created['term_id'];
		}

		if ( '' === (string) get_term_meta( $term_id, 'lomtheme_color', true ) ) {
			update_term_meta( $term_id, 'lomtheme_color', $term['color'] );
		}
	}
}
add_action( 'after_switch_theme', 'lomtheme_seed_terms' );

/**
 * 分類の表示色を返す。
 *
 * タームメタ → 標準分類の既定値 → フォールバック の順に解決する。
 * テーマ側が色を持たないので、LOMが分類を増やしても破綻しない。
 */
function lomtheme_term_color( ?WP_Term $term ): string {
	if ( ! $term instanceof WP_Term ) {
		return '#999999';
	}

	$stored = (string) get_term_meta( $term->term_id, 'lomtheme_color', true );
	if ( '' !== $stored ) {
		return $stored;
	}

	$defaults = lomtheme_default_terms();
	if ( isset( $defaults[ $term->slug ]['color'] ) ) {
		return $defaults[ $term->slug ]['color'];
	}

	return '#999999';
}

/**
 * 投稿の代表分類を1つ返す。
 *
 * カードのバッジは1つしか置けないため、複数付いている場合は
 * 最も投稿数の少ない（＝具体的な）分類を選ぶ。
 */
function lomtheme_primary_term( int $post_id = 0 ): ?WP_Term {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	$terms = get_the_terms( $post_id, 'category' );
	if ( ! is_array( $terms ) || array() === $terms ) {
		return null;
	}

	usort( $terms, static fn( WP_Term $a, WP_Term $b ): int => $a->count <=> $b->count );

	return $terms[0];
}

/**
 * 分類タブに出す一覧を返す。
 *
 * 投稿が1件も無い分類はタブに出さない（空タブは押しても何も出ないため）。
 *
 * @return WP_Term[]
 */
function lomtheme_filter_terms(): array {
	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'term_id',
	) );

	return is_wp_error( $terms ) ? array() : $terms;
}
