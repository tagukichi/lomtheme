<?php
/**
 * レイアウト: カード（画像＋テキスト）
 *
 * 「入会のメリット」のような、小見出し＋画像＋説明文の繰り返し。
 * 小見出しは h3 で固定（セクション見出しの h2 の下位）。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_heading = (string) get_sub_field( 'heading' );

if ( ! have_rows( 'cards' ) ) {
	return;
}
?>
<section class="lom-features">
	<?php if ( '' !== $lom_heading ) : ?>
		<h2 class="lom-section__heading"><?php echo esc_html( $lom_heading ); ?></h2>
	<?php endif; ?>

	<?php while ( have_rows( 'cards' ) ) : the_row(); ?>
		<?php
		$lom_card_heading = (string) get_sub_field( 'heading' );
		$lom_card_body    = (string) get_sub_field( 'body' );
		$lom_card_image   = get_sub_field( 'image' );
		$lom_position     = (string) ( get_sub_field( 'image_position' ) ?: 'left' );
		$lom_has_image    = is_array( $lom_card_image ) && ! empty( $lom_card_image['url'] );
		?>
		<article class="lom-feature lom-feature--image-<?php echo esc_attr( $lom_position ); ?>">
			<?php if ( '' !== $lom_card_heading ) : ?>
				<h3 class="lom-feature__heading"><?php echo esc_html( $lom_card_heading ); ?></h3>
			<?php endif; ?>

			<div class="lom-feature__inner">
				<?php if ( $lom_has_image ) : ?>
					<div class="lom-feature__media">
						<img src="<?php echo esc_url( (string) ( $lom_card_image['sizes']['medium_large'] ?? $lom_card_image['url'] ) ); ?>"
						     alt="<?php echo esc_attr( (string) ( $lom_card_image['alt'] ?? '' ) ); ?>"
						     loading="lazy">
					</div>
				<?php endif; ?>

				<?php if ( '' !== trim( wp_strip_all_tags( $lom_card_body ) ) ) : ?>
					<div class="lom-feature__body lom-prose">
						<?php echo wp_kses_post( $lom_card_body ); ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</section>
