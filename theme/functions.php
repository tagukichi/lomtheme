<?php
/**
 * LOM Theme ブートストラップ
 *
 * @package LomTheme
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LOMTHEME_VERSION', '0.1.0' );
define( 'LOMTHEME_DIR', get_template_directory() );
define( 'LOMTHEME_URI', get_template_directory_uri() );

require_once LOMTHEME_DIR . '/inc/setup.php';
require_once LOMTHEME_DIR . '/inc/assets.php';
require_once LOMTHEME_DIR . '/inc/template-tags.php';
require_once LOMTHEME_DIR . '/inc/news.php';
require_once LOMTHEME_DIR . '/inc/acf-fields.php';
require_once LOMTHEME_DIR . '/inc/ajax-filter.php';
