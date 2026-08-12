<?php
/**
 * レイアウト: リード文（中央・強調）
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_text = (string) get_sub_field( 'text' );
if ( '' === trim( wp_strip_all_tags( $lom_text ) ) ) {
	return;
}
?>
<p class="lom-lead"><?php echo wp_kses( $lom_text, array( 'br' => array() ) ); ?></p>
