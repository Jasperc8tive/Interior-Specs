<?php
/**
 * Closing CTA block — renders template-parts/cta-section.php (port of components/CTASection.tsx).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

get_template_part(
	'template-parts/cta-section',
	null,
	array(
		'eyebrow'         => (string) ( $attributes['eyebrow'] ?? '' ),
		'title'           => (string) ( $attributes['title'] ?? '' ),
		'intro'           => (string) ( $attributes['intro'] ?? '' ),
		'primary_label'   => (string) ( $attributes['primaryLabel'] ?? '' ),
		'primary_url'     => (string) ( $attributes['primaryUrl'] ?? '' ),
		'secondary_label' => (string) ( $attributes['secondaryLabel'] ?? '' ),
		'accent_from'     => (int) ( $attributes['accentFrom'] ?? -1 ),
		'accent_to'       => (int) ( $attributes['accentTo'] ?? -1 ),
		'wrapper'         => get_block_wrapper_attributes( array( 'class' => 'grain relative overflow-hidden bg-onyx py-24 md:py-32' ) ),
	)
);
