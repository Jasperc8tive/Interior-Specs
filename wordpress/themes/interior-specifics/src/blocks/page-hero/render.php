<?php
/**
 * Page Hero block — renders template-parts/page-hero.php.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'eyebrow'     => (string) $attributes['eyebrow'],
		'title'       => (string) $attributes['title'],
		'accent_from' => (int) $attributes['accentFrom'],
		'accent_to'   => (int) $attributes['accentTo'],
		'intro'       => (string) $attributes['intro'],
		'image_id'    => (int) $attributes['imageId'],
		'image_url'   => (string) $attributes['imageUrl'],
		'image_alt'   => (string) $attributes['imageAlt'],
		'wrapper'     => get_block_wrapper_attributes( array( 'class' => 'grain relative -mt-[88px] flex min-h-[68vh] items-center overflow-hidden bg-onyx' ) ),
	)
);
