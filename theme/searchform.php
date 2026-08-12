<?php
/**
 * 検索フォーム
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_id = wp_unique_id( 'lom-search-' );
?>
<form class="lom-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $lom_id ); ?>">
		<?php esc_html_e( 'サイト内を検索', 'lomtheme' ); ?>
	</label>
	<input type="search"
	       id="<?php echo esc_attr( $lom_id ); ?>"
	       class="lom-search__input"
	       name="s"
	       value="<?php echo esc_attr( get_search_query() ); ?>"
	       placeholder="<?php esc_attr_e( 'キーワードを入力', 'lomtheme' ); ?>">
	<button type="submit" class="lom-search__submit">
		<?php esc_html_e( '検索', 'lomtheme' ); ?>
	</button>
</form>
