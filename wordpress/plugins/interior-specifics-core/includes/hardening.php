<?php
/**
 * Site-wide hardening: comments and pings off, XML-RPC off, no user enumeration.
 * Lives in the plugin so it holds whichever way the site was deployed (seeded or imported).
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------------------------
 * Comments: the theme has no comments template, so accepting them only builds a hidden queue.
 * -------------------------------------------------------------------------------------------
 */

add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
add_filter( 'pre_option_default_comment_status', 'isp_closed_status' );
add_filter( 'pre_option_default_ping_status', 'isp_closed_status' );

/**
 * Value for the default comment/ping status options.
 */
function isp_closed_status(): string {
	return 'closed';
}

add_action( 'init', 'isp_remove_comment_support', 100 );
/**
 * Remove comment and trackback support from every post type (hides the Discussion panels).
 */
function isp_remove_comment_support(): void {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
		}
		if ( post_type_supports( $post_type, 'trackbacks' ) ) {
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

add_action( 'admin_menu', 'isp_remove_comment_menus' );
/**
 * Hide the Comments screen and the Discussion settings page.
 */
function isp_remove_comment_menus(): void {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}

add_action( 'admin_init', 'isp_block_comment_screens' );
/**
 * Send direct visits to the hidden comment screens back to the dashboard.
 */
function isp_block_comment_screens(): void {
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}

add_action( 'admin_bar_menu', 'isp_remove_comment_admin_bar', 100 );
/**
 * Remove the comments bubble from the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar Admin bar.
 */
function isp_remove_comment_admin_bar( WP_Admin_Bar $admin_bar ): void {
	$admin_bar->remove_node( 'comments' );
}

add_action( 'wp_dashboard_setup', 'isp_remove_comment_dashboard_widget' );
/**
 * Remove the Recent Comments dashboard widget.
 */
function isp_remove_comment_dashboard_widget(): void {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

add_filter( 'feed_links_show_comments_feed', '__return_false' );

add_action( 'template_redirect', 'isp_no_comment_feeds', 1 );
/**
 * Comment feeds would be empty; answer them with a 404.
 */
function isp_no_comment_feeds(): void {
	if ( is_comment_feed() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		// Otherwise the canonical redirect "guesses" a URL for the 404 (…/feed/feed/).
		remove_action( 'template_redirect', 'redirect_canonical' );
	}
}

/**
 * Close comments and pings on existing content (run on activation and by the seeder).
 */
function isp_close_existing_comments(): void {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off bulk update.
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE comment_status <> 'closed' OR ping_status <> 'closed'" );
	if ( $ids ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off bulk update.
		$wpdb->query( "UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed' WHERE comment_status <> 'closed' OR ping_status <> 'closed'" );
		array_map( 'clean_post_cache', array_map( 'intval', $ids ) );
	}
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'default_pingback_flag', '0' );
}

/*
 * -------------------------------------------------------------------------------------------
 * XML-RPC: no client uses it, and system.multicall allows bulk password guessing.
 * -------------------------------------------------------------------------------------------
 */

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', 'isp_remove_pingback_header' );
remove_action( 'wp_head', 'rsd_link' );

add_action( 'init', 'isp_refuse_xmlrpc', 0 );
/**
 * Refuse xmlrpc.php outright (the system.* methods survive an empty method list).
 */
function isp_refuse_xmlrpc(): void {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		// wp_die() prints nothing here: its XML-RPC handler needs a server object that does not exist yet.
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
		echo esc_html__( 'XML-RPC is disabled on this site.', 'interior-specifics-core' );
		exit;
	}
}

/**
 * Drop the X-Pingback header.
 *
 * @param array<string, string> $headers Response headers.
 * @return array<string, string>
 */
function isp_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
}

/*
 * -------------------------------------------------------------------------------------------
 * User enumeration: the studio, not a person, authors the site.
 * -------------------------------------------------------------------------------------------
 */

add_filter( 'rest_endpoints', 'isp_restrict_user_endpoints' );
/**
 * Hide the users REST routes from anyone who cannot edit content (the editor still needs them).
 *
 * @param array<string, mixed> $endpoints Registered routes.
 * @return array<string, mixed>
 */
function isp_restrict_user_endpoints( array $endpoints ): array {
	if ( current_user_can( 'edit_posts' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}

add_action( 'template_redirect', 'isp_redirect_author_archives', 1 );
/**
 * Author archives (/?author=1, /blog/author/{login}/) expose login names; send them home.
 */
function isp_redirect_author_archives(): void {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}

add_filter( 'author_link', 'isp_author_link' );
/**
 * Point author links (oEmbed, feeds, schema) at the home page instead of the archive.
 */
function isp_author_link(): string {
	return home_url( '/' );
}

add_filter( 'the_author', 'isp_feed_author' );
/**
 * Credit the studio in feeds (<dc:creator>) instead of the account's display name.
 *
 * @param string|null $author Display name.
 */
function isp_feed_author( $author ): string {
	return is_feed() ? get_bloginfo( 'name' ) : (string) $author;
}

add_filter( 'oembed_response_data', 'isp_oembed_author' );
/**
 * Credit the studio, not the user account, in oEmbed responses.
 *
 * @param array<string, mixed> $data oEmbed data.
 * @return array<string, mixed>
 */
function isp_oembed_author( array $data ): array {
	$data['author_name'] = get_bloginfo( 'name' );
	$data['author_url']  = home_url( '/' );
	return $data;
}
