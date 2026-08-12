<?php
/**
 * 固定ページ
 *
 * h1 はここで1つだけ出す。編集者が本文中で見出しレベルを選ぶ必要はない。
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
				<article <?php post_class( 'lom-page' ); ?>>
					<h1 class="lom-page-title"><?php the_title(); ?></h1>

					<?php get_template_part( 'template-parts/content-flexible' ); ?>

					<?php
					// フレキシブルコンテンツを使わず、通常の本文で書く場合にも対応する。
					$lom_content = get_the_content();
					if ( '' !== trim( wp_strip_all_tags( $lom_content ) ) ) :
						?>
						<div class="lom-prose">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
