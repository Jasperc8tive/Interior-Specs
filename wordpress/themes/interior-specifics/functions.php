<?php
/**
 * Interior Specifics theme bootstrap.
 *
 * Presentation only: content types, settings, lead handling and structured data live in the
 * Interior Specifics Core plugin. Theme helpers are prefixed ist_; plugin helpers isp_.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

define( 'IST_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'IST_DIR', get_template_directory() );
define( 'IST_URI', get_template_directory_uri() );

require_once IST_DIR . '/inc/setup.php';
require_once IST_DIR . '/inc/helpers.php';
require_once IST_DIR . '/inc/assets.php';
require_once IST_DIR . '/inc/blocks.php';
require_once IST_DIR . '/inc/nav.php';
require_once IST_DIR . '/inc/template-tags.php';

// Feature modules: lead-form rendering and <head> extras (favicon, theme colour).
foreach ( array( 'forms', 'head' ) as $ist_module ) {
	if ( file_exists( IST_DIR . "/inc/{$ist_module}.php" ) ) {
		require_once IST_DIR . "/inc/{$ist_module}.php";
	}
}
unset( $ist_module );
