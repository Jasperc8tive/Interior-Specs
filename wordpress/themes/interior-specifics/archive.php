<?php
/**
 * Journal archives — category, tag, author and date listings in the same design as the
 * posts page (home.php). Archives of other content (custom post types or taxonomies without
 * their own template) fall back to index.php.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( is_category() || is_tag() || is_author() || is_date() || is_tax( 'post_format' ) ) ) {
	require get_index_template();
	return;
}

get_header();

add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
$ist_title = wp_strip_all_tags( get_the_archive_title() );
remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

if ( is_author() ) {
	/* translators: %s: author display name. */
	$ist_title = sprintf( __( 'Articles by %s', 'interior-specifics' ), get_the_author_meta( 'display_name', (int) get_queried_object_id() ) );
}

$ist_intro = trim( wp_strip_all_tags( (string) get_the_archive_description() ) );

$ist_hero = array(
	'eyebrow'   => __( 'The Journal', 'interior-specifics' ),
	'title'     => $ist_title,
	'intro'     => '' !== $ist_intro ? $ist_intro : __( 'Expert advice for creating exceptional spaces.', 'interior-specifics' ),
	'image_id'  => ist_page_hero_image_id( 'journal' ),
	'image_url' => '',
	'image_alt' => __( 'Elegant interior reading nook', 'interior-specifics' ),
);

/**
 * Filters the Journal archive hero arguments. See template-parts/page-hero.php.
 *
 * @param array<string, mixed> $ist_hero Hero args.
 */
ist_page_hero( (array) apply_filters( 'ist_journal_archive_hero_args', $ist_hero ) );

get_template_part(
	'template-parts/journal-listing',
	null,
	array(
		'empty'     => __( 'There are no articles in this archive yet.', 'interior-specifics' ),
		'back_link' => true,
	)
);

ist_cta_section();

get_footer();
