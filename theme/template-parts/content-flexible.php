<?php
/**
 * フレキシブルコンテンツの振り分け
 *
 * ACF が無い、またはブロックが未設定の場合は何も出力しない。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'page_content' ) ) {
	return;
}
?>
<div class="lom-blocks">
	<?php
	while ( have_rows( 'page_content' ) ) :
		the_row();
		$lom_layout = (string) get_row_layout();
		get_template_part( 'template-parts/layouts/' . str_replace( '_', '-', $lom_layout ) );
	endwhile;
	?>
</div>
