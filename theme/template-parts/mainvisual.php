<?php
/**
 * メインビジュアル・スライダー
 *
 * 既存サイトの mvts を踏襲（ドット＋サムネイルの二重ナビ、スライドにリンク可）。
 * JS が無い環境では1枚目が静止画として表示される。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'mainvisual_slides' ) ) {
	return;
}

$lom_slides = array();
while ( have_rows( 'mainvisual_slides' ) ) {
	the_row();
	$lom_image = get_sub_field( 'image' );
	if ( ! is_array( $lom_image ) || empty( $lom_image['url'] ) ) {
		continue;
	}
	$lom_slides[] = array(
		'url'  => (string) ( $lom_image['sizes']['large'] ?? $lom_image['url'] ),
		'full' => (string) $lom_image['url'],
		'thumb' => (string) ( $lom_image['sizes']['thumbnail'] ?? $lom_image['url'] ),
		'alt'  => (string) get_sub_field( 'alt' ),
		'link' => (string) get_sub_field( 'link' ),
	);
}

if ( array() === $lom_slides ) {
	return;
}

$lom_multiple = count( $lom_slides ) > 1;
?>
<section class="lom-mv" data-lom-mv aria-roledescription="carousel"
         aria-label="<?php esc_attr_e( 'メインビジュアル', 'lomtheme' ); ?>">

	<div class="lom-mv__viewport" data-lom-mv-viewport>
		<?php foreach ( $lom_slides as $lom_i => $lom_slide ) : ?>
			<?php
			$lom_active = 0 === $lom_i;
			$lom_tag    = '' !== $lom_slide['link'] ? 'a' : 'div';
			?>
			<<?php echo esc_attr( $lom_tag ); ?>
				class="lom-mv__slide<?php echo $lom_active ? ' is-active' : ''; ?>"
				<?php if ( 'a' === $lom_tag ) : ?>href="<?php echo esc_url( $lom_slide['link'] ); ?>"<?php endif; ?>
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr( sprintf( '%1$d / %2$d', $lom_i + 1, count( $lom_slides ) ) ); ?>"
				<?php echo $lom_active ? '' : 'aria-hidden="true"'; ?>>
				<img src="<?php echo esc_url( $lom_slide['url'] ); ?>"
				     alt="<?php echo esc_attr( $lom_slide['alt'] ); ?>"
				     <?php echo $lom_active ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
			</<?php echo esc_attr( $lom_tag ); ?>>
		<?php endforeach; ?>

		<?php if ( $lom_multiple ) : ?>
			<?php // ドットはビューポート内に置く。外に出すとサムネイルの上に重なる。 ?>
			<div class="lom-mv__dots" role="tablist"
			     aria-label="<?php esc_attr_e( 'スライドを選択', 'lomtheme' ); ?>">
				<?php foreach ( $lom_slides as $lom_i => $lom_slide ) : ?>
					<button type="button"
					        class="lom-mv__dot<?php echo 0 === $lom_i ? ' is-active' : ''; ?>"
					        data-lom-mv-goto="<?php echo esc_attr( (string) $lom_i ); ?>"
					        role="tab"
					        aria-selected="<?php echo 0 === $lom_i ? 'true' : 'false'; ?>">
						<span class="screen-reader-text">
							<?php echo esc_html( sprintf( /* translators: %d: スライド番号 */ __( 'スライド %d', 'lomtheme' ), $lom_i + 1 ) ); ?>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $lom_multiple ) : ?>
		<div class="lom-mv__thumbs">
			<?php foreach ( $lom_slides as $lom_i => $lom_slide ) : ?>
				<button type="button"
				        class="lom-mv__thumb<?php echo 0 === $lom_i ? ' is-active' : ''; ?>"
				        data-lom-mv-goto="<?php echo esc_attr( (string) $lom_i ); ?>">
					<img src="<?php echo esc_url( $lom_slide['thumb'] ); ?>" alt="" loading="lazy">
					<span class="screen-reader-text"><?php echo esc_html( $lom_slide['alt'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
