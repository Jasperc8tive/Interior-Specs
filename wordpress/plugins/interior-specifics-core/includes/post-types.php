<?php
/**
 * Content model: services, showroom projects, testimonials and their taxonomies.
 * Leads are registered in leads.php.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'isp_register_post_types' );
/**
 * Register CPTs and taxonomies. URLs match the Next.js site: /services/{slug}, /showroom/{slug}.
 */
function isp_register_post_types(): void {
	// Taxonomies first: their /showroom/type/{term}/ rewrite rules must precede the
	// is_project rules, or /showroom/type/x/ is read as an attachment of a project.
	register_taxonomy(
		'is_project_type',
		array( 'is_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Project Types', 'interior-specifics-core' ),
				'singular_name' => __( 'Project Type', 'interior-specifics-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			// Filtering happens on /showroom/?type=slug; term archives stay reachable for crawlers.
			'query_var'         => false,
			'rewrite'           => array(
				'slug'       => 'showroom/type',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'is_project_scope',
		array( 'is_project' ),
		array(
			'labels'       => array(
				'name'          => __( 'Scope', 'interior-specifics-core' ),
				'singular_name' => __( 'Scope item', 'interior-specifics-core' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'hierarchical' => false,
			// Keep scope items in the order they were entered (term_order).
			'sort'         => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		)
	);

	register_post_type(
		'is_service',
		array(
			'labels'        => array(
				'name'               => __( 'Services', 'interior-specifics-core' ),
				'singular_name'      => __( 'Service', 'interior-specifics-core' ),
				'add_new_item'       => __( 'Add New Service', 'interior-specifics-core' ),
				'edit_item'          => __( 'Edit Service', 'interior-specifics-core' ),
				'all_items'          => __( 'All Services', 'interior-specifics-core' ),
				'search_items'       => __( 'Search Services', 'interior-specifics-core' ),
				'not_found'          => __( 'No services found.', 'interior-specifics-core' ),
				'featured_image'     => __( 'Service image', 'interior-specifics-core' ),
				'set_featured_image' => __( 'Set service image', 'interior-specifics-core' ),
			),
			'description'   => __( 'Interior design services offered by the studio.', 'interior-specifics-core' ),
			'public'        => true,
			'has_archive'   => 'services',
			'show_in_rest'  => true,
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-admin-customizer',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'is_project',
		array(
			'labels'        => array(
				'name'               => __( 'Showroom', 'interior-specifics-core' ),
				'singular_name'      => __( 'Project', 'interior-specifics-core' ),
				'menu_name'          => __( 'Showroom', 'interior-specifics-core' ),
				'add_new_item'       => __( 'Add New Project', 'interior-specifics-core' ),
				'edit_item'          => __( 'Edit Project', 'interior-specifics-core' ),
				'all_items'          => __( 'All Projects', 'interior-specifics-core' ),
				'search_items'       => __( 'Search Projects', 'interior-specifics-core' ),
				'not_found'          => __( 'No projects found.', 'interior-specifics-core' ),
				'featured_image'     => __( 'Hero image', 'interior-specifics-core' ),
				'set_featured_image' => __( 'Set hero image', 'interior-specifics-core' ),
			),
			'description'   => __( 'Portfolio case studies.', 'interior-specifics-core' ),
			'public'        => true,
			'has_archive'   => 'showroom',
			'show_in_rest'  => true,
			'menu_position' => 21,
			'menu_icon'     => 'dashicons-format-gallery',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
			// Case-study fields live in the sidebar; the content area holds optional closing notes.
			'template'      => array(
				array(
					'core/paragraph',
					array( 'placeholder' => __( 'Optional notes shown after the Results section', 'interior-specifics-core' ) ),
				),
			),
			'template_lock' => false,
			'rewrite'       => array(
				'slug'       => 'showroom',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'is_testimonial',
		array(
			'labels'              => array(
				'name'          => __( 'Testimonials', 'interior-specifics-core' ),
				'singular_name' => __( 'Testimonial', 'interior-specifics-core' ),
				'add_new_item'  => __( 'Add New Testimonial', 'interior-specifics-core' ),
				'edit_item'     => __( 'Edit Testimonial', 'interior-specifics-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'menu_position'       => 22,
			'menu_icon'           => 'dashicons-format-quote',
			'supports'            => array( 'title', 'editor', 'page-attributes', 'custom-fields' ),
			'rewrite'             => false,
		)
	);
}

add_filter( 'rest_pre_dispatch', 'isp_restrict_testimonial_rest', 10, 3 );
/**
 * Testimonials need REST for the block editor but are not public content: the theme renders
 * them server-side, so anonymous REST reads (collection and single items) are refused.
 *
 * @param mixed           $result  Response to short-circuit with, or null.
 * @param WP_REST_Server  $server  Server.
 * @param WP_REST_Request $request Request.
 * @return mixed
 */
function isp_restrict_testimonial_rest( $result, WP_REST_Server $server, WP_REST_Request $request ) {
	if ( null !== $result || current_user_can( 'edit_posts' ) || ! preg_match( '#^/wp/v2/is_testimonial(/|$)#i', $request->get_route() ) ) {
		return $result;
	}
	return new WP_Error(
		'rest_forbidden',
		__( 'Sorry, you are not allowed to view testimonials.', 'interior-specifics-core' ),
		array( 'status' => rest_authorization_required_code() )
	);
}

add_action( 'pre_get_posts', 'isp_archive_queries' );
/**
 * Archives list every item in editor-controlled order (menu_order), like the Next.js data arrays.
 *
 * @param WP_Query $query Query.
 */
function isp_archive_queries( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( array( 'is_service', 'is_project' ) ) || $query->is_tax( 'is_project_type' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			)
		);
	}
}

/**
 * Project types in display order, as [slug => name].
 *
 * @return array<string, string>
 */
function isp_project_types(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'is_project_type',
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	return wp_list_pluck( $terms, 'name', 'slug' );
}

/**
 * The single project type of a project.
 *
 * @param int $post_id Project ID.
 */
function isp_project_type( int $post_id ): ?WP_Term {
	$terms = get_the_terms( $post_id, 'is_project_type' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Previous and next project by menu_order, wrapping around (as the Next.js case studies do).
 *
 * @param int $post_id Current project.
 * @return array{prev: ?WP_Post, next: ?WP_Post}
 */
function isp_adjacent_projects( int $post_id ): array {
	$ids   = get_posts(
		array(
			'post_type'      => 'is_project',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);
	$index = array_search( $post_id, $ids, true );
	if ( false === $index || count( $ids ) < 2 ) {
		return array(
			'prev' => null,
			'next' => null,
		);
	}
	$count = count( $ids );
	return array(
		'prev' => get_post( $ids[ ( $index - 1 + $count ) % $count ] ),
		'next' => get_post( $ids[ ( $index + 1 ) % $count ] ),
	);
}
