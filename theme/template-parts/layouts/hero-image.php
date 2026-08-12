<?php
/**
 * レイアウト: ヒーロー画像
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_image = get_sub_field( 'image' );
if ( ! is_array( $lom_image ) || empty( $lom_image['url'] ) ) {
	return;
}

$lom_ratio = (string) ( get_sub_field( 'ratio' ) ?: 'wide' );
?>
<figure class="lom-hero lom-hero--<?php echo esc_attr( $lom_ratio ); ?>">
	<img src="<?php echo esc_url( $lom_image['url'] ); ?>"
	     alt="<?php echo esc_attr( (string) ( $lom_image['alt'] ?? '' ) ); ?>"
	     <?php if ( ! empty( $lom_image['width'] ) ) : ?>width="<?php echo esc_attr( (string) $lom_image['width'] ); ?>"<?php endif; ?>
	     <?php if ( ! empty( $lom_image['height'] ) ) : ?>height="<?php echo esc_attr( (string) $lom_image['height'] ); ?>"<?php endif; ?>>
	<?php if ( ! empty( $lom_image['caption'] ) ) : ?>
		<figcaption class="lom-hero__caption"><?php echo esc_html( (string) $lom_image['caption'] ); ?></figcaption>
	<?php endif; ?>
</figure>
