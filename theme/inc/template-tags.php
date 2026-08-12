<?php
/**
 * テンプレート用のヘルパー
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 分類バッジを出力する。
 *
 * 色は CSS 変数 --badge で渡す。テーマのCSSは色を持たない。
 *
 * @param string $style 'solid'（塗り）または 'outline'（線）。
 */
function lomtheme_the_badge( ?WP_Term $term, string $style = 'solid' ): void {
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	printf(
		'<span class="lom-badge%1$s" style="--badge:%2$s">%3$s</span>',
		'outline' === $style ? ' lom-badge--outline' : '',
		esc_attr( lomtheme_term_color( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * 日付を「2025.11.15」形式で出力する。
 */
function lomtheme_the_date( int $post_id = 0 ): void {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	printf(
		'<time class="lom-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C, $post_id ) ),
		esc_html( get_the_date( 'Y.m.d', $post_id ) )
	);
}

/**
 * サムネイルを出力する。無い場合はプレースホルダを出す。
 *
 * alt にはタイトルを入れる。既存サイトでは alt が空の画像が多く、
 * アクセシビリティ上の問題になっていたため、テンプレート側で担保する。
 */
function lomtheme_the_thumbnail( string $size = 'lomtheme-card', int $post_id = 0 ): void {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, $size, array(
			'alt'     => the_title_attribute( array( 'echo' => false, 'post' => $post_id ) ),
			'loading' => 'lazy',
		) );
		return;
	}

	printf(
		'<span class="lom-thumb__placeholder" aria-hidden="true"></span>'
	);
}

/**
 * このページでサイドバーを表示するか。
 *
 * ページ単位の ACF 設定を優先し、未設定ならデフォルト表示。
 * トップページは全幅で使うことが多いので既定で非表示。
 */
function lomtheme_show_sidebar(): bool {
	if ( is_front_page() ) {
		$show = false;
	} else {
		$show = true;
	}

	// wp_enqueue_scripts から呼ばれる時点ではループ内ではないため、
	// グローバルの $post に頼らず対象IDを明示する。
	if ( is_singular() && function_exists( 'get_field' ) ) {
		$field = get_field( 'show_sidebar', get_queried_object_id() );
		if ( is_bool( $field ) ) {
			$show = $field;
		}
	}

	return (bool) apply_filters( 'lomtheme_show_sidebar', $show );
}

/**
 * パンくずを出力する。
 *
 * Yoast SEO が有効ならそちらに任せる（構造化データを出してくれるため）。
 */
function lomtheme_the_breadcrumb(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="lom-breadcrumb" aria-label="' . esc_attr__( 'パンくず', 'lomtheme' ) . '">', '</nav>' );
		return;
	}

	$items = array(
		array(
			'label' => __( 'ホーム', 'lomtheme' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$term = lomtheme_primary_term();
		if ( $term instanceof WP_Term ) {
			$items[] = array(
				'label' => $term->name,
				'url'   => get_term_link( $term ),
			);
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor_id ) {
			$items[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_search() ) {
		/* translators: %s: 検索キーワード */
		$items[] = array( 'label' => sprintf( __( '「%s」の検索結果', 'lomtheme' ), get_search_query() ), 'url' => '' );
	} elseif ( is_archive() ) {
		$items[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'label' => __( 'ページが見つかりません', 'lomtheme' ), 'url' => '' );
	}

	echo '<nav class="lom-breadcrumb" aria-label="' . esc_attr__( 'パンくず', 'lomtheme' ) . '"><ol>';
	foreach ( $items as $item ) {
		echo '<li>';
		if ( '' !== $item['url'] && ! is_wp_error( $item['url'] ) ) {
			printf( '<a href="%1$s">%2$s</a>', esc_url( (string) $item['url'] ), esc_html( $item['label'] ) );
		} else {
			echo '<span aria-current="page">' . esc_html( $item['label'] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

/**
 * カード群のHTMLを組み立てて返す。
 *
 * アーカイブテンプレートと AJAX 絞り込みの両方から呼ばれるため、
 * echo せず文字列で返す。
 */
function lomtheme_render_cards( WP_Query $query ): string {
	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/card' );
	}
	wp_reset_postdata();

	return (string) ob_get_clean();
}

/**
 * ページネーションを出力する。
 */
function lomtheme_the_pagination( ?WP_Query $query = null ): void {
	$links = paginate_links( array(
		'total'     => $query instanceof WP_Query ? $query->max_num_pages : 0,
		'type'      => 'array',
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
	) );

	if ( ! is_array( $links ) || array() === $links ) {
		return;
	}

	echo '<nav class="lom-pagination" aria-label="' . esc_attr__( 'ページ送り', 'lomtheme' ) . '">';
	echo implode( '', $links ); // paginate_links() はエスケープ済みのマークアップを返す。
	echo '</nav>';
}
