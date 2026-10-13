<?php
/**
 * Template-part wrappers so templates and blocks render shared sections identically.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full-bleed page hero (template-parts/page-hero.php).
 *
 * @param array<string, mixed> $args eyebrow, title, accent_from, accent_to, intro, image_id, image_url, image_alt, tag.
 */
function ist_page_hero( array $args ): void {
	get_template_part( 'template-parts/page-hero', null, $args );
}

/**
 * Closing call-to-action band (template-parts/cta-section.php).
 *
 * @param array<string, mixed> $args eyebrow, title, intro, primary_label, primary_url, accent_from, accent_to.
 */
function ist_cta_section( array $args = array() ): void {
	get_template_part( 'template-parts/cta-section', null, $args );
}

/**
 * Capture a template part as a string (for block render callbacks).
 *
 * @param string               $slug Template part slug, e.g. template-parts/page-hero.
 * @param array<string, mixed> $args Args.
 */
function ist_get_template_part( string $slug, array $args = array() ): string {
	ob_start();
	get_template_part( $slug, null, $args );
	return (string) ob_get_clean();
}
