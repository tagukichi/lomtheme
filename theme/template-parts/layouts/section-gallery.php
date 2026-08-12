<?php
/**
 * レイアウト: セクション（見出し＋本文＋画像グリッド＋キャプション）
 *
 * 「活動について」の中核。画像の枚数は固定せず、指定列数で折り返す。
 * 参考サイトでは3枚と4枚が混在していたため。
 *
 * 見出しは h2 で固定する。編集者に見出しレベルを選ばせない。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_heading = (string) get_sub_field( 'heading' );
$lom_body    = (string) get_sub_field( 'body' );
$lom_caption = (string) get_sub_field( 'caption' );
$lom_columns = (int) ( get_sub_field( 'columns' ) ?: 4 );
$lom_has_img = have_rows( 'images' );

if ( '' === $lom_heading && '' === trim( wp_strip_all_tags( $lom_body ) ) && ! $lom_has_img ) {
	return;
}
?>
<section class="lom-section">
	<?php if ( '' !== $lom_heading ) : ?>
		<h2 class="lom-section__heading"><?php echo esc_html( $lom_heading ); ?></h2>
	<?php endif; ?>

	<?php if ( '' !== trim( wp_strip_all_tags( $lom_body ) ) ) : ?>
		<div class="lom-section__body lom-prose">
			<?php echo wp_kses_post( $lom_body ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $lom_has_img ) : ?>
		<ul class="lom-gallery" style="--cols:<?php echo esc_attr( (string) $lom_columns ); ?>">
			<?php while ( have_rows( 'images' ) ) : the_row(); ?>
				<?php
				$lom_image = get_sub_field( 'image' );
				if ( ! is_array( $lom_image ) || empty( $lom_image['url'] ) ) {
					continue;
				}
				?>
				<li class="lom-gallery__item">
					<img src="<?php echo esc_url( (string) ( $lom_image['sizes']['large'] ?? $lom_image['url'] ) ); ?>"
					     alt="<?php echo esc_attr( (string) ( $lom_image['alt'] ?? '' ) ); ?>"
					     loading="lazy">
				</li>
			<?php endwhile; ?>
		</ul>
	<?php endif; ?>

	<?php if ( '' !== $lom_caption ) : ?>
		<p class="lom-section__caption"><?php echo esc_html( $lom_caption ); ?></p>
	<?php endif; ?>
</section>
