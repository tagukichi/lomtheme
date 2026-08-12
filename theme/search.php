<?php
/**
 * 検索結果
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
				printf(
					/* translators: %s: 検索キーワード */
					esc_html__( '「%s」の検索結果', 'lomtheme' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>

			<?php if ( have_posts() ) : ?>
				<p class="lom-archive__count">
					<?php
					printf(
						/* translators: %s: 件数 */
						esc_html__( '%s件見つかりました。', 'lomtheme' ),
						esc_html( number_format_i18n( (int) $GLOBALS['wp_query']->found_posts ) )
					);
					?>
				</p>

				<div class="lom-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/card' ); ?>
					<?php endwhile; ?>
				</div>

				<?php lomtheme_the_pagination( $GLOBALS['wp_query'] ); ?>
			<?php else : ?>
				<div class="lom-empty">
					<p><?php esc_html_e( '該当する記事が見つかりませんでした。別のキーワードでお試しください。', 'lomtheme' ); ?></p>
					<?php get_search_form(); ?>
				</div>
			<?php endif; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
