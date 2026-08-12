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
<p class="lom-lead"><?php
	// ACF の new_lines 設定に依存せず、テンプレート側で改行を扱う。
	echo nl2br( esc_html( $lom_text ) );
?></p>
