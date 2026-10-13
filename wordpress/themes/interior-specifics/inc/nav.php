<?php
/**
 * Menus as data. The header and footer render their own markup from a nested tree, which keeps
 * the Next.js structure (desktop hover dropdowns + mobile accordion) without a custom Walker.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nested menu items for a theme location.
 *
 * Items: ['id', 'label', 'url', 'current', 'children' => [...]]. A "Services" archive item with no
 * children of its own is auto-filled with every published service, so the dropdown never drifts.
 *
 * @param string $location Theme location.
 * @return array<int, array<string, mixed>>
 */
function ist_menu_tree( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return ist_menu_fallback( $location );
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return array();
	}
	_wp_menu_item_classes_by_context( $items );

	$by_id = array();
	foreach ( $items as $item ) {
		$by_id[ $item->ID ] = array(
			'id'       => (int) $item->ID,
			'parent'   => (int) $item->menu_item_parent,
			'label'    => $item->title,
			'url'      => $item->url,
			'current'  => in_array( 'current-menu-item', (array) $item->classes, true ),
			'object'   => $item->object,
			'type'     => $item->type,
			'children' => array(),
		);
	}

	$tree = array();
	foreach ( $by_id as $id => &$node ) {
		if ( $node['parent'] && isset( $by_id[ $node['parent'] ] ) ) {
			$by_id[ $node['parent'] ]['children'][] = &$node;
		} else {
			$tree[] = &$node;
		}
	}
	unset( $node );

	foreach ( $tree as &$top ) {
		if ( 'primary' === $location && 'post_type_archive' === $top['type'] && 'is_service' === $top['object'] && ! $top['children'] ) {
			$top['children'] = ist_service_menu_children();
		}
	}
	unset( $top );

	return $tree;
}

/**
 * Every published service as dropdown children.
 *
 * @return array<int, array<string, mixed>>
 */
function ist_service_menu_children(): array {
	if ( ! post_type_exists( 'is_service' ) ) {
		return array();
	}
	$services = get_posts(
		array(
			'post_type'      => 'is_service',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
	return array_map(
		static fn( WP_Post $s ) => array(
			'id'       => $s->ID,
			'label'    => get_the_title( $s ),
			'url'      => get_permalink( $s ),
			'current'  => is_single( $s->ID ),
			'children' => array(),
		),
		$services
	);
}

/**
 * Reasonable menus before any are assigned (fresh install without the seeder).
 *
 * @param string $location Theme location.
 * @return array<int, array<string, mixed>>
 */
function ist_menu_fallback( string $location ): array {
	if ( 'legal' === $location ) {
		$items   = array();
		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy && 'publish' === get_post_status( $privacy ) ) {
			$items[] = array(
				'label'    => get_the_title( $privacy ),
				'url'      => get_permalink( $privacy ),
				'children' => array(),
			);
		}
		return $items;
	}
	$items = array(
		array(
			'label'    => __( 'Home', 'interior-specifics' ),
			'url'      => home_url( '/' ),
			'children' => array(),
		),
	);
	if ( post_type_exists( 'is_project' ) ) {
		$items[] = array(
			'label'    => __( 'Showroom', 'interior-specifics' ),
			'url'      => get_post_type_archive_link( 'is_project' ),
			'children' => array(),
		);
	}
	if ( post_type_exists( 'is_service' ) ) {
		$items[] = array(
			'label'    => __( 'Services', 'interior-specifics' ),
			'url'      => get_post_type_archive_link( 'is_service' ),
			'children' => 'primary' === $location ? ist_service_menu_children() : array(),
		);
	}
	return $items;
}
