<?php
/**
 * Typed post meta for services, projects, testimonials and posts.
 * Exposed to REST so the block-editor sidebar (editor.php / src/editor) can edit it.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta definitions per post type: key (without isp_ prefix) => [type, item schema?].
 *
 * @return array<string, array<string, array<string, mixed>>>
 */
function isp_meta_schema(): array {
	$string = array( 'type' => 'string' );
	$bool   = array( 'type' => 'boolean' );
	$ints   = array(
		'type'  => 'array',
		'items' => array( 'type' => 'integer' ),
	);
	$texts  = array(
		'type'  => 'array',
		'items' => array( 'type' => 'string' ),
	);

	return array(
		'is_service'     => array(
			'investment_from'  => $string,
			'process'          => array(
				'type'  => 'array',
				'items' => array(
					'type'       => 'object',
					'properties' => array(
						'step'   => array( 'type' => 'string' ),
						'detail' => array( 'type' => 'string' ),
					),
				),
			),
			'faqs'             => array(
				'type'  => 'array',
				'items' => array(
					'type'       => 'object',
					'properties' => array(
						'q' => array( 'type' => 'string' ),
						'a' => array( 'type' => 'string' ),
					),
				),
			),
			'seo_keywords'     => $texts,
			'featured'         => $bool,
			'related_projects' => $ints,
		),
		'is_project'     => array(
			'location'          => $string,
			'budget'            => $string,
			'year'              => $string,
			'timeline'          => $string,
			'challenge'         => $string,
			'solution'          => $string,
			'results'           => $string,
			'gallery'           => $ints,
			'testimonial_quote' => $string,
			'testimonial_name'  => $string,
			'featured'          => $bool,
		),
		'is_testimonial' => array(
			'location'      => $string,
			'project_label' => $string,
		),
		'post'           => array(
			'read_time' => $string,
		),
	);
}

add_action( 'init', 'isp_register_meta' );
/**
 * Register every meta key with its REST schema and an edit-capability check.
 */
function isp_register_meta(): void {
	foreach ( isp_meta_schema() as $post_type => $fields ) {
		foreach ( $fields as $key => $schema ) {
			$is_array = 'array' === $schema['type'];
			register_post_meta(
				$post_type,
				'isp_' . $key,
				array(
					'type'              => $schema['type'],
					'single'            => true,
					'default'           => $is_array ? array() : ( 'boolean' === $schema['type'] ? false : '' ),
					'sanitize_callback' => $is_array ? 'isp_sanitize_meta_array' : null,
					'show_in_rest'      => $is_array ? array( 'schema' => $schema ) : true,
					'auth_callback'     => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
				)
			);
		}
	}
}

/**
 * Recursively sanitise array meta (strings trimmed and stripped, ints cast).
 *
 * @param mixed $value Raw value.
 * @return array<int|string, mixed>
 */
function isp_sanitize_meta_array( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}
	$clean = array();
	foreach ( $value as $k => $item ) {
		if ( is_array( $item ) ) {
			$clean[ $k ] = isp_sanitize_meta_array( $item );
		} elseif ( is_int( $item ) || ( is_string( $item ) && ctype_digit( $item ) && ! is_string( $k ) ) ) {
			$clean[ $k ] = (int) $item;
		} else {
			$clean[ $k ] = sanitize_textarea_field( (string) $item );
		}
	}
	return $clean;
}
