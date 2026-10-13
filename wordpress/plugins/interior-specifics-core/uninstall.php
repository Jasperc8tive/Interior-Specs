<?php
/**
 * Uninstall: remove the plugin's settings, transients and scheduled events only.
 *
 * Content is deliberately kept: leads, services, showroom projects, testimonials, their isp_*
 * meta and the project taxonomies stay in the database, so deleting the plugin by mistake (or
 * reinstalling it) never loses enquiries or portfolio work. See readme.txt → Uninstall.
 *
 * @package InteriorSpecificsCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete this plugin's options, transients and cron events on the current site.
 */
function isp_uninstall_site(): void {
	global $wpdb;

	// Settings (isp_settings) and any other isp_* option, plus isp_* transients and timeouts.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall clean-up.
	$options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( 'isp_' ) . '%',
			$wpdb->esc_like( '_transient_isp_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_isp_' ) . '%'
		)
	);
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Scheduled events registered under the isp_ prefix.
	foreach ( (array) _get_cron_array() as $events ) {
		foreach ( array_keys( (array) $events ) as $hook ) {
			if ( str_starts_with( (string) $hook, 'isp_' ) ) {
				wp_unschedule_hook( $hook );
			}
		}
	}
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $isp_site_id ) {
		switch_to_blog( $isp_site_id );
		isp_uninstall_site();
		restore_current_blog();
	}
} else {
	isp_uninstall_site();
}

// Persistent object caches keep transients outside the options table.
wp_cache_flush();
