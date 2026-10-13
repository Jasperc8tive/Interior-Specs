<?php
/**
 * Custom blocks: every folder in build/blocks/ with a block.json is registered automatically.
 * Blocks are dynamic (render.php), so markup changes apply everywhere without block recovery.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ist_register_blocks' );
/**
 * Register built blocks.
 */
function ist_register_blocks(): void {
	foreach ( glob( IST_DIR . '/build/blocks/*/block.json' ) ?: array() as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}

add_filter( 'block_categories_all', 'ist_block_category' );
/**
 * Put the site's sections at the top of the inserter.
 *
 * @param array<int, array<string, mixed>> $categories Categories.
 * @return array<int, array<string, mixed>>
 */
function ist_block_category( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'interior-specifics',
			'title' => __( 'Interior Specifics', 'interior-specifics' ),
			'icon'  => null,
		)
	);
	return $categories;
}

/**
 * Whether the current post's content starts with a full-bleed hero block,
 * in which case templates skip their default title hero.
 *
 * @param int|null $post_id Post ID (defaults to the current post).
 */
function ist_content_has_hero( ?int $post_id = null ): bool {
	$content = (string) get_post_field( 'post_content', $post_id ?? get_the_ID() );
	return (bool) preg_match( '/^\s*<!-- wp:interior-specifics\/(page-hero|story-hero)[\s\/]/', $content );
}
