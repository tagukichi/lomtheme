<?php
/**
 * 投稿個別
 *
 * 既存サイトの jaycee-single を踏襲した2カラム構成
 * （左: タイトルとメタ / 右: 主画像）。モバイルでは縦積み。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="lom-container">
	<div class="lom-layout<?php echo lomtheme_show_sidebar() ? ' lom-layout--with-sidebar' : ''; ?>">
		<main class="lom-main" id="lom-content">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $lom_term = lomtheme_primary_term(); ?>

				<article <?php post_class( 'lom-single' ); ?>>
					<div class="lom-single__card">
						<div class="lom-single__head">
							<div class="lom-single__text">
								<h1 class="lom-single__title"><?php the_title(); ?></h1>
								<div class="lom-single__meta">
									<?php lomtheme_the_date(); ?>
									<?php lomtheme_the_badge( $lom_term, 'outline' ); ?>
								</div>
							</div>

							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="lom-single__figure">
									<?php lomtheme_the_thumbnail( 'lomtheme-hero' ); ?>
								</figure>
							<?php endif; ?>
						</div>
					</div>

					<div class="lom-prose lom-single__body">
						<?php
						the_content();
						wp_link_pages( array(
							'before' => '<nav class="lom-pagination">',
							'after'  => '</nav>',
						) );
						?>
					</div>

					<nav class="lom-postnav" aria-label="<?php esc_attr_e( '前後の投稿', 'lomtheme' ); ?>">
						<?php
						previous_post_link( '<span class="lom-postnav__prev">%link</span>', '&laquo; %title' );
						next_post_link( '<span class="lom-postnav__next">%link</span>', '%title &raquo;' );
						?>
					</nav>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</article>
			<?php endwhile; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
