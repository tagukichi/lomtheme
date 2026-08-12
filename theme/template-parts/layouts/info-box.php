<?php
/**
 * レイアウト: 案内ボックス（番号付きリスト＋補足）
 *
 * 「入会申込みについて」のような手続き案内。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_heading = (string) get_sub_field( 'heading' );
$lom_note    = (string) get_sub_field( 'note' );
$lom_has_items = have_rows( 'items' );

if ( '' === $lom_heading && ! $lom_has_items && '' === trim( wp_strip_all_tags( $lom_note ) ) ) {
	return;
}
?>
<section class="lom-info">
	<?php if ( '' !== $lom_heading ) : ?>
		<h2 class="lom-info__heading"><?php echo esc_html( $lom_heading ); ?></h2>
	<?php endif; ?>

	<?php if ( $lom_has_items ) : ?>
		<ol class="lom-info__list">
			<?php while ( have_rows( 'items' ) ) : the_row(); ?>
				<?php $lom_item = (string) get_sub_field( 'text' ); ?>
				<?php if ( '' === trim( $lom_item ) ) { continue; } ?>
				<li><?php echo esc_html( $lom_item ); ?></li>
			<?php endwhile; ?>
		</ol>
	<?php endif; ?>

	<?php if ( '' !== trim( wp_strip_all_tags( $lom_note ) ) ) : ?>
		<div class="lom-info__note lom-prose">
			<?php echo wp_kses_post( $lom_note ); ?>
		</div>
	<?php endif; ?>
</section>
