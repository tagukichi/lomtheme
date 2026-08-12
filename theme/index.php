<?php
/**
 * フォールバックテンプレート
 *
 * より具体的なテンプレート（archive.php / single.php / page.php）が
 * 無い場合にのみ使われる。
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

			<h1 class="lom-page-title">
				<?php
				if ( is_home() && ! is_front_page() ) {
					echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
				} else {
					esc_html_e( '最新情報', 'lomtheme' );
				}
				?>
			</h1>

			<?php get_template_part( 'template-parts/archive-filter' ); ?>

			<div class="lom-archive" data-lom-archive>
				<p class="lom-archive__status" data-lom-status role="status" hidden></p>

				<div class="lom-grid" data-lom-grid>
					<?php if ( have_posts() ) : ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_template_part( 'template-parts/card' ); ?>
						<?php endwhile; ?>
					<?php else : ?>
						<p class="lom-empty"><?php esc_html_e( '投稿がまだありません。', 'lomtheme' ); ?></p>
					<?php endif; ?>
				</div>

				<?php lomtheme_the_pagination( $GLOBALS['wp_query'] ); ?>
			</div>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
