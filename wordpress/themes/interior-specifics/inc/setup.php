<?php
/**
 * Theme supports, menus, image sizes and the companion-plugin check.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'ist_setup' );
/**
 * Register theme features.
 */
function ist_setup(): void {
	load_theme_textdomain( 'interior-specifics', IST_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 96,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	// Block patterns from core's directory don't fit this design system.
	remove_theme_support( 'core-block-patterns' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary (header)', 'interior-specifics' ),
			'footer'  => __( 'Footer — Explore', 'interior-specifics' ),
			'legal'   => __( 'Footer — Legal', 'interior-specifics' ),
		)
	);

	// Card and hero crops. Sizes are generous for retina; srcset handles the rest. The theme's
	// own cards use the uncropped 'large' size so their 16:10 / 16:11 boxes crop the original
	// once (as next/image does); ist-card stays available to editors in the block editor.
	add_image_size( 'ist-card', 900, 675, true );       // 4:3 cards.
	add_image_size( 'ist-portrait', 900, 1200, true );  // 3:4 portraits.
	add_image_size( 'ist-hero', 2000, 1333, false );    // Full-bleed heroes.

	$GLOBALS['content_width'] = 768;
}

add_filter( 'image_size_names_choose', 'ist_image_size_names' );
/**
 * Expose the custom sizes in the editor.
 *
 * @param array<string, string> $sizes Sizes.
 * @return array<string, string>
 */
function ist_image_size_names( array $sizes ): array {
	return $sizes + array(
		'ist-card'     => __( 'Card (4:3)', 'interior-specifics' ),
		'ist-portrait' => __( 'Portrait (3:4)', 'interior-specifics' ),
		'ist-hero'     => __( 'Hero', 'interior-specifics' ),
	);
}

add_action( 'admin_notices', 'ist_require_core_plugin_notice' );
/**
 * The theme renders without the plugin, but content and forms need it.
 */
function ist_require_core_plugin_notice(): void {
	if ( function_exists( 'isp_setting' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'The Interior Specifics theme needs the Interior Specifics Core plugin for services, projects, forms and site settings. Please install and activate it.', 'interior-specifics' )
	);
}

add_filter( 'excerpt_length', static fn() => 28 );
add_filter( 'excerpt_more', static fn() => '…' );
