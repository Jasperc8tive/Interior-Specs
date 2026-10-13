<?php
/**
 * Lead capture: REST endpoint (JS forms), admin-post fallback (no-JS), validation,
 * spam defences, storage as a private `is_lead` post, notification email, the privacy
 * exporter/eraser and the retention clean-up.
 *
 * Order of work is deliberate: a lead is stored *before* any email is attempted, so a
 * mail outage never loses an enquiry.
 *
 * Why no nonce: these forms are public and unauthenticated, so there is no session for a
 * CSRF attack to ride on, and nonces in cached HTML go stale. Abuse is handled instead by a
 * honeypot, a signed token with a minimum fill time and a maximum age, a per-IP rate limit
 * (IPv6 grouped by /64) and a site-wide hourly cap per form type.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

const ISP_LEAD_MIN_SECONDS   = 3;
const ISP_LEAD_RATE_LIMIT    = 5;
const ISP_LEAD_RATE_WINDOW   = 10 * MINUTE_IN_SECONDS;
const ISP_LEAD_GLOBAL_LIMIT  = 30; // Stored leads per form type per hour, site-wide.
const ISP_GUIDE_DAILY_LIMIT  = 50; // Guide emails per day, site-wide.
const ISP_LEAD_PRIVACY_BATCH = 50;

/*
 * Months after which closed leads are deleted and every lead loses its IP hash and user
 * agent (see isp_purge_old_leads()). Override in wp-config.php, or with the
 * `isp_lead_retention_months` filter; 0 switches the clean-up off.
 */
if ( ! defined( 'ISP_LEAD_RETENTION_MONTHS' ) ) {
	define( 'ISP_LEAD_RETENTION_MONTHS', 24 );
}

add_action( 'init', 'isp_register_lead_type' );
/**
 * Private store for submissions. Uses page capabilities so only editors and admins can read leads.
 */
function isp_register_lead_type(): void {
	register_post_type(
		'is_lead',
		array(
			'labels'              => array(
				'name'          => __( 'Leads', 'interior-specifics-core' ),
				'singular_name' => __( 'Lead', 'interior-specifics-core' ),
				'edit_item'     => __( 'Lead', 'interior-specifics-core' ),
				'search_items'  => __( 'Search Leads', 'interior-specifics-core' ),
				'not_found'     => __( 'No leads yet.', 'interior-specifics-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'show_in_nav_menus'   => false,
			'menu_position'       => 3,
			'menu_icon'           => 'dashicons-email-alt',
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'supports'            => array( 'title' ),
			'rewrite'             => false,
			'query_var'           => false,
		)
	);
}

add_action( 'rest_api_init', 'isp_register_lead_route' );
/**
 * POST /wp-json/is/v1/lead  {formType, data:{...}, company, token}
 */
function isp_register_lead_route(): void {
	register_rest_route(
		'is/v1',
		'/lead',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => '__return_true',
			'callback'            => 'isp_rest_lead',
			'args'                => array(
				'formType' => array(
					'type'     => 'string',
					'required' => true,
				),
				'data'     => array(
					'type'     => 'object',
					'required' => true,
				),
				'company'  => array( 'type' => 'string' ),
				'token'    => array( 'type' => 'string' ),
			),
		)
	);
}

/**
 * REST handler.
 *
 * @param WP_REST_Request $request Request.
 */
function isp_rest_lead( WP_REST_Request $request ): WP_REST_Response {
	$result = isp_process_lead(
		(string) $request['formType'],
		(array) $request['data'],
		(string) ( $request['company'] ?? '' ),
		(string) ( $request['token'] ?? '' ),
		// Only same-site pages count as the source; a forged Referer is dropped.
		(string) wp_validate_redirect( (string) $request->get_header( 'referer' ), '' )
	);
	$response = new WP_REST_Response( $result['body'], $result['status'] );
	$response->header( 'Cache-Control', 'no-store' );
	return $response;
}

add_action( 'admin_post_nopriv_isp_lead', 'isp_admin_post_lead' );
add_action( 'admin_post_isp_lead', 'isp_admin_post_lead' );
/**
 * No-JS fallback: classic POST, then redirect back with
 * ?is_status=success|invalid|throttled|expired|error. A failed submission also carries
 * &is_err={key}: its cleaned values and field messages are kept briefly (see
 * isp_form_feedback_store()) so the theme can refill the form and flag the fields.
 */
function isp_admin_post_lead(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public lead form, see file docblock.
	$type   = isset( $_POST['formType'] ) ? sanitize_key( wp_unslash( $_POST['formType'] ) ) : '';
	$form   = isp_form( $type );
	$data   = array();
	$fields = $form ? array_keys( $form['fields'] ) : array();
	foreach ( $fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$data[ $key ] = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field type in isp_process_lead().
		}
	}
	$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$token   = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
	$anchor  = isset( $_POST['anchor'] ) ? sanitize_html_class( wp_unslash( $_POST['anchor'] ) ) : '';
	// phpcs:enable

	$referer = wp_get_referer();
	$result  = isp_process_lead( $type, $data, $company, $token, (string) $referer );
	$target  = $referer ? $referer : home_url( '/' );
	$target  = remove_query_arg( array( 'is_status', 'is_err' ), $target );

	if ( $result['body']['ok'] ) {
		$target = add_query_arg( 'is_status', 'success', $target );
	} else {
		$code   = (string) ( $result['body']['code'] ?? '' );
		$status = 'error';
		if ( 422 === $result['status'] ) {
			$status = 'invalid';
		} elseif ( 429 === $result['status'] ) {
			$status = 'throttled';
		} elseif ( 'token_expired' === $code ) {
			$status = 'expired';
		}
		$args = array( 'is_status' => $status );
		if ( $form ) {
			$args['is_err'] = isp_form_feedback_store(
				array(
					'values' => isp_sanitize_lead_values( $form, $data ),
					'fields' => (array) ( $result['body']['fields'] ?? array() ),
					'error'  => (string) ( $result['body']['error'] ?? '' ),
				)
			);
		}
		$target = add_query_arg( $args, $target );
	}

	wp_safe_redirect( $target . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

/**
 * Validate, store and notify. Shared by REST and admin-post.
 *
 * @param string               $type     Form type.
 * @param array<string, mixed> $data     Raw field values.
 * @param string               $honeypot Value of the hidden `company` field.
 * @param string               $token    Signed render-time token.
 * @param string               $referer  Page the form was submitted from.
 * @return array{status: int, body: array<string, mixed>}
 */
function isp_process_lead( string $type, array $data, string $honeypot, string $token, string $referer = '' ): array {
	$form = isp_form( $type );
	if ( ! $form ) {
		return isp_lead_response( 400, __( 'Invalid form data.', 'interior-specifics-core' ) );
	}

	// Bots: pretend success, store nothing.
	if ( '' !== trim( $honeypot ) ) {
		return isp_lead_response( 200 );
	}
	$age = isp_form_token_age( $token );
	if ( null === $age ) {
		return isp_lead_response( 400, __( 'Invalid request.', 'interior-specifics-core' ) );
	}
	if ( $age > isp_form_token_max_age() ) {
		// The page sat open too long (or the token was harvested). Hand back a fresh token,
		// back-dated past the minimum fill time, so a real visitor's form can resubmit at once.
		$response                  = isp_lead_response( 400, __( 'This page had been open for a while. Please submit the form again.', 'interior-specifics-core' ), 'token_expired' );
		$response['body']['token'] = isp_form_token( time() - ISP_LEAD_MIN_SECONDS );
		return $response;
	}
	if ( $age < ISP_LEAD_MIN_SECONDS ) {
		return isp_lead_response( 200 );
	}

	$throttled  = __( 'Too many submissions. Please try again in a few minutes.', 'interior-specifics-core' );
	$rate_key   = 'isp_rl_' . md5( isp_rate_bucket() . '|' . $type );
	$count      = (int) get_transient( $rate_key );
	$global_key = 'isp_rlg_' . md5( $type ) . '_' . gmdate( 'YmdH' );
	$global     = (int) get_transient( $global_key );
	if ( $count >= ISP_LEAD_RATE_LIMIT ) {
		return isp_lead_response( 429, $throttled, 'rate_limited' );
	}
	// Past the site-wide cap the lead is still stored (a flood must never lock out real
	// visitors), but no email goes out: outbound mail is what a flood would abuse.
	$over_cap = $global >= isp_lead_global_limit( $type );

	$validated = isp_validate_lead( $form, $data );
	if ( $validated['errors'] ) {
		$response                   = isp_lead_response( 422, __( 'Please check the highlighted fields.', 'interior-specifics-core' ), 'invalid_fields' );
		$response['body']['fields'] = $validated['errors'];
		return $response;
	}
	$values = $validated['values'];

	set_transient( $rate_key, $count + 1, ISP_LEAD_RATE_WINDOW );
	set_transient( $global_key, $global + 1, HOUR_IN_SECONDS );

	$lead_id = isp_store_lead( $type, $form, $values, $referer );
	$sent    = $over_cap ? false : isp_send_lead_notification( $type, $form, $values, $lead_id );
	if ( $over_cap && $lead_id ) {
		update_post_meta( $lead_id, 'isp_email_sent', 'no' );
	}

	if ( 'lead-magnet' === $type && ! $over_cap ) {
		isp_send_guide_email( $values );
	}

	if ( ! $lead_id && ! $sent ) {
		return isp_lead_response( 500, __( 'Something went wrong.', 'interior-specifics-core' ) );
	}

	/**
	 * Fires after a lead is stored — hook an email marketing service here.
	 *
	 * @param string               $type    Form type.
	 * @param array<string,string> $values  Clean field values.
	 * @param int                  $lead_id Stored lead post ID (0 if storage failed).
	 */
	do_action( 'isp_lead_submitted', $type, $values, (int) $lead_id );

	return isp_lead_response( 200 );
}

/**
 * Site-wide hourly cap per form type on lead EMAILS: a circuit breaker for floods that rotate
 * addresses past the per-IP limit. Leads beyond it are still stored, just not emailed.
 *
 * @param string $type Form type.
 */
function isp_lead_global_limit( string $type ): int {
	/**
	 * Filter the site-wide hourly cap on stored leads for one form type.
	 *
	 * @param int    $limit Default ISP_LEAD_GLOBAL_LIMIT.
	 * @param string $type  Form type.
	 */
	return max( 1, (int) apply_filters( 'isp_lead_global_limit', ISP_LEAD_GLOBAL_LIMIT, $type ) );
}

/**
 * Response envelope matching the Next.js API contract: {ok, error?}, plus an optional
 * machine-readable `code` (token_expired, rate_limited, invalid_fields) for the front end.
 *
 * @param int    $status HTTP status.
 * @param string $error  Error message for non-2xx.
 * @param string $code   Error code.
 * @return array{status: int, body: array<string, mixed>}
 */
function isp_lead_response( int $status, string $error = '', string $code = '' ): array {
	$body = array( 'ok' => $status < 300 );
	if ( $error ) {
		$body['error'] = $error;
	}
	if ( $code ) {
		$body['code'] = $code;
	}
	return array(
		'status' => $status,
		'body'   => $body,
	);
}

/**
 * Sanitise raw values against the form schema (no validation). Unknown fields are ignored;
 * a ticked checkbox becomes 'Yes', select values stay as their option value.
 *
 * @param array{fields: array<string, array<string, mixed>>} $form Form schema.
 * @param array<string, mixed>                               $data Raw values.
 * @return array<string, string>
 */
function isp_sanitize_lead_values( array $form, array $data ): array {
	$values = array();
	foreach ( $form['fields'] as $key => $field ) {
		$raw = $data[ $key ] ?? '';
		$raw = is_scalar( $raw ) ? (string) $raw : '';

		if ( 'checkbox' === $field['type'] ) {
			$values[ $key ] = in_array( strtolower( $raw ), array( 'on', '1', 'true', 'yes' ), true ) ? 'Yes' : '';
		} elseif ( 'textarea' === $field['type'] ) {
			$values[ $key ] = mb_substr( sanitize_textarea_field( $raw ), 0, 5000 );
		} else {
			$values[ $key ] = mb_substr( sanitize_text_field( $raw ), 0, 200 );
		}
	}
	return $values;
}

/**
 * Sanitise and validate against the form schema. Unknown fields are ignored. Select values
 * are checked against the option values and stored as their (translated) labels, so leads,
 * emails and the CSV read naturally.
 *
 * @param array{fields: array<string, array<string, mixed>>} $form Form schema.
 * @param array<string, mixed>                               $data Raw values.
 * @return array{values: array<string, string>, errors: array<string, string>}
 */
function isp_validate_lead( array $form, array $data ): array {
	$values = array();
	$errors = array();
	$clean  = isp_sanitize_lead_values( $form, $data );

	foreach ( $form['fields'] as $key => $field ) {
		$value = $clean[ $key ];

		if ( '' === $value ) {
			if ( ! empty( $field['required'] ) ) {
				$errors[ $key ] = __( 'This field is required.', 'interior-specifics-core' );
			}
			continue;
		}

		switch ( $field['type'] ) {
			case 'email':
				if ( ! is_email( $value ) ) {
					$errors[ $key ] = __( 'Please enter a valid email address.', 'interior-specifics-core' );
				}
				break;
			case 'tel':
				if ( ! preg_match( '/^[0-9+().\s-]{6,25}$/', $value ) ) {
					$errors[ $key ] = __( 'Please enter a valid phone number.', 'interior-specifics-core' );
				}
				break;
			case 'select':
				$options = isp_field_options( $field );
				if ( array_key_exists( $value, $options ) ) {
					$value = $options[ $value ];
				} elseif ( ! in_array( $value, $options, true ) ) {
					// A label is accepted too, for pages cached before options had separate values.
					$errors[ $key ] = __( 'Please choose one of the options.', 'interior-specifics-core' );
				}
				break;
			case 'date':
				$date  = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() );
				$today = new DateTimeImmutable( 'today', wp_timezone() );
				if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
					$errors[ $key ] = __( 'Please choose a valid date.', 'interior-specifics-core' );
				} elseif ( $date < $today ) {
					$errors[ $key ] = __( 'Please choose a date in the future.', 'interior-specifics-core' );
				}
				break;
		}

		$values[ $key ] = $value;
	}

	return array(
		'values' => $values,
		'errors' => $errors,
	);
}

/**
 * Persist a lead as a private post with its fields in meta.
 *
 * @param string                                             $type    Form type.
 * @param array{label: string, fields: array<string, mixed>} $form    Form schema.
 * @param array<string, string>                              $values  Clean values.
 * @param string                                             $referer Source page.
 * @return int Lead ID, or 0 on failure.
 */
function isp_store_lead( string $type, array $form, array $values, string $referer ): int {
	$who = $values['name'] ?? ( $values['email'] ?? '' );
	// wp_insert_post() and update_post_meta() unslash their input; these values are already
	// unslashed, so slash them once or every backslash a visitor typed would be lost.
	$lead_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'is_lead',
				'post_status'  => 'private',
				'post_title'   => $form['label'] . ( $who ? ' — ' . $who : '' ),
				// Plain-text copy so the admin search box finds leads by name, email or message.
				'post_content' => implode( "\n", array_map( static fn( $k, $v ) => "{$k}: {$v}", array_keys( $values ), $values ) ),
			)
		),
		true
	);
	if ( is_wp_error( $lead_id ) ) {
		return 0;
	}

	$agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$referer = (string) wp_validate_redirect( $referer, '' );
	update_post_meta( $lead_id, 'isp_form_type', $type );
	update_post_meta( $lead_id, 'isp_fields', wp_slash( $values ) );
	update_post_meta( $lead_id, 'isp_status', 'new' );
	update_post_meta( $lead_id, 'isp_ip_hash', isp_ip_hash() );
	update_post_meta( $lead_id, 'isp_user_agent', wp_slash( mb_substr( $agent, 0, 255 ) ) );
	update_post_meta( $lead_id, 'isp_source_url', wp_slash( esc_url_raw( $referer ) ) );
	if ( ! empty( $values['email'] ) ) {
		// Exact-match key for the privacy exporter and eraser.
		update_post_meta( $lead_id, 'isp_email', wp_slash( strtolower( $values['email'] ) ) );
	}

	return (int) $lead_id;
}

/**
 * Email the studio. Labels come from the schema; Reply-To is the visitor.
 *
 * @param string                                                            $type    Form type.
 * @param array{label: string, fields: array<string, array<string, mixed>>} $form    Form schema.
 * @param array<string, string>                                             $values  Clean values.
 * @param int                                                               $lead_id Stored lead ID.
 */
function isp_send_lead_notification( string $type, array $form, array $values, int $lead_id ): bool {
	$rows = '';
	foreach ( $values as $key => $value ) {
		$label = $form['fields'][ $key ]['label'] ?? $key;
		$rows .= sprintf(
			'<tr><td style="padding:8px 14px;font-weight:600;color:#1A1A1A;vertical-align:top;border-bottom:1px solid #eee">%s</td><td style="padding:8px 14px;color:#444;border-bottom:1px solid #eee">%s</td></tr>',
			esc_html( $label ),
			nl2br( esc_html( $value ) )
		);
	}

	$admin_link = $lead_id ? sprintf( '<p style="margin-top:16px"><a href="%s" style="color:#a8843f">%s</a></p>', esc_url( admin_url( 'post.php?post=' . $lead_id . '&action=edit' ) ), esc_html__( 'View lead in WordPress', 'interior-specifics-core' ) ) : '';

	$body = sprintf(
		'<div style="font-family:Arial,sans-serif;max-width:640px"><h2 style="color:#1A1A1A;font-weight:normal">%s</h2><table style="border-collapse:collapse;border:1px solid #eee;width:100%%">%s</table>%s<p style="color:#999;font-size:12px;margin-top:16px">%s</p></div>',
		/* translators: %s: form label, e.g. Contact Inquiry. */
		esc_html( sprintf( __( 'New %s', 'interior-specifics-core' ), $form['label'] ) ),
		$rows,
		$admin_link,
		/* translators: %s: site domain. */
		esc_html( sprintf( __( 'Sent from %s', 'interior-specifics-core' ), wp_parse_url( home_url(), PHP_URL_HOST ) ) )
	);

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	if ( ! empty( $values['email'] ) && is_email( $values['email'] ) ) {
		// Unquoted on purpose: wp_mail() and PHPMailer quote and encode the name themselves.
		$reply_name = isset( $values['name'] ) ? isp_mail_display_name( $values['name'] ) : '';
		$headers[]  = 'Reply-To: ' . ( '' !== $reply_name ? "{$reply_name} <{$values['email']}>" : $values['email'] );
	}

	$subject = $form['label'] . ( ! empty( $values['name'] ) ? ' — ' . $values['name'] : '' );
	$sent    = wp_mail( isp_lead_recipient(), $subject, $body, $headers );

	if ( $lead_id ) {
		update_post_meta( $lead_id, 'isp_email_sent', $sent ? 'yes' : 'no' );
	}
	return $sent;
}

/**
 * Send the free guide to someone who requested it (when a guide PDF is configured).
 *
 * The visitor chooses the recipient, so this is throttled: one guide per address per day and
 * ISP_GUIDE_DAILY_LIMIT (filter `isp_guide_daily_limit`) site-wide, and the greeting keeps
 * only letters from the first name, so no link or other text can be planted in it.
 *
 * @param array<string, string> $values Clean values.
 */
function isp_send_guide_email( array $values ): void {
	$guide = isp_setting( 'guide_url' );
	if ( ! $guide || empty( $values['email'] ) ) {
		return;
	}

	$recipient_key = 'isp_guide_' . md5( strtolower( $values['email'] ) );
	$daily_key     = 'isp_guide_day_' . gmdate( 'Ymd' );
	$daily         = (int) get_transient( $daily_key );
	/**
	 * Filter the site-wide daily cap on guide emails.
	 *
	 * @param int $limit Default ISP_GUIDE_DAILY_LIMIT.
	 */
	$daily_limit = (int) apply_filters( 'isp_guide_daily_limit', ISP_GUIDE_DAILY_LIMIT );
	if ( get_transient( $recipient_key ) || $daily >= $daily_limit ) {
		return;
	}
	set_transient( $recipient_key, 1, DAY_IN_SECONDS );
	set_transient( $daily_key, $daily + 1, DAY_IN_SECONDS );

	// A first name made only of letters (plus ' and -) is used; anything else falls back to "Hello,".
	$first = isset( $values['name'] ) ? explode( ' ', trim( $values['name'] ) )[0] : '';
	$first = preg_match( "/^[\\p{L}\\p{M}][\\p{L}\\p{M}'\\-]{0,39}$/u", $first ) ? $first : '';
	$body  = sprintf(
		'<div style="font-family:Georgia,serif;max-width:560px;color:#1A1A1A;line-height:1.7"><p>%s</p><p>%s</p><p><a href="%s" style="display:inline-block;background:#d4a574;color:#1a1a1a;padding:12px 24px;text-decoration:none;font-family:Arial,sans-serif;font-size:13px;letter-spacing:.15em;text-transform:uppercase">%s</a></p><p>%s</p><p>— %s</p></div>',
		/* translators: %s: first name. */
		esc_html( $first ? sprintf( __( 'Dear %s,', 'interior-specifics-core' ), $first ) : __( 'Hello,', 'interior-specifics-core' ) ),
		esc_html__( 'Thank you for your interest. Your complimentary copy of The 7 Secrets of Luxury Interior Design in Nigeria is ready.', 'interior-specifics-core' ),
		esc_url( $guide ),
		esc_html__( 'Download the guide', 'interior-specifics-core' ),
		esc_html__( 'A member of our team will be in touch within 24 hours to arrange your private consultation.', 'interior-specifics-core' ),
		esc_html( isp_setting( 'name' ) )
	);
	wp_mail( $values['email'], __( 'Your guide: The 7 Secrets of Luxury Interior Design in Nigeria', 'interior-specifics-core' ), $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/*
 * -------------------------------------------------------------------------
 * Privacy: Tools → Export / Erase Personal Data, and retention
 * -------------------------------------------------------------------------
 */

/**
 * Leads submitted with an email address, matched exactly on the `isp_email` key.
 *
 * @param string $email Email address.
 * @param int    $page  1-based page of ISP_LEAD_PRIVACY_BATCH leads.
 * @return int[]
 */
function isp_privacy_lead_ids( string $email, int $page = 1 ): array {
	$email = strtolower( trim( $email ) );
	if ( ! is_email( $email ) ) {
		return array();
	}
	return array_map(
		'intval',
		get_posts(
			array(
				'post_type'        => 'is_lead',
				'post_status'      => array( 'any', 'trash' ),
				'fields'           => 'ids',
				'posts_per_page'   => ISP_LEAD_PRIVACY_BATCH,
				'paged'            => max( 1, $page ),
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'no_found_rows'    => true,
				'suppress_filters' => true,
				'meta_key'         => 'isp_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin-only privacy request.
				'meta_value'       => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- admin-only privacy request.
			)
		)
	);
}

add_filter( 'wp_privacy_personal_data_exporters', 'isp_register_lead_exporter' );
/**
 * Add enquiries to Tools → Export Personal Data.
 *
 * @param array<string, array<string, mixed>> $exporters Registered exporters.
 * @return array<string, array<string, mixed>>
 */
function isp_register_lead_exporter( array $exporters ): array {
	$exporters['interior-specifics-leads'] = array(
		'exporter_friendly_name' => __( 'Interior Specifics enquiries', 'interior-specifics-core' ),
		'callback'               => 'isp_export_lead_data',
	);
	return $exporters;
}

/**
 * Exporter callback: every enquiry sent with this email address.
 *
 * @param string $email Email address.
 * @param int    $page  1-based page.
 * @return array{data: array<int, array<string, mixed>>, done: bool}
 */
function isp_export_lead_data( string $email, int $page = 1 ): array {
	$ids   = isp_privacy_lead_ids( $email, $page );
	$items = array();
	foreach ( $ids as $lead_id ) {
		$type   = (string) get_post_meta( $lead_id, 'isp_form_type', true );
		$form   = isp_form( $type );
		$fields = get_post_meta( $lead_id, 'isp_fields', true );
		$data   = array(
			array(
				'name'  => __( 'Form', 'interior-specifics-core' ),
				'value' => $form ? $form['label'] : $type,
			),
			array(
				'name'  => __( 'Submitted', 'interior-specifics-core' ),
				'value' => (string) get_post_time( 'Y-m-d H:i', false, $lead_id ),
			),
		);
		foreach ( is_array( $fields ) ? $fields : array() as $key => $value ) {
			$data[] = array(
				'name'  => (string) ( $form['fields'][ $key ]['label'] ?? $key ),
				'value' => (string) $value,
			);
		}
		$extra = array(
			'isp_source_url' => __( 'Source page', 'interior-specifics-core' ),
			'isp_user_agent' => __( 'Browser', 'interior-specifics-core' ),
		);
		foreach ( $extra as $meta_key => $label ) {
			$value = (string) get_post_meta( $lead_id, $meta_key, true );
			if ( '' !== $value ) {
				$data[] = array(
					'name'  => $label,
					'value' => $value,
				);
			}
		}
		$items[] = array(
			'group_id'          => 'interior-specifics-leads',
			'group_label'       => __( 'Enquiries', 'interior-specifics-core' ),
			'group_description' => __( 'Enquiries, consultation requests, guide requests and newsletter sign-ups sent through the website forms.', 'interior-specifics-core' ),
			'item_id'           => 'is-lead-' . $lead_id,
			'data'              => $data,
		);
	}
	return array(
		'data' => $items,
		'done' => count( $ids ) < ISP_LEAD_PRIVACY_BATCH,
	);
}

add_filter( 'wp_privacy_personal_data_erasers', 'isp_register_lead_eraser' );
/**
 * Add enquiries to Tools → Erase Personal Data.
 *
 * @param array<string, array<string, mixed>> $erasers Registered erasers.
 * @return array<string, array<string, mixed>>
 */
function isp_register_lead_eraser( array $erasers ): array {
	$erasers['interior-specifics-leads'] = array(
		'eraser_friendly_name' => __( 'Interior Specifics enquiries', 'interior-specifics-core' ),
		'callback'             => 'isp_erase_lead_data',
	);
	return $erasers;
}

/**
 * Eraser callback: permanently delete every enquiry sent with this email address. Deleted
 * leads drop out of the query, so each call takes the first batch again.
 *
 * @param string $email Email address.
 * @return array{items_removed: bool, items_retained: bool, messages: string[], done: bool}
 */
function isp_erase_lead_data( string $email ): array {
	$ids      = isp_privacy_lead_ids( $email );
	$removed  = false;
	$retained = false;
	foreach ( $ids as $lead_id ) {
		if ( wp_delete_post( $lead_id, true ) ) {
			$removed = true;
		} else {
			$retained = true;
		}
	}
	return array(
		'items_removed'  => $removed,
		'items_retained' => $retained,
		'messages'       => $retained ? array( __( 'Some enquiries could not be deleted.', 'interior-specifics-core' ) ) : array(),
		// Stop on a failure too, or the same undeletable batch would be fetched forever.
		'done'           => $retained || count( $ids ) < ISP_LEAD_PRIVACY_BATCH,
	);
}

/**
 * Give leads stored before the `isp_email` key existed that key, so the exporter and eraser
 * find them. Runs once from the admin (and can be run by hand with WP-CLI `wp eval`).
 *
 * @return int Number of leads updated.
 */
function isp_backfill_lead_emails(): int {
	$ids   = get_posts(
		array(
			'post_type'        => 'is_lead',
			'post_status'      => array( 'any', 'trash' ),
			'fields'           => 'ids',
			'posts_per_page'   => -1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
			'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one-off migration.
				array(
					'key'     => 'isp_email',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	$count = 0;
	foreach ( $ids as $lead_id ) {
		$fields = get_post_meta( (int) $lead_id, 'isp_fields', true );
		$email  = is_array( $fields ) ? strtolower( trim( (string) ( $fields['email'] ?? '' ) ) ) : '';
		if ( is_email( $email ) ) {
			update_post_meta( (int) $lead_id, 'isp_email', wp_slash( $email ) );
			++$count;
		}
	}
	update_option( 'isp_lead_email_index', 1, false );
	return $count;
}

add_action( 'admin_init', 'isp_maybe_backfill_lead_emails' );
/**
 * Run the one-off email backfill the first time an admin page loads after the update.
 */
function isp_maybe_backfill_lead_emails(): void {
	if ( ! get_option( 'isp_lead_email_index' ) ) {
		isp_backfill_lead_emails();
	}
}

/**
 * Retention period in months; 0 disables the clean-up.
 */
function isp_lead_retention_months(): int {
	/**
	 * Filter how many months leads are kept before the retention clean-up.
	 *
	 * @param int $months Default ISP_LEAD_RETENTION_MONTHS (24). Return 0 to disable.
	 */
	return max( 0, (int) apply_filters( 'isp_lead_retention_months', (int) ISP_LEAD_RETENTION_MONTHS ) );
}

add_action( 'init', 'isp_schedule_lead_retention' );
/**
 * Keep the daily clean-up event scheduled while retention is on, and remove it when it is off.
 */
function isp_schedule_lead_retention(): void {
	$next = wp_next_scheduled( 'isp_lead_retention' );
	if ( isp_lead_retention_months() > 0 ) {
		if ( ! $next ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'isp_lead_retention' );
		}
	} elseif ( $next ) {
		wp_clear_scheduled_hook( 'isp_lead_retention' );
	}
}

register_deactivation_hook( ISP_FILE, 'isp_unschedule_lead_retention' );
/**
 * Remove the clean-up event when the plugin is deactivated.
 */
function isp_unschedule_lead_retention(): void {
	wp_clear_scheduled_hook( 'isp_lead_retention' );
}

add_action( 'isp_lead_retention', 'isp_purge_old_leads' );
/**
 * Daily clean-up for leads older than the retention period: closed leads are deleted, and
 * every remaining lead loses its pseudonymous technical data (IP hash and user agent). Open
 * leads keep their enquiry so staff never lose a live conversation. Works in batches of 200.
 *
 * @return array{deleted: int, scrubbed: int}
 */
function isp_purge_old_leads(): array {
	$out    = array(
		'deleted'  => 0,
		'scrubbed' => 0,
	);
	$months = isp_lead_retention_months();
	if ( $months <= 0 ) {
		return $out;
	}
	$query = array(
		'post_type'        => 'is_lead',
		'post_status'      => array( 'any', 'trash' ),
		'fields'           => 'ids',
		'posts_per_page'   => 200,
		'no_found_rows'    => true,
		'suppress_filters' => true,
		'date_query'       => array(
			array(
				'column' => 'post_date_gmt',
				'before' => gmdate( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) ),
			),
		),
	);

	$closed = get_posts(
		$query + array(
			'meta_key'   => 'isp_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- daily cron.
			'meta_value' => 'closed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- daily cron.
		)
	);
	foreach ( $closed as $lead_id ) {
		if ( wp_delete_post( (int) $lead_id, true ) ) {
			++$out['deleted'];
		}
	}

	$tracked = get_posts(
		$query + array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- daily cron.
				'relation' => 'OR',
				array(
					'key'     => 'isp_ip_hash',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => 'isp_user_agent',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	foreach ( $tracked as $lead_id ) {
		delete_post_meta( (int) $lead_id, 'isp_ip_hash' );
		delete_post_meta( (int) $lead_id, 'isp_user_agent' );
		++$out['scrubbed'];
	}
	return $out;
}
