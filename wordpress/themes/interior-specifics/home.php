<?php
/**
 * The Journal — the posts page at /blog/ (port of app/blog/page.tsx).
 *
 * The hero copy defaults to the Next.js page. Editors can override it from the posts page
 * itself (Settings → Reading → Posts page): its excerpt becomes the intro and its featured
 * image the hero image; without one, the Journal hero from Settings → Interior Specifics is
 * used. Developers can filter everything with `ist_journal_hero_args`.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ist_posts_page = (int) get_option( 'page_for_posts' );
$ist_hero       = array(
	'eyebrow'   => __( 'The Journal', 'interior-specifics' ),
	'title'     => __( 'Design Insights & Luxury Living', 'interior-specifics' ),
	'intro'     => __( 'Expert advice for creating exceptional spaces.', 'interior-specifics' ),
	'image_id'  => ist_page_hero_image_id( 'journal' ),
	'image_url' => '',
	'image_alt' => __( 'Elegant interior reading nook', 'interior-specifics' ),
);

if ( $ist_posts_page ) {
	$ist_page_excerpt = (string) get_post_field( 'post_excerpt', $ist_posts_page );
	if ( '' !== trim( $ist_page_excerpt ) ) {
		$ist_hero['intro'] = wp_strip_all_tags( $ist_page_excerpt );
	}
	$ist_page_thumb = (int) get_post_thumbnail_id( $ist_posts_page );
	if ( $ist_page_thumb ) {
		$ist_alt               = (string) get_post_meta( $ist_page_thumb, '_wp_attachment_image_alt', true );
		$ist_hero['image_id']  = $ist_page_thumb;
		$ist_hero['image_alt'] = '' !== $ist_alt ? $ist_alt : $ist_hero['image_alt'];
	}
}

/**
 * Filters the Journal (posts page) hero arguments. See template-parts/page-hero.php.
 *
 * @param array<string, mixed> $ist_hero Hero args.
 */
ist_page_hero( (array) apply_filters( 'ist_journal_hero_args', $ist_hero ) );

get_template_part( 'template-parts/journal-listing' );

ist_cta_section();

get_footer();
