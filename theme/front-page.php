<?php
/**
 * トップページ
 *
 * メインビジュアル → 最新情報 → 固定ページのブロック の順。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="lom-main lom-main--front" id="lom-content">

	<?php get_template_part( 'template-parts/mainvisual' ); ?>

	<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>

	<?php
	$lom_news = new WP_Query( array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) apply_filters( 'lomtheme_front_news_count', 6 ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	?>

	<?php if ( $lom_news->have_posts() ) : ?>
		<section class="lom-front-news">
			<div class="lom-container">
				<h2 class="lom-section__heading"><?php esc_html_e( '最新情報', 'lomtheme' ); ?></h2>

				<div class="lom-grid">
					<?php
					// エスケープ済みのマークアップを組み立てて返すヘルパー。
					echo lomtheme_render_cards( $lom_news ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>

				<p class="lom-front-news__more">
					<a class="lom-button" href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ); ?>">
						<?php esc_html_e( '一覧を見る', 'lomtheme' ); ?>
					</a>
				</p>
			</div>
		</section>
	<?php endif; ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<div class="lom-container">
			<?php get_template_part( 'template-parts/content-flexible' ); ?>
		</div>
	<?php endwhile; ?>

</main>

<?php
get_footer();
