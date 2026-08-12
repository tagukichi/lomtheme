<?php
/**
 * サイドバー「最新情報」
 *
 * 既存サイトの jaycee-sb を踏襲。JCサイトの定番なのでテーマ標準にする。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lomtheme_show_sidebar() ) {
	return;
}

$lom_latest = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => (int) apply_filters( 'lomtheme_sidebar_count', 6 ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
?>
<aside class="lom-sidebar">
	<?php if ( $lom_latest->have_posts() ) : ?>
		<section class="lom-sb">
			<h2 class="lom-sb__title"><?php esc_html_e( '最新情報', 'lomtheme' ); ?></h2>

			<ul class="lom-sb__list">
				<?php while ( $lom_latest->have_posts() ) : $lom_latest->the_post(); ?>
					<?php $lom_term = lomtheme_primary_term(); ?>
					<li class="lom-sb__item">
						<a class="lom-sb__link" href="<?php the_permalink(); ?>">
							<span class="lom-sb__thumb">
								<?php lomtheme_the_thumbnail( 'thumbnail' ); ?>
							</span>
							<span class="lom-sb__main">
								<?php lomtheme_the_date(); ?>
								<span class="lom-sb__heading"><?php the_title(); ?></span>
								<?php lomtheme_the_badge( $lom_term ); ?>
							</span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		</section>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php endif; ?>
</aside>
