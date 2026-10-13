<?php
/**
 * Leads inbox: list-table columns, filters, status views, row and bulk actions, a menu
 * count of new leads, a read-only single-lead screen with a status control, CSV export and
 * a "Recent leads" dashboard widget.
 *
 * Leads are private `is_lead` posts using page capabilities (see leads.php), so everything
 * here is gated on `edit_pages` / `edit_post`.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * Shared helpers
 * -------------------------------------------------------------------------
 */

/**
 * Lead workflow statuses, in pipeline order.
 *
 * @return array<string, string> status => label
 */
function isp_lead_statuses(): array {
	return array(
		'new'       => __( 'New', 'interior-specifics-core' ),
		'contacted' => __( 'Contacted', 'interior-specifics-core' ),
		'closed'    => __( 'Closed', 'interior-specifics-core' ),
	);
}

/**
 * Current status of a lead, defaulting to "new" for unknown values.
 *
 * @param int $lead_id Lead ID.
 */
function isp_lead_status( int $lead_id ): string {
	$status = (string) get_post_meta( $lead_id, 'isp_status', true );
	return array_key_exists( $status, isp_lead_statuses() ) ? $status : 'new';
}

/**
 * Stored field values of a lead.
 *
 * @param int $lead_id Lead ID.
 * @return array<string, string>
 */
function isp_lead_fields( int $lead_id ): array {
	$fields = get_post_meta( $lead_id, 'isp_fields', true );
	return is_array( $fields ) ? array_map( 'strval', $fields ) : array();
}

/**
 * Human label of a lead's form type ("Contact Inquiry"), falling back to the raw type.
 *
 * @param string $type Form type key.
 */
function isp_lead_type_label( string $type ): string {
	$form = function_exists( 'isp_form' ) ? isp_form( $type ) : null;
	return $form ? (string) $form['label'] : $type;
}

/**
 * Every field key known across all form schemas, in first-seen order, with its label.
 *
 * @return array<string, string> key => label
 */
function isp_lead_known_fields(): array {
	$known = array();
	foreach ( isp_forms() as $form ) {
		foreach ( $form['fields'] as $key => $field ) {
			if ( ! isset( $known[ $key ] ) ) {
				$known[ $key ] = (string) ( $field['label'] ?? $key );
			}
		}
	}
	return $known;
}

/**
 * Status badge markup.
 *
 * @param string $status Status key.
 */
function isp_lead_status_badge( string $status ): string {
	$labels = isp_lead_statuses();
	$status = isset( $labels[ $status ] ) ? $status : 'new';
	return sprintf(
		'<span class="isp-lead-badge isp-lead-badge--%1$s">%2$s</span>',
		esc_attr( $status ),
		esc_html( $labels[ $status ] )
	);
}

/**
 * Display name for a lead: the visitor's name, else their email, else a placeholder.
 *
 * @param int $lead_id Lead ID.
 */
function isp_lead_display_name( int $lead_id ): string {
	$fields = isp_lead_fields( $lead_id );
	if ( ! empty( $fields['name'] ) ) {
		return $fields['name'];
	}
	if ( ! empty( $fields['email'] ) ) {
		return $fields['email'];
	}
	return __( '(no name)', 'interior-specifics-core' );
}

/**
 * The list filters currently requested (form type and status), validated.
 *
 * @return array{type: string, status: string}
 */
function isp_lead_request_filters(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
	$type   = isset( $_GET['lead_type'] ) ? sanitize_key( wp_unslash( $_GET['lead_type'] ) ) : '';
	$status = isset( $_GET['lead_status'] ) ? sanitize_key( wp_unslash( $_GET['lead_status'] ) ) : '';
	// phpcs:enable
	return array(
		'type'   => ( $type && isp_form( $type ) ) ? $type : '',
		'status' => array_key_exists( $status, isp_lead_statuses() ) ? $status : '',
	);
}

/**
 * Meta query clauses for the given filters.
 *
 * @param array{type: string, status: string} $filters Filters.
 * @return array<int|string, mixed>
 */
function isp_lead_meta_query( array $filters ): array {
	$clauses = array();
	if ( $filters['type'] ) {
		$clauses[] = array(
			'key'   => 'isp_form_type',
			'value' => $filters['type'],
		);
	}
	if ( $filters['status'] ) {
		$clauses[] = array(
			'key'   => 'isp_status',
			'value' => $filters['status'],
		);
	}
	if ( count( $clauses ) > 1 ) {
		$clauses['relation'] = 'AND';
	}
	return $clauses;
}

/**
 * Number of leads with the given workflow status (excluding trash).
 *
 * @param string $status Status key.
 */
function isp_lead_count( string $status ): int {
	static $cache = array();
	if ( ! isset( $cache[ $status ] ) ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'is_lead',
				'post_status'            => array( 'private', 'publish', 'draft', 'pending' ),
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small private table, admin only.
				'meta_query'             => array(
					array(
						'key'   => 'isp_status',
						'value' => $status,
					),
				),
			)
		);
		$cache[ $status ] = (int) $query->found_posts;
	}
	return $cache[ $status ];
}

/**
 * True when the current admin screen is the leads list.
 */
function isp_is_leads_list_screen(): bool {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return $screen && 'edit-is_lead' === $screen->id;
}

/*
 * -------------------------------------------------------------------------
 * Styles
 * -------------------------------------------------------------------------
 */

add_action( 'admin_enqueue_scripts', 'isp_leads_admin_styles' );
/**
 * Small inline stylesheet for badges, the lead detail table and the dashboard widget.
 */
function isp_leads_admin_styles(): void {
	$screen = get_current_screen();
	if ( ! $screen || ! ( 'is_lead' === $screen->post_type || 'dashboard' === $screen->id ) ) {
		return;
	}
	wp_register_style( 'isp-admin-leads', false, array(), ISP_VERSION );
	wp_enqueue_style( 'isp-admin-leads' );
	wp_add_inline_style(
		'isp-admin-leads',
		'.isp-lead-badge{display:inline-block;padding:2px 9px;border-radius:999px;font-size:12px;line-height:1.6;font-weight:600;white-space:nowrap}'
		. '.isp-lead-badge--new{background:#fcf0e1;color:#8a5a14;box-shadow:inset 0 0 0 1px #e6c48f}'
		. '.isp-lead-badge--contacted{background:#e5f1fa;color:#135e96;box-shadow:inset 0 0 0 1px #9ec2e6}'
		. '.isp-lead-badge--closed{background:#edf7ed;color:#1e6b2f;box-shadow:inset 0 0 0 1px #a7d1ac}'
		. '.post-type-is_lead .column-isp_type{width:15%}.post-type-is_lead .column-isp_status{width:9%}.post-type-is_lead .column-isp_received{width:14%}'
		. '.post-type-is_lead .column-isp_phone{width:13%}'
		// Core only collapses columns after the primary one on small screens and Type sits before
		// Name, so on phones the Type column is hidden and repeated under the name instead.
		. '.isp-lead-type-sm{display:none}'
		. '@media screen and (max-width:782px){.post-type-is_lead .wp-list-table .column-isp_type,.post-type-is_lead .wp-list-table tr.is-expanded td.column-isp_type{display:none!important}.isp-lead-type-sm{display:block;color:#646970;font-size:13px}}'
		. '.isp-lead-export{display:inline-block;margin:0 0 4px 0}'
		. '.isp-lead-heading{margin:0 0 12px;font-size:20px;font-weight:400;line-height:1.4}'
		. '.isp-lead-table{width:100%;border-collapse:collapse}'
		. '.isp-lead-table th,.isp-lead-table td{padding:10px 12px;text-align:left;vertical-align:top;border-bottom:1px solid #f0f0f1}'
		. '.isp-lead-table th{width:30%;font-weight:600;color:#1d2327}'
		. '.isp-lead-table td{word-break:break-word}'
		. '.isp-lead-table tr:last-child th,.isp-lead-table tr:last-child td{border-bottom:0}'
		. '.isp-lead-section{margin:20px 0 6px;padding:0 12px;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#646970}'
		. '.isp-lead-empty{color:#646970;font-style:italic}'
		. '.isp-lead-status-box label{display:block;margin-bottom:6px;font-weight:600}'
		. '.isp-lead-status-box select{width:100%;max-width:none}'
		. '.isp-lead-status-actions{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #f0f0f1}'
		. '.isp-lead-status-actions .submitdelete{color:#b32d2e}'
		. '.isp-recent-leads{margin:0}'
		. '.isp-recent-leads li{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0;padding:9px 0;border-bottom:1px solid #f0f0f1}'
		. '.isp-recent-leads li:last-child{border-bottom:0}'
		. '.isp-recent-leads__meta{display:block;color:#646970;font-size:12px}'
		. '.isp-recent-leads__footer{margin:10px 0 0;padding-top:10px;border-top:1px solid #f0f0f1}'
	);
}

/*
 * -------------------------------------------------------------------------
 * List table
 * -------------------------------------------------------------------------
 */

add_filter( 'manage_is_lead_posts_columns', 'isp_lead_columns' );
/**
 * Columns: Type, Name, Email, Phone, Status, Received.
 *
 * @param array<string, string> $columns Default columns.
 * @return array<string, string>
 */
function isp_lead_columns( array $columns ): array {
	return array(
		'cb'           => $columns['cb'] ?? '<input type="checkbox" />',
		'isp_type'     => __( 'Type', 'interior-specifics-core' ),
		'isp_name'     => __( 'Name', 'interior-specifics-core' ),
		'isp_email'    => __( 'Email', 'interior-specifics-core' ),
		'isp_phone'    => __( 'Phone', 'interior-specifics-core' ),
		'isp_status'   => __( 'Status', 'interior-specifics-core' ),
		'isp_received' => __( 'Received', 'interior-specifics-core' ),
	);
}

add_filter( 'list_table_primary_column', 'isp_lead_primary_column', 10, 2 );
/**
 * Row actions and the mobile toggle live on the Name column.
 *
 * @param string $column    Default primary column.
 * @param string $screen_id Screen ID.
 */
function isp_lead_primary_column( string $column, string $screen_id ): string {
	return 'edit-is_lead' === $screen_id ? 'isp_name' : $column;
}

add_action( 'manage_is_lead_posts_custom_column', 'isp_lead_column_content', 10, 2 );
/**
 * Render a custom column cell.
 *
 * @param string $column  Column key.
 * @param int    $post_id Lead ID.
 */
function isp_lead_column_content( string $column, int $post_id ): void {
	$fields = isp_lead_fields( $post_id );

	switch ( $column ) {
		case 'isp_type':
			echo esc_html( isp_lead_type_label( (string) get_post_meta( $post_id, 'isp_form_type', true ) ) );
			break;

		case 'isp_name':
			$name = isp_lead_display_name( $post_id );
			if ( current_user_can( 'edit_post', $post_id ) ) {
				printf(
					'<strong><a class="row-title" href="%1$s" aria-label="%2$s">%3$s</a></strong>',
					esc_url( (string) get_edit_post_link( $post_id ) ),
					/* translators: %s: lead name. */
					esc_attr( sprintf( __( 'View lead from %s', 'interior-specifics-core' ), $name ) ),
					esc_html( $name )
				);
			} else {
				printf( '<strong>%s</strong>', esc_html( $name ) );
			}
			if ( 'trash' === get_post_status( $post_id ) ) {
				echo ' &mdash; <span class="post-state">' . esc_html__( 'Trash', 'interior-specifics-core' ) . '</span>';
			}
			echo '<span class="isp-lead-type-sm">' . esc_html( isp_lead_type_label( (string) get_post_meta( $post_id, 'isp_form_type', true ) ) ) . '</span>';
			break;

		case 'isp_email':
			if ( ! empty( $fields['email'] ) && is_email( $fields['email'] ) ) {
				printf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $fields['email'] ), esc_html( $fields['email'] ) );
			} else {
				echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . esc_html__( 'None', 'interior-specifics-core' ) . '</span>';
			}
			break;

		case 'isp_phone':
			if ( ! empty( $fields['phone'] ) ) {
				printf( '<a href="%1$s">%2$s</a>', esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $fields['phone'] ) ), esc_html( $fields['phone'] ) );
			} else {
				echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . esc_html__( 'None', 'interior-specifics-core' ) . '</span>';
			}
			break;

		case 'isp_status':
			echo isp_lead_status_badge( isp_lead_status( $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in isp_lead_status_badge().
			break;

		case 'isp_received':
			$time = get_post_timestamp( $post_id );
			if ( $time ) {
				printf(
					'<time datetime="%1$s" title="%2$s">%3$s</time>',
					esc_attr( wp_date( DATE_W3C, $time ) ),
					esc_attr( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) ),
					esc_html(
						( time() - $time < DAY_IN_SECONDS )
							/* translators: %s: human-readable time difference, e.g. "3 hours". */
							? sprintf( __( '%s ago', 'interior-specifics-core' ), human_time_diff( $time ) )
							: wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time )
					)
				);
			}
			break;
	}
}

add_filter( 'manage_edit-is_lead_sortable_columns', 'isp_lead_sortable_columns' );
/**
 * Received sorts by post date, newest first on the first click.
 *
 * @param array<string, mixed> $columns Sortable columns.
 * @return array<string, mixed>
 */
function isp_lead_sortable_columns( array $columns ): array {
	$columns['isp_received'] = array( 'date', true );
	return $columns;
}

add_action( 'restrict_manage_posts', 'isp_lead_filter_dropdowns', 10, 2 );
/**
 * Form type and status filters above the list.
 *
 * @param string $post_type Post type.
 * @param string $which     Top or bottom nav.
 */
function isp_lead_filter_dropdowns( string $post_type, string $which = 'top' ): void {
	if ( 'is_lead' !== $post_type || 'top' !== $which ) {
		return;
	}
	$filters = isp_lead_request_filters();

	echo '<label for="isp-filter-lead-type" class="screen-reader-text">' . esc_html__( 'Filter by form type', 'interior-specifics-core' ) . '</label>';
	echo '<select name="lead_type" id="isp-filter-lead-type">';
	echo '<option value="">' . esc_html__( 'All form types', 'interior-specifics-core' ) . '</option>';
	foreach ( isp_forms() as $type => $form ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $type ), selected( $filters['type'], $type, false ), esc_html( $form['label'] ) );
	}
	echo '</select>';

	echo '<label for="isp-filter-lead-status" class="screen-reader-text">' . esc_html__( 'Filter by status', 'interior-specifics-core' ) . '</label>';
	echo '<select name="lead_status" id="isp-filter-lead-status">';
	echo '<option value="">' . esc_html__( 'All statuses', 'interior-specifics-core' ) . '</option>';
	foreach ( isp_lead_statuses() as $status => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $status ), selected( $filters['status'], $status, false ), esc_html( $label ) );
	}
	echo '</select>';
}

add_action( 'manage_posts_extra_tablenav', 'isp_lead_export_button' );
/**
 * "Export CSV" submits the list's own filter form, so the export matches what is on screen.
 *
 * @param string $which Top or bottom nav.
 */
function isp_lead_export_button( string $which ): void {
	if ( 'top' !== $which || ! isp_is_leads_list_screen() || ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	echo '<div class="alignleft actions isp-lead-export">';
	wp_nonce_field( 'isp_export_leads', 'isp_export_nonce', false );
	printf(
		'<button type="submit" name="isp_export" value="csv" class="button">%s</button>',
		esc_html__( 'Export CSV', 'interior-specifics-core' )
	);
	echo '</div>';
}

add_action( 'pre_get_posts', 'isp_lead_filter_query' );
/**
 * Apply the type and status filters to the admin list query.
 *
 * @param WP_Query $query Query.
 */
function isp_lead_filter_query( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() || 'is_lead' !== $query->get( 'post_type' ) ) {
		return;
	}
	global $pagenow;
	if ( 'edit.php' !== $pagenow ) {
		return;
	}
	$meta_query = isp_lead_meta_query( isp_lead_request_filters() );
	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin filter on a small private table.
	}
}

add_filter( 'views_edit-is_lead', 'isp_lead_views' );
/**
 * Replace the redundant "Private" / "Mine" views with workflow status views.
 *
 * @param array<string, string> $views View links.
 * @return array<string, string>
 */
function isp_lead_views( array $views ): array {
	$filters = isp_lead_request_filters();
	$out     = array();
	if ( isset( $views['all'] ) ) {
		$out['all'] = $filters['status'] ? str_replace( array( ' class="current"', ' aria-current="page"' ), '', $views['all'] ) : $views['all'];
	}
	foreach ( isp_lead_statuses() as $status => $label ) {
		$current                 = $filters['status'] === $status;
		$out[ 'isp_' . $status ] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
			esc_url(
				add_query_arg(
					array(
						'post_type'   => 'is_lead',
						'lead_status' => $status,
					),
					admin_url( 'edit.php' )
				)
			),
			$current ? ' class="current" aria-current="page"' : '',
			esc_html( $label ),
			esc_html( number_format_i18n( isp_lead_count( $status ) ) )
		);
	}
	if ( isset( $views['trash'] ) ) {
		$out['trash'] = $views['trash'];
	}
	return $out;
}

add_filter( 'post_row_actions', 'isp_lead_row_actions', 10, 2 );
/**
 * Row actions: View, Mark contacted, Trash. Quick Edit is removed (leads are read-only).
 *
 * @param array<string, string> $actions Actions.
 * @param WP_Post               $post    Lead.
 * @return array<string, string>
 */
function isp_lead_row_actions( array $actions, WP_Post $post ): array {
	if ( 'is_lead' !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'], $actions['view'] );

	if ( isset( $actions['edit'] ) ) {
		$actions['edit'] = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( (string) get_edit_post_link( $post->ID ) ),
			/* translators: %s: lead name. */
			esc_attr( sprintf( __( 'View lead from %s', 'interior-specifics-core' ), isp_lead_display_name( $post->ID ) ) ),
			esc_html__( 'View', 'interior-specifics-core' )
		);
	}

	if ( 'trash' !== $post->post_status && current_user_can( 'edit_post', $post->ID ) && 'contacted' !== isp_lead_status( $post->ID ) ) {
		$url     = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'isp_lead_status',
					'lead'   => $post->ID,
					'status' => 'contacted',
				),
				admin_url( 'admin-post.php' )
			),
			'isp_lead_status_' . $post->ID
		);
		$marked  = array(
			'isp_contacted' => sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				esc_url( $url ),
				/* translators: %s: lead name. */
				esc_attr( sprintf( __( 'Mark lead from %s as contacted', 'interior-specifics-core' ), isp_lead_display_name( $post->ID ) ) ),
				esc_html__( 'Mark contacted', 'interior-specifics-core' )
			),
		);
		$actions = array_slice( $actions, 0, 1, true ) + $marked + array_slice( $actions, 1, null, true );
	}

	return $actions;
}

add_action( 'admin_post_isp_lead_status', 'isp_lead_status_action' );
/**
 * Row action handler: set a lead's status, then return to the list.
 */
function isp_lead_status_action(): void {
	$lead_id = isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0;
	$status  = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

	check_admin_referer( 'isp_lead_status_' . $lead_id );

	if ( ! $lead_id || 'is_lead' !== get_post_type( $lead_id ) || ! current_user_can( 'edit_post', $lead_id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to edit this lead.', 'interior-specifics-core' ), '', array( 'response' => 403 ) );
	}
	if ( ! array_key_exists( $status, isp_lead_statuses() ) ) {
		wp_die( esc_html__( 'Unknown lead status.', 'interior-specifics-core' ), '', array( 'response' => 400 ) );
	}

	update_post_meta( $lead_id, 'isp_status', $status );

	$back = wp_get_referer();
	$back = $back ? $back : admin_url( 'edit.php?post_type=is_lead' );
	wp_safe_redirect( add_query_arg( 'isp_updated', 1, remove_query_arg( 'isp_updated', $back ) ) );
	exit;
}

add_filter( 'bulk_actions-edit-is_lead', 'isp_lead_bulk_actions' );
/**
 * Bulk actions: status changes and trash. Core bulk edit is removed.
 *
 * @param array<string, string> $actions Bulk actions.
 * @return array<string, string>
 */
function isp_lead_bulk_actions( array $actions ): array {
	unset( $actions['edit'] );
	return array(
		'isp_mark_contacted' => __( 'Mark contacted', 'interior-specifics-core' ),
		'isp_mark_closed'    => __( 'Mark closed', 'interior-specifics-core' ),
		'isp_mark_new'       => __( 'Mark new', 'interior-specifics-core' ),
	) + $actions;
}

add_filter( 'handle_bulk_actions-edit-is_lead', 'isp_lead_handle_bulk_actions', 10, 3 );
/**
 * Apply a bulk status change. Core has already verified the bulk-posts nonce.
 *
 * @param string $redirect Redirect URL.
 * @param string $action   Action.
 * @param int[]  $post_ids Selected leads.
 */
function isp_lead_handle_bulk_actions( string $redirect, string $action, array $post_ids ): string {
	if ( ! str_starts_with( $action, 'isp_mark_' ) ) {
		return $redirect;
	}
	$status = substr( $action, strlen( 'isp_mark_' ) );
	if ( ! array_key_exists( $status, isp_lead_statuses() ) ) {
		return $redirect;
	}
	$done = 0;
	foreach ( array_map( 'absint', $post_ids ) as $lead_id ) {
		if ( 'is_lead' === get_post_type( $lead_id ) && current_user_can( 'edit_post', $lead_id ) ) {
			update_post_meta( $lead_id, 'isp_status', $status );
			++$done;
		}
	}
	return add_query_arg( 'isp_updated', $done, $redirect );
}

add_action( 'admin_notices', 'isp_lead_list_notices' );
/**
 * Confirmation after a row or bulk status change.
 */
function isp_lead_list_notices(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	if ( ! isp_is_leads_list_screen() || ! isset( $_GET['isp_updated'] ) ) {
		return;
	}
	$count = absint( $_GET['isp_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: number of leads. */
				_n( '%s lead updated.', '%s leads updated.', $count, 'interior-specifics-core' ),
				number_format_i18n( $count )
			)
		)
	);
}

add_filter( 'removable_query_args', 'isp_lead_removable_query_args' );
/**
 * Drop the one-off notice flag and the export nonce from the address bar after display.
 *
 * @param string[] $args Args.
 * @return string[]
 */
function isp_lead_removable_query_args( array $args ): array {
	$args[] = 'isp_updated';
	$args[] = 'isp_export_nonce';
	return $args;
}

/*
 * -------------------------------------------------------------------------
 * Menu bubble
 * -------------------------------------------------------------------------
 */

add_action( 'admin_menu', 'isp_lead_menu_bubble', 999 );
/**
 * Show the number of new leads next to the Leads menu item, like pending comments.
 */
function isp_lead_menu_bubble(): void {
	global $menu;
	if ( ! current_user_can( 'edit_pages' ) || ! is_array( $menu ) ) {
		return;
	}
	$count = isp_lead_count( 'new' );
	if ( ! $count ) {
		return;
	}
	foreach ( $menu as $position => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=is_lead' === $item[2] ) {
			$menu[ $position ][0] .= sprintf( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- standard way to add a count bubble.
				' <span class="awaiting-mod count-%1$d"><span class="pending-count" aria-hidden="true">%2$s</span><span class="screen-reader-text">%3$s</span></span>',
				$count,
				esc_html( number_format_i18n( $count ) ),
				esc_html(
					sprintf(
						/* translators: %s: number of new leads. */
						_n( '%s new lead', '%s new leads', $count, 'interior-specifics-core' ),
						number_format_i18n( $count )
					)
				)
			);
			break;
		}
	}
}

/*
 * -------------------------------------------------------------------------
 * Single lead screen
 * -------------------------------------------------------------------------
 */

add_action( 'add_meta_boxes_is_lead', 'isp_lead_meta_boxes' );
/**
 * Replace the editing UI with a read-only details box and a status box.
 */
function isp_lead_meta_boxes(): void {
	// The title is generated from the submission; show it read-only instead of in an input.
	remove_post_type_support( 'is_lead', 'title' );
	remove_meta_box( 'submitdiv', 'is_lead', 'side' );
	remove_meta_box( 'slugdiv', 'is_lead', 'normal' );

	add_meta_box( 'isp_lead_details', __( 'Submission', 'interior-specifics-core' ), 'isp_lead_details_box', 'is_lead', 'normal', 'high' );
	add_meta_box( 'isp_lead_status', __( 'Lead status', 'interior-specifics-core' ), 'isp_lead_status_box', 'is_lead', 'side', 'high' );
}

add_action( 'edit_form_top', 'isp_lead_heading' );
/**
 * Lead title above the meta boxes (the title input is removed).
 *
 * @param WP_Post $post Post being edited.
 */
function isp_lead_heading( WP_Post $post ): void {
	if ( 'is_lead' !== $post->post_type ) {
		return;
	}
	printf( '<h2 class="isp-lead-heading">%s</h2>', esc_html( get_the_title( $post ) ) );
}

/**
 * Format a stored field value for display according to its schema type.
 *
 * @param string               $value Stored value.
 * @param array<string, mixed> $field Field schema (may be empty for removed fields).
 */
function isp_lead_format_value( string $value, array $field ): string {
	$type = (string) ( $field['type'] ?? 'text' );
	if ( 'email' === $type && is_email( $value ) ) {
		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $value ), esc_html( $value ) );
	}
	if ( 'tel' === $type ) {
		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $value ) ), esc_html( $value ) );
	}
	if ( 'date' === $type ) {
		$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
		if ( $date ) {
			return esc_html( wp_date( (string) get_option( 'date_format' ), $date->getTimestamp() ) );
		}
	}
	return nl2br( esc_html( $value ) );
}

/**
 * Read-only table of every submitted field plus request metadata.
 *
 * @param WP_Post $post Lead.
 */
function isp_lead_details_box( WP_Post $post ): void {
	$type   = (string) get_post_meta( $post->ID, 'isp_form_type', true );
	$form   = isp_form( $type );
	$schema = $form ? $form['fields'] : array();
	$values = isp_lead_fields( $post->ID );

	// Schema order first, then any stored keys the current schema no longer knows about.
	$rows = array();
	foreach ( $schema as $key => $field ) {
		if ( isset( $values[ $key ] ) && '' !== $values[ $key ] ) {
			$rows[] = array( (string) ( $field['label'] ?? $key ), isp_lead_format_value( $values[ $key ], $field ) );
		}
	}
	$known = isp_lead_known_fields();
	foreach ( array_diff_key( $values, $schema ) as $key => $value ) {
		if ( '' !== $value ) {
			$rows[] = array( $known[ $key ] ?? (string) $key, isp_lead_format_value( $value, array() ) );
		}
	}

	echo '<table class="isp-lead-table" role="presentation"><tbody>';
	printf( '<tr><th scope="row">%1$s</th><td>%2$s</td></tr>', esc_html__( 'Form', 'interior-specifics-core' ), esc_html( isp_lead_type_label( $type ) ) );
	if ( ! $rows ) {
		printf( '<tr><td colspan="2" class="isp-lead-empty">%s</td></tr>', esc_html__( 'No field values were stored for this lead.', 'interior-specifics-core' ) );
	}
	foreach ( $rows as $row ) {
		printf( '<tr><th scope="row">%1$s</th><td>%2$s</td></tr>', esc_html( $row[0] ), wp_kses_post( $row[1] ) );
	}
	echo '</tbody></table>';

	$source = (string) get_post_meta( $post->ID, 'isp_source_url', true );
	$agent  = (string) get_post_meta( $post->ID, 'isp_user_agent', true );
	$sent   = (string) get_post_meta( $post->ID, 'isp_email_sent', true );
	$time   = get_post_timestamp( $post );
	$none   = '<span class="isp-lead-empty">' . esc_html__( 'Not recorded', 'interior-specifics-core' ) . '</span>';

	$sent_label = array(
		'yes' => __( 'Yes', 'interior-specifics-core' ),
		'no'  => __( 'No — delivery failed, follow up manually', 'interior-specifics-core' ),
	);

	echo '<h3 class="isp-lead-section">' . esc_html__( 'Request details', 'interior-specifics-core' ) . '</h3>';
	echo '<table class="isp-lead-table" role="presentation"><tbody>';
	printf(
		'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
		esc_html__( 'Received', 'interior-specifics-core' ),
		$time ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) ) : $none // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $none is escaped above.
	);
	printf(
		'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
		esc_html__( 'Source page', 'interior-specifics-core' ),
		$source ? sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $source ), esc_html( $source ) ) : $none // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inline.
	);
	printf(
		'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
		esc_html__( 'Notification email sent', 'interior-specifics-core' ),
		isset( $sent_label[ $sent ] ) ? esc_html( $sent_label[ $sent ] ) : $none // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inline.
	);
	printf(
		'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
		esc_html__( 'User agent', 'interior-specifics-core' ),
		$agent ? '<code>' . esc_html( $agent ) . '</code>' : $none // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inline.
	);
	echo '</tbody></table>';
}

/**
 * Status select with its own save button and a trash link.
 *
 * @param WP_Post $post Lead.
 */
function isp_lead_status_box( WP_Post $post ): void {
	$current = isp_lead_status( $post->ID );
	wp_nonce_field( 'isp_lead_status_save', 'isp_lead_status_nonce' );

	echo '<div class="isp-lead-status-box">';
	echo '<label for="isp-lead-status">' . esc_html__( 'Status', 'interior-specifics-core' ) . '</label>';
	echo '<select name="isp_status" id="isp-lead-status">';
	foreach ( isp_lead_statuses() as $status => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $status ), selected( $current, $status, false ), esc_html( $label ) );
	}
	echo '</select>';
	echo '<p class="description">' . wp_kses_post( isp_lead_status_badge( $current ) ) . '</p>';

	echo '<div class="isp-lead-status-actions">';
	if ( current_user_can( 'delete_post', $post->ID ) ) {
		printf(
			'<a class="submitdelete deletion" href="%1$s">%2$s</a>',
			esc_url( (string) get_delete_post_link( $post->ID ) ),
			esc_html__( 'Move to Trash', 'interior-specifics-core' )
		);
	} else {
		echo '<span></span>';
	}
	submit_button( __( 'Save status', 'interior-specifics-core' ), 'primary', 'save', false, array( 'id' => 'isp-lead-save' ) );
	echo '</div></div>';
}

add_action( 'save_post_is_lead', 'isp_save_lead_status' );
/**
 * Persist the status chosen on the single lead screen.
 *
 * @param int $post_id Lead ID.
 */
function isp_save_lead_status( int $post_id ): void {
	if ( ! isset( $_POST['isp_lead_status_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['isp_lead_status_nonce'] ) ), 'isp_lead_status_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = isset( $_POST['isp_status'] ) ? sanitize_key( wp_unslash( $_POST['isp_status'] ) ) : '';
	if ( array_key_exists( $status, isp_lead_statuses() ) ) {
		update_post_meta( $post_id, 'isp_status', $status );
	}
}

add_filter( 'post_updated_messages', 'isp_lead_updated_messages' );
/**
 * "Lead updated." instead of the generic post message.
 *
 * @param array<string, array<int, string>> $messages Messages per post type.
 * @return array<string, array<int, string>>
 */
function isp_lead_updated_messages( array $messages ): array {
	$messages['is_lead']    = array_fill( 0, 11, __( 'Lead updated.', 'interior-specifics-core' ) );
	$messages['is_lead'][0] = '';
	return $messages;
}

/*
 * -------------------------------------------------------------------------
 * CSV export
 * -------------------------------------------------------------------------
 */

add_action( 'load-edit.php', 'isp_lead_maybe_export' );
/**
 * Stream the filtered leads as CSV when the Export CSV button submitted the list form.
 */
function isp_lead_maybe_export(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified below.
	if ( ! isset( $_GET['isp_export'] ) || ! isset( $_GET['post_type'] ) || 'is_lead' !== $_GET['post_type'] ) {
		return;
	}
	if ( ! isset( $_GET['isp_export_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['isp_export_nonce'] ) ), 'isp_export_leads' ) ) {
		wp_die(
			esc_html__( 'The export link has expired. Please reload the page and try again.', 'interior-specifics-core' ),
			'',
			array(
				'response'  => 403,
				'back_link' => true,
			)
		);
	}
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to export leads.', 'interior-specifics-core' ), '', array( 'response' => 403 ) );
	}
	isp_lead_export_csv( isp_lead_export_query_args() );
	exit;
}

/**
 * Query args for the export, mirroring the list screen's filters (type, status, view, search, month, order).
 *
 * @return array<string, mixed>
 */
function isp_lead_export_query_args(): array {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce verified in isp_lead_maybe_export().
	$post_status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';
	$search      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$month       = isset( $_GET['m'] ) ? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_GET['m'] ) ) ) : '';
	$order       = isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ? 'ASC' : 'DESC';
	// phpcs:enable

	$args = array(
		'post_type'              => 'is_lead',
		'post_status'            => 'trash' === $post_status ? 'trash' : array( 'private', 'publish', 'draft', 'pending' ),
		'orderby'                => 'date',
		'order'                  => $order,
		'update_post_term_cache' => false,
		'no_found_rows'          => true,
	);
	if ( '' !== $search ) {
		$args['s'] = $search;
	}
	if ( $month ) {
		$args['m'] = $month;
	}
	$meta_query = isp_lead_meta_query( isp_lead_request_filters() );
	if ( $meta_query ) {
		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin export.
	}
	return $args;
}

/**
 * Neutralise spreadsheet formulas (CSV injection) while leaving plain numbers and phones alone.
 *
 * @param string $value Cell value.
 */
function isp_csv_cell( string $value ): string {
	if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) && ! preg_match( '/^[+-]?[0-9][0-9 ().-]*$/', $value ) ) {
		return "'" . $value;
	}
	return $value;
}

/**
 * Stream a CSV of matching leads: date, type, status, then one column per known field.
 *
 * @param array<string, mixed> $args WP_Query args (without paging).
 */
function isp_lead_export_csv( array $args ): void {
	$known = isp_lead_known_fields();

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="leads-' . wp_date( 'Y-m-d' ) . '.csv"' );
	header( 'X-Content-Type-Options: nosniff' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming response.
	if ( ! $out ) {
		return;
	}
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- UTF-8 BOM so Excel detects the encoding.

	$header = array_merge(
		array(
			__( 'Received', 'interior-specifics-core' ),
			__( 'Type', 'interior-specifics-core' ),
			__( 'Status', 'interior-specifics-core' ),
		),
		array_values( $known ),
		array( __( 'Source page', 'interior-specifics-core' ) )
	);
	fputcsv( $out, $header, ',', '"', '' );

	$statuses = isp_lead_statuses();
	$page     = 1;
	do {
		$query = new WP_Query(
			$args + array(
				'posts_per_page' => 200,
				'paged'          => $page,
			)
		);
		foreach ( $query->posts as $lead ) {
			$fields = isp_lead_fields( $lead->ID );
			$row    = array(
				(string) get_post_time( 'Y-m-d H:i', false, $lead ),
				isp_lead_type_label( (string) get_post_meta( $lead->ID, 'isp_form_type', true ) ),
				$statuses[ isp_lead_status( $lead->ID ) ],
			);
			foreach ( array_keys( $known ) as $key ) {
				$row[] = $fields[ $key ] ?? '';
			}
			$row[] = (string) get_post_meta( $lead->ID, 'isp_source_url', true );
			fputcsv( $out, array_map( 'isp_csv_cell', $row ), ',', '"', '' );
		}
		$fetched = count( $query->posts );
		++$page;
	} while ( 200 === $fetched );

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- streaming response.
}

/*
 * -------------------------------------------------------------------------
 * Dashboard widget
 * -------------------------------------------------------------------------
 */

add_action( 'wp_dashboard_setup', 'isp_lead_dashboard_widget' );
/**
 * "Recent leads" for editors and administrators.
 */
function isp_lead_dashboard_widget(): void {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	wp_add_dashboard_widget( 'isp_recent_leads', __( 'Recent leads', 'interior-specifics-core' ), 'isp_lead_dashboard_widget_render' );
}

/**
 * Latest five leads with status badges.
 */
function isp_lead_dashboard_widget_render(): void {
	$leads = get_posts(
		array(
			'post_type'              => 'is_lead',
			'post_status'            => array( 'private', 'publish', 'draft', 'pending' ),
			'posts_per_page'         => 5,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	if ( ! $leads ) {
		echo '<p>' . esc_html__( 'No leads yet. Enquiries from the website forms will appear here.', 'interior-specifics-core' ) . '</p>';
		return;
	}

	echo '<ul class="isp-recent-leads">';
	foreach ( $leads as $lead ) {
		$time = get_post_timestamp( $lead );
		printf(
			'<li><span><a href="%1$s"><strong>%2$s</strong></a><span class="isp-recent-leads__meta">%3$s</span></span>%4$s</li>',
			esc_url( (string) get_edit_post_link( $lead->ID ) ),
			esc_html( isp_lead_display_name( $lead->ID ) ),
			esc_html(
				sprintf(
					/* translators: 1: form label, 2: human-readable time difference. */
					__( '%1$s · %2$s ago', 'interior-specifics-core' ),
					isp_lead_type_label( (string) get_post_meta( $lead->ID, 'isp_form_type', true ) ),
					$time ? human_time_diff( $time ) : ''
				)
			),
			isp_lead_status_badge( isp_lead_status( $lead->ID ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in isp_lead_status_badge().
		);
	}
	echo '</ul>';

	$new = isp_lead_count( 'new' );
	printf(
		'<p class="isp-recent-leads__footer"><a href="%1$s">%2$s</a>%3$s</p>',
		esc_url( admin_url( 'edit.php?post_type=is_lead' ) ),
		esc_html__( 'View all leads', 'interior-specifics-core' ),
		$new ? ' &middot; <a href="' . esc_url( admin_url( 'edit.php?post_type=is_lead&lead_status=new' ) ) . '">' . esc_html(
			sprintf(
				/* translators: %s: number of new leads. */
				_n( '%s new', '%s new', $new, 'interior-specifics-core' ),
				number_format_i18n( $new )
			)
		) . '</a>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inline.
	);
}
