<?php
/**
 * Front page: the homepage is composed entirely of blocks on the page set as "Homepage".
 * Falls back to the blog index when the site shows latest posts.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

if ( 'posts' === get_option( 'show_on_front' ) ) {
	require get_home_template();
	return;
}

get_header();

// Thin gold reading-progress bar along the top of the homepage (scroll-progress.js).
// The start state is an inline transform, not a scale-x-0 utility: Tailwind v4 maps that to the
// separate `scale` property, which would multiply the script's scaleX() back to zero.
echo '<div class="pointer-events-none fixed inset-x-0 top-0 z-50 h-[3px] origin-left bg-gold" style="transform:scaleX(0)" data-scroll-progress aria-hidden="true"></div>';

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
