<?php
/**
 * Lead form definitions — the single source for both rendering (theme lead-form block)
 * and server-side validation (leads.php). Field names match the Next.js forms.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Longest a rendered form stays valid. Older tokens are refused with code `token_expired`
 * (the JS form retries once with a fresh token; the no-JS form re-renders with one), so a
 * harvested token cannot be replayed forever. Keep any full-page cache TTL below this.
 */
const ISP_FORM_TOKEN_MAX_AGE = DAY_IN_SECONDS;

/**
 * How long a failed no-JS submission's values and messages are kept for the redirect back.
 */
const ISP_FORM_FEEDBACK_TTL = 10 * MINUTE_IN_SECONDS;

/**
 * Shared select option lists: stable value => translated label. The value is what the form
 * posts and the validator checks; the label is shown in the form and stored on the lead.
 *
 * @return array<string, array<string, string>>
 */
function isp_form_options(): array {
	return array(
		'propertyType'     => array(
			'residential' => __( 'Residential', 'interior-specifics-core' ),
			'commercial'  => __( 'Commercial', 'interior-specifics-core' ),
			'hospitality' => __( 'Hospitality', 'interior-specifics-core' ),
			'other'       => __( 'Other', 'interior-specifics-core' ),
		),
		'budget'           => array(
			'10m-50m'  => __( '₦10M – ₦50M', 'interior-specifics-core' ),
			'50m-100m' => __( '₦50M – ₦100M', 'interior-specifics-core' ),
			'100m-up'  => __( '₦100M+', 'interior-specifics-core' ),
		),
		'timeline'         => array(
			'asap'  => __( 'ASAP', 'interior-specifics-core' ),
			'1-3m'  => __( '1–3 months', 'interior-specifics-core' ),
			'3-6m'  => __( '3–6 months', 'interior-specifics-core' ),
			'6m-up' => __( '6+ months', 'interior-specifics-core' ),
		),
		'source'           => array(
			'instagram' => __( 'Instagram', 'interior-specifics-core' ),
			'referral'  => __( 'Referral', 'interior-specifics-core' ),
			'google'    => __( 'Google', 'interior-specifics-core' ),
			'other'     => __( 'Other', 'interior-specifics-core' ),
		),
		'consultationType' => array(
			'in-person' => __( 'In-person (Lagos showroom)', 'interior-specifics-core' ),
			'virtual'   => __( 'Virtual (video call)', 'interior-specifics-core' ),
			'phone'     => __( 'Phone call', 'interior-specifics-core' ),
		),
		'preferredTime'    => array(
			'morning'   => __( 'Morning (9am–12pm)', 'interior-specifics-core' ),
			'afternoon' => __( 'Afternoon (12pm–4pm)', 'interior-specifics-core' ),
			'evening'   => __( 'Evening (4pm–6pm)', 'interior-specifics-core' ),
		),
	);
}

/**
 * A select field's options as value => label. Accepts the keyed maps above and, for
 * `isp_forms` filters that add plain lists, uses each list entry as both value and label.
 *
 * @param array<string, mixed> $field Field schema.
 * @return array<string, string>
 */
function isp_field_options( array $field ): array {
	$options = (array) ( $field['options'] ?? array() );
	$out     = array();
	foreach ( $options as $value => $label ) {
		$value         = array_is_list( $options ) ? (string) $label : (string) $value;
		$out[ $value ] = (string) $label;
	}
	return $out;
}

/**
 * Form schemas. Each field: type (text|email|tel|select|date|textarea|checkbox), label
 * (used in emails and the admin), placeholder, required, optional `options` key and `rows`.
 *
 * @return array<string, array{label: string, fields: array<string, array<string, mixed>>}>
 */
function isp_forms(): array {
	$opts = isp_form_options();

	$name    = array(
		'type'         => 'text',
		'label'        => __( 'Name', 'interior-specifics-core' ),
		'required'     => true,
		'autocomplete' => 'name',
	);
	$email   = array(
		'type'         => 'email',
		'label'        => __( 'Email', 'interior-specifics-core' ),
		'required'     => true,
		'autocomplete' => 'email',
	);
	$phone   = array(
		'type'         => 'tel',
		'label'        => __( 'Phone', 'interior-specifics-core' ),
		'required'     => true,
		'autocomplete' => 'tel',
	);
	$consent = array(
		'type'     => 'checkbox',
		'label'    => __( 'Consent', 'interior-specifics-core' ),
		'required' => true,
	);

	$forms = array(
		'contact'     => array(
			'label'  => __( 'Contact Inquiry', 'interior-specifics-core' ),
			'fields' => array(
				'name'         => $name + array( 'placeholder' => __( 'Full name *', 'interior-specifics-core' ) ),
				'email'        => $email + array( 'placeholder' => __( 'Email *', 'interior-specifics-core' ) ),
				'phone'        => $phone + array( 'placeholder' => __( 'Phone *', 'interior-specifics-core' ) ),
				'location'     => array(
					'type'        => 'text',
					'label'       => __( 'Project location', 'interior-specifics-core' ),
					'placeholder' => __( 'Project location', 'interior-specifics-core' ),
					'required'    => false,
				),
				'propertyType' => array(
					'type'        => 'select',
					'label'       => __( 'Property type', 'interior-specifics-core' ),
					'placeholder' => __( 'Property type *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['propertyType'],
				),
				'budget'       => array(
					'type'        => 'select',
					'label'       => __( 'Budget', 'interior-specifics-core' ),
					'placeholder' => __( 'Estimated budget *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['budget'],
				),
				'timeline'     => array(
					'type'        => 'select',
					'label'       => __( 'Timeline', 'interior-specifics-core' ),
					'placeholder' => __( 'Project timeline *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['timeline'],
				),
				'source'       => array(
					'type'        => 'select',
					'label'       => __( 'Heard about us via', 'interior-specifics-core' ),
					'placeholder' => __( 'How did you hear about us?', 'interior-specifics-core' ),
					'required'    => false,
					'options'     => $opts['source'],
				),
				'message'      => array(
					'type'        => 'textarea',
					'label'       => __( 'Message', 'interior-specifics-core' ),
					'placeholder' => __( 'Tell us about your project *', 'interior-specifics-core' ),
					'required'    => true,
					'rows'        => 5,
				),
				'consent'      => $consent,
			),
		),
		'booking'     => array(
			'label'  => __( 'Consultation Request', 'interior-specifics-core' ),
			'fields' => array(
				'name'             => $name + array( 'placeholder' => __( 'Full name *', 'interior-specifics-core' ) ),
				'email'            => $email + array( 'placeholder' => __( 'Email *', 'interior-specifics-core' ) ),
				'phone'            => $phone + array( 'placeholder' => __( 'Phone *', 'interior-specifics-core' ) ),
				'consultationType' => array(
					'type'        => 'select',
					'label'       => __( 'Consultation type', 'interior-specifics-core' ),
					'placeholder' => __( 'Consultation type *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['consultationType'],
				),
				'preferredDate'    => array(
					'type'        => 'date',
					'label'       => __( 'Preferred date', 'interior-specifics-core' ),
					'placeholder' => __( 'Preferred date *', 'interior-specifics-core' ),
					'required'    => true,
				),
				'preferredTime'    => array(
					'type'        => 'select',
					'label'       => __( 'Preferred time', 'interior-specifics-core' ),
					'placeholder' => __( 'Preferred time *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['preferredTime'],
				),
				'propertyType'     => array(
					'type'        => 'select',
					'label'       => __( 'Property type', 'interior-specifics-core' ),
					'placeholder' => __( 'Property type *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['propertyType'],
				),
				'budget'           => array(
					'type'        => 'select',
					'label'       => __( 'Budget', 'interior-specifics-core' ),
					'placeholder' => __( 'Estimated budget *', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['budget'],
				),
				'message'          => array(
					'type'        => 'textarea',
					'label'       => __( 'Message', 'interior-specifics-core' ),
					'placeholder' => __( 'Tell us a little about your project (optional)', 'interior-specifics-core' ),
					'required'    => false,
					'rows'        => 4,
				),
				'consent'          => $consent,
			),
		),
		'lead-magnet' => array(
			'label'  => __( 'Guide Download', 'interior-specifics-core' ),
			'fields' => array(
				'name'         => $name + array( 'placeholder' => __( 'Full name', 'interior-specifics-core' ) ),
				'email'        => $email + array( 'placeholder' => __( 'Email address', 'interior-specifics-core' ) ),
				'phone'        => $phone + array( 'placeholder' => __( 'Phone number', 'interior-specifics-core' ) ),
				'propertyType' => array(
					'type'        => 'select',
					'label'       => __( 'Property type', 'interior-specifics-core' ),
					'placeholder' => __( 'Property type', 'interior-specifics-core' ),
					'required'    => true,
					'options'     => $opts['propertyType'],
				),
			),
		),
		'newsletter'  => array(
			'label'  => __( 'Newsletter Signup', 'interior-specifics-core' ),
			'fields' => array(
				'email' => $email + array( 'placeholder' => __( 'Your email', 'interior-specifics-core' ) ),
			),
		),
	);

	/**
	 * Filter lead form definitions (add fields, change options).
	 *
	 * @param array $forms Form schemas keyed by form type.
	 */
	return apply_filters( 'isp_forms', $forms );
}

/**
 * Schema for one form type, or null if unknown.
 *
 * @param string $type Form type.
 * @return array{label: string, fields: array<string, array<string, mixed>>}|null
 */
function isp_form( string $type ): ?array {
	return isp_forms()[ $type ] ?? null;
}

/**
 * Signed render-time token. Submissions faster than the minimum fill time are treated as bots;
 * tokens older than isp_form_token_max_age() are refused as expired.
 *
 * @param int|null $issued Issue timestamp (default now). The lead endpoint back-dates the
 *                         replacement token it hands out for an expired one, so the retry
 *                         is not mistaken for a too-fast bot.
 */
function isp_form_token( ?int $issued = null ): string {
	$ts = (string) ( $issued ?? time() );
	return $ts . '.' . substr( wp_hash( 'isp_form|' . $ts ), 0, 16 );
}

/**
 * Seconds since a token was issued, or null if the token is missing or forged.
 *
 * @param string $token Token from the form.
 */
function isp_form_token_age( string $token ): ?int {
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return null;
	}
	if ( ! hash_equals( substr( wp_hash( 'isp_form|' . $parts[0] ), 0, 16 ), $parts[1] ) ) {
		return null;
	}
	return time() - (int) $parts[0];
}

/**
 * Seconds a rendered form's token stays valid.
 */
function isp_form_token_max_age(): int {
	/**
	 * Filter how long a form token stays valid, in seconds.
	 *
	 * @param int $max_age Default ISP_FORM_TOKEN_MAX_AGE (one day).
	 */
	return max( HOUR_IN_SECONDS, (int) apply_filters( 'isp_form_token_max_age', ISP_FORM_TOKEN_MAX_AGE ) );
}

/**
 * Keep a failed no-JS submission (sanitised values, field messages, summary) for the page
 * the visitor is sent back to, so it can refill the form and show what went wrong.
 *
 * @param array{values: array<string, string>, fields: array<string, string>, error: string} $feedback Feedback.
 * @return string Random key for the redirect URL.
 */
function isp_form_feedback_store( array $feedback ): string {
	$key = bin2hex( random_bytes( 12 ) );
	set_transient( 'isp_fb_' . $key, $feedback, ISP_FORM_FEEDBACK_TTL );
	return $key;
}

/**
 * Read stored no-JS feedback (see isp_form_feedback_store()).
 *
 * @param string $key Key from the redirect URL.
 * @return array{values: array<string, string>, fields: array<string, string>, error: string}|null
 */
function isp_form_feedback( string $key ): ?array {
	if ( ! preg_match( '/^[a-f0-9]{24}$/', $key ) ) {
		return null;
	}
	$feedback = get_transient( 'isp_fb_' . $key );
	if ( ! is_array( $feedback ) ) {
		return null;
	}
	return array(
		'values' => array_map( 'strval', (array) ( $feedback['values'] ?? array() ) ),
		'fields' => array_map( 'strval', (array) ( $feedback['fields'] ?? array() ) ),
		'error'  => (string) ( $feedback['error'] ?? '' ),
	);
}
