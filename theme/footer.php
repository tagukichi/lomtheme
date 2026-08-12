<?php
/**
 * フッター
 *
 * 住所や連絡先は ACF のオプションページから取得する。
 * 各LOMはテーマファイルを触らずに設定できる。
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lom_opt = static function ( string $key ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$value = get_field( $key, 'option' );

	return is_string( $value ) ? trim( $value ) : '';
};

$lom_socials = array(
	'youtube'   => __( 'YouTube', 'lomtheme' ),
	'facebook'  => __( 'Facebook', 'lomtheme' ),
	'instagram' => __( 'Instagram', 'lomtheme' ),
	'x'         => __( 'X', 'lomtheme' ),
);
?>

<footer class="lom-footer">
	<div class="lom-footer__inner lom-container lom-container--wide">

		<div class="lom-footer__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>
		</div>

		<div class="lom-footer__address">
			<p class="lom-footer__org"><?php echo esc_html( $lom_opt( 'org_name' ) ?: get_bloginfo( 'name' ) ); ?></p>
			<?php if ( '' !== $lom_opt( 'postal_code' ) || '' !== $lom_opt( 'address' ) ) : ?>
				<p>
					<?php if ( '' !== $lom_opt( 'postal_code' ) ) : ?>
						<?php echo esc_html( '〒' . $lom_opt( 'postal_code' ) ); ?><br>
					<?php endif; ?>
					<?php echo esc_html( $lom_opt( 'address' ) ); ?>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $lom_opt( 'tel' ) ) : ?>
				<p>
					TEL <a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $lom_opt( 'tel' ) ) ); ?>"><?php echo esc_html( $lom_opt( 'tel' ) ); ?></a>
					<?php if ( '' !== $lom_opt( 'fax' ) ) : ?>
						<br>FAX <?php echo esc_html( $lom_opt( 'fax' ) ); ?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $lom_opt( 'email' ) ) : ?>
				<p>
					<a href="<?php echo esc_url( 'mailto:' . $lom_opt( 'email' ) ); ?>"><?php echo esc_html( $lom_opt( 'email' ) ); ?></a>
				</p>
			<?php endif; ?>
		</div>

		<div class="lom-footer__links">
			<ul class="lom-footer__social">
				<?php foreach ( $lom_socials as $lom_key => $lom_label ) : ?>
					<?php $lom_url = $lom_opt( 'social_' . $lom_key ); ?>
					<?php if ( '' === $lom_url ) { continue; } ?>
					<li>
						<a href="<?php echo esc_url( $lom_url ); ?>" rel="noopener noreferrer" target="_blank">
							<span class="screen-reader-text"><?php echo esc_html( $lom_label ); ?></span>
							<span class="lom-icon lom-icon--<?php echo esc_attr( $lom_key ); ?>" aria-hidden="true"></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'container'      => 'nav',
				'menu_class'     => 'lom-footer__menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
			?>
		</div>
	</div>

	<div class="lom-footer__copyright">
		<div class="lom-container lom-container--wide">
			<small>
				<?php
				$lom_copy = $lom_opt( 'copyright' );
				echo esc_html( '' !== $lom_copy ? $lom_copy : sprintf(
					/* translators: 1: 発行年, 2: サイト名 */
					__( '© %1$s %2$s All Rights Reserved.', 'lomtheme' ),
					date_i18n( 'Y' ),
					get_bloginfo( 'name' )
				) );
				?>
			</small>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
