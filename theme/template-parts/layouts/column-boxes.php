<?php
/**
 * レイアウト: 箇条書きボックス（横並び）
 *
 * 「入会資格」「費用について」のような並列情報。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! have_rows( 'boxes' ) ) {
	return;
}

$lom_columns = (int) ( get_sub_field( 'columns' ) ?: 2 );
?>
<div class="lom-boxes" style="--cols:<?php echo esc_attr( (string) $lom_columns ); ?>">
	<?php while ( have_rows( 'boxes' ) ) : the_row(); ?>
		<?php $lom_box_heading = (string) get_sub_field( 'heading' ); ?>
		<section class="lom-box">
			<?php if ( '' !== $lom_box_heading ) : ?>
				<h2 class="lom-box__heading"><?php echo esc_html( $lom_box_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( have_rows( 'items' ) ) : ?>
				<ul class="lom-box__list">
					<?php while ( have_rows( 'items' ) ) : the_row(); ?>
						<?php $lom_item = (string) get_sub_field( 'text' ); ?>
						<?php if ( '' === trim( $lom_item ) ) { continue; } ?>
						<li><?php echo esc_html( $lom_item ); ?></li>
					<?php endwhile; ?>
				</ul>
			<?php endif; ?>
		</section>
	<?php endwhile; ?>
</div>
