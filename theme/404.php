<?php
/**
 * 404
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
	<main class="lom-main" id="lom-content">
		<h1 class="lom-page-title"><?php esc_html_e( 'ページが見つかりません', 'lomtheme' ); ?></h1>

		<div class="lom-empty">
			<p><?php esc_html_e( 'お探しのページは移動または削除された可能性があります。', 'lomtheme' ); ?></p>

			<?php get_search_form(); ?>

			<p>
				<a class="lom-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'トップページへ戻る', 'lomtheme' ); ?>
				</a>
			</p>
		</div>
	</main>
</div>

<?php
get_footer();
