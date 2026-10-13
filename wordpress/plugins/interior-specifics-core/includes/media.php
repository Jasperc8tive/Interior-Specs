<?php
/**
 * Media: generate WebP sub-sizes for JPEG and PNG uploads (Next.js served WebP through next/image).
 *
 * WordPress converts only the generated sub-sizes; the original upload (and its `-scaled` copy)
 * keeps its format. Existing images need `wp media regenerate --yes` once after activation.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'image_editor_output_format', 'isp_webp_output_format' );
/**
 * Map JPEG and PNG sub-sizes to WebP when the server's image editor can write it.
 *
 * @param array<string, string> $formats Source MIME type => output MIME type.
 * @return array<string, string>
 */
function isp_webp_output_format( array $formats ): array {
	/**
	 * Whether Interior Specifics Core converts JPEG and PNG sub-sizes to WebP.
	 *
	 * @param bool $enabled Default true.
	 */
	if ( ! apply_filters( 'isp_webp_sub_sizes', true ) || ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formats;
	}
	return array_merge(
		$formats,
		array(
			'image/jpeg' => 'image/webp',
			'image/png'  => 'image/webp',
		)
	);
}
