<?php
/**
 * <head> extras that no SEO plugin provides: browser theme colour and the brand favicon.
 *
 * Titles, descriptions, social tags and structured data are handled by Yoast SEO or, without it,
 * by the Interior Specifics Core plugin (includes/seo.php) — nothing here duplicates them.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'ist_head_theme_color', 2 );
/**
 * Browser UI colour (address bar on mobile) — the charcoal brand colour.
 */
function ist_head_theme_color(): void {
	/**
	 * Filters the <meta name="theme-color"> value.
	 *
	 * @param string $color Hex colour.
	 */
	$color = sanitize_hex_color( (string) apply_filters( 'ist_theme_color', '#1a1a1a' ) );
	if ( $color ) {
		printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $color ) );
	}
}

add_action( 'wp_head', 'ist_head_favicon', 99 );
add_action( 'admin_head', 'ist_head_favicon' );
add_action( 'login_head', 'ist_head_favicon' );
/**
 * The SVG monogram favicon (app/icon.svg) plus a PNG apple-touch-icon, until a Site Icon is set
 * in Appearance → Customize → Site Identity (core then prints its own icon tags).
 */
function ist_head_favicon(): void {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml" sizes="any">' . "\n", esc_url( IST_URI . '/assets/icon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s" sizes="180x180">' . "\n", esc_url( IST_URI . '/assets/apple-touch-icon.png' ) );
}

add_action( 'do_faviconico', 'ist_favicon_ico', 5 );
/**
 * Requests for /favicon.ico (crawlers, feed readers) otherwise redirect to the WordPress logo
 * when no Site Icon exists; send them to the brand icon instead.
 */
function ist_favicon_ico(): void {
	if ( has_site_icon() ) {
		return;
	}
	wp_safe_redirect( IST_URI . '/assets/apple-touch-icon.png', 301 );
	exit;
}
