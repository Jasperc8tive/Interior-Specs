<?php
/**
 * Block-editor sidebar for the custom fields registered in meta.php (services, projects,
 * testimonials, posts). The UI lives in src/editor.js and is built to build/editor.js.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post types that get a custom-fields sidebar panel.
 *
 * @return string[]
 */
function isp_editor_post_types(): array {
	return array( 'is_service', 'is_project', 'is_testimonial', 'post' );
}

add_action( 'enqueue_block_editor_assets', 'isp_enqueue_editor_assets' );
/**
 * Load the sidebar script (with its generated dependency list) on supported post editors only.
 */
function isp_enqueue_editor_assets(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, isp_editor_post_types(), true ) ) {
		return;
	}

	$asset_file = ISP_DIR . 'build/editor.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}
	$asset = require $asset_file;

	wp_enqueue_script(
		'isp-editor',
		ISP_URL . 'build/editor.js',
		$asset['dependencies'] ?? array(),
		$asset['version'] ?? ISP_VERSION,
		true
	);
	wp_set_script_translations( 'isp-editor', 'interior-specifics-core', ISP_DIR . 'languages' );

	if ( file_exists( ISP_DIR . 'build/editor.css' ) ) {
		wp_enqueue_style( 'isp-editor', ISP_URL . 'build/editor.css', array( 'wp-components' ), $asset['version'] ?? ISP_VERSION );
		wp_style_add_data( 'isp-editor', 'rtl', 'replace' );
	}

	$data = array( 'autoReadTime' => '' );
	$post = get_post();
	if ( $post && 'post' === $post->post_type ) {
		$data['autoReadTime'] = isp_editor_auto_read_time( $post->ID );
	}
	wp_add_inline_script( 'isp-editor', 'window.ispEditor = ' . wp_json_encode( $data ) . ';', 'before' );
}

/**
 * The reading time isp_read_time() would compute if no override were set.
 *
 * @param int $post_id Post ID.
 */
function isp_editor_auto_read_time( int $post_id ): string {
	$ignore_override = static function ( $value, $object_id, $meta_key ) use ( $post_id ) {
		return ( (int) $object_id === $post_id && 'isp_read_time' === $meta_key ) ? array( '' ) : $value;
	};
	add_filter( 'get_post_metadata', $ignore_override, 10, 3 );
	$value = isp_read_time( $post_id );
	remove_filter( 'get_post_metadata', $ignore_override, 10 );
	return $value;
}

add_filter( 'enter_title_here', 'isp_editor_title_placeholder', 10, 2 );
/**
 * Testimonials are titled by the client's name.
 *
 * @param string  $placeholder Default placeholder.
 * @param WP_Post $post        Post being edited.
 */
function isp_editor_title_placeholder( string $placeholder, $post ): string {
	if ( $post instanceof WP_Post && 'is_testimonial' === $post->post_type ) {
		return __( 'Client name', 'interior-specifics-core' );
	}
	return $placeholder;
}
