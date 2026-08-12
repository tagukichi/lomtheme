<?php
/**
 * ヘッダー
 *
 * サイト名は見出しタグにしない。既存サイトでは h3「一般社団法人」＋
 * h2「玉野青年会議所」として出力されていたため、h1 が無いまま h3 から
 * 始まる壊れた見出し順序になっていた。h1 は各テンプレートが1つだけ出す。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#lom-content">
	<?php esc_html_e( 'コンテンツにスキップ', 'lomtheme' ); ?>
</a>

<header class="lom-header">
	<div class="lom-header__inner lom-container lom-container--wide">

		<div class="lom-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="lom-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="lom-header__actions">
			<?php
			$lom_join = function_exists( 'get_field' ) ? get_field( 'join_page_url', 'option' ) : '';
			if ( is_string( $lom_join ) && '' !== $lom_join ) :
				?>
				<a class="lom-header__cta" href="<?php echo esc_url( $lom_join ); ?>">
					<?php esc_html_e( '入会案内', 'lomtheme' ); ?>
				</a>
			<?php endif; ?>

			<button type="button"
			        class="lom-header__toggle"
			        aria-expanded="false"
			        aria-controls="lom-drawer">
				<span class="lom-header__toggle-bar" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'メニューを開く', 'lomtheme' ); ?></span>
			</button>
		</div>
	</div>

	<div class="lom-drawer" id="lom-drawer" hidden>
		<div class="lom-container">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => 'nav',
				'menu_class'     => 'lom-drawer__menu',
				'fallback_cb'    => false,
			) );
			?>
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<div class="lom-container lom-container--wide">
	<?php lomtheme_the_breadcrumb(); ?>
</div>
