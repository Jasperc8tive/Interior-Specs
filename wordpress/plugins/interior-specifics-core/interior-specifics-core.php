<?php
/**
 * Plugin Name:       Interior Specifics Core
 * Plugin URI:        https://interiorspecifics.com/
 * Description:       Content model, site settings, lead capture, structured data and seeding for the Interior Specifics website. Keeps content safe when the theme changes.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Interior Specifics
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       interior-specifics-core
 * Update URI:        false
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

define( 'ISP_VERSION', '1.0.0' );
define( 'ISP_FILE', __FILE__ );
define( 'ISP_DIR', plugin_dir_path( __FILE__ ) );
define( 'ISP_URL', plugin_dir_url( __FILE__ ) );

require_once ISP_DIR . 'includes/helpers.php';
require_once ISP_DIR . 'includes/settings.php';
require_once ISP_DIR . 'includes/post-types.php';
require_once ISP_DIR . 'includes/meta.php';
require_once ISP_DIR . 'includes/forms.php';
require_once ISP_DIR . 'includes/leads.php';
require_once ISP_DIR . 'includes/mailer.php';
require_once ISP_DIR . 'includes/media.php';
require_once ISP_DIR . 'includes/hardening.php';

// Modules added in later build phases; loaded when present.
foreach ( array( 'admin-leads', 'seo', 'editor' ) as $isp_module ) {
	if ( file_exists( ISP_DIR . "includes/{$isp_module}.php" ) ) {
		require_once ISP_DIR . "includes/{$isp_module}.php";
	}
}
unset( $isp_module );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once ISP_DIR . 'includes/cli.php';
}

/**
 * Register content types before flushing so the new permalinks exist on activation, and close
 * comments on any content created before the plugin was active.
 */
function isp_activate(): void {
	isp_register_post_types();
	flush_rewrite_rules();
	isp_close_existing_comments();
}
register_activation_hook( __FILE__, 'isp_activate' );

/**
 * Drop the plugin's content types before flushing, or the regenerated rules would still
 * contain /services/ and /showroom/ (they were registered on init during this request).
 */
function isp_deactivate(): void {
	foreach ( array( 'is_service', 'is_project', 'is_testimonial', 'is_lead' ) as $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			unregister_post_type( $post_type );
		}
	}
	foreach ( array( 'is_project_type', 'is_project_scope' ) as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			unregister_taxonomy( $taxonomy );
		}
	}
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'isp_deactivate' );
