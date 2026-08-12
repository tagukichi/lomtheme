<?php
/**
 * 分類タブ
 *
 * JavaScript が動く環境では AJAX で絞り込む。動かない場合でも
 * カテゴリーアーカイブへの通常リンクとして機能する（プログレッシブ
 * エンハンスメント）。既存サイトの実装は button 要素で、JS無効時は
 * 何も起きなかった。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_terms = lomtheme_filter_terms();

if ( count( $lom_terms ) < 2 ) {
	return; // 分類が1つ以下ならタブを出す意味がない。
}

$lom_current = is_category() ? get_queried_object() : null;
?>
<nav class="lom-filter" aria-label="<?php esc_attr_e( '分類で絞り込む', 'lomtheme' ); ?>">
	<a class="lom-filter__tab<?php echo $lom_current instanceof WP_Term ? '' : ' is-active'; ?>"
	   href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ); ?>"
	   data-term=""
	   <?php echo $lom_current instanceof WP_Term ? '' : 'aria-current="page"'; ?>>
		<?php esc_html_e( 'ALL', 'lomtheme' ); ?>
	</a>
	<?php foreach ( $lom_terms as $lom_term ) : ?>
		<?php $lom_is_active = $lom_current instanceof WP_Term && $lom_current->term_id === $lom_term->term_id; ?>
		<a class="lom-filter__tab<?php echo $lom_is_active ? ' is-active' : ''; ?>"
		   href="<?php echo esc_url( (string) get_term_link( $lom_term ) ); ?>"
		   data-term="<?php echo esc_attr( $lom_term->slug ); ?>"
		   style="--badge:<?php echo esc_attr( lomtheme_term_color( $lom_term ) ); ?>"
		   <?php echo $lom_is_active ? 'aria-current="page"' : ''; ?>>
			<?php echo esc_html( $lom_term->name ); ?>
		</a>
	<?php endforeach; ?>
</nav>
