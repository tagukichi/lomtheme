<?php
/**
 * レイアウト: 本文
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_body = (string) get_sub_field( 'body' );
if ( '' === trim( wp_strip_all_tags( $lom_body ) ) ) {
	return;
}

$lom_boxed = (bool) get_sub_field( 'boxed' );
?>
<div class="lom-prose<?php echo $lom_boxed ? ' lom-prose--boxed' : ''; ?>">
	<?php echo wp_kses_post( $lom_body ); ?>
</div>
