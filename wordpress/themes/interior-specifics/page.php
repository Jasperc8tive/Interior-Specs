<?php
/**
 * Pages. Designed pages are built from Interior Specifics blocks (hero first) and render
 * edge-to-edge. Plain pages without a hero get a title hero (the featured image, else the
 * default hero from Settings → Interior Specifics) and a readable prose column.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	if ( ! ist_content_has_hero() ) {
		ist_page_hero(
			array(
				'title'     => get_the_title(),
				'intro'     => has_excerpt() ? get_the_excerpt() : '',
				'image_id'  => has_post_thumbnail() ? (int) get_post_thumbnail_id() : ist_page_hero_image_id(),
				'image_url' => '',
				'image_alt' => '',
			)
		);
		echo '<div class="is-prose">';
		the_content();
		echo '</div>';
	} else {
		the_content();
	}
endwhile;

get_footer();
