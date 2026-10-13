<?php
/**
 * Lead forms — the shared renderer behind every form on the site. Ports of
 * components/ContactForm.tsx, BookingForm.tsx, LeadMagnet.tsx, NewsletterForm.tsx and the form
 * card of components/home/ClosingCTA.tsx.
 *
 * Field definitions (order, types, placeholders, options) come from the Core plugin's
 * isp_form( $type ), which also validates submissions server-side, so the markup and the
 * validator can never drift apart.
 *
 * Each form is a real <form> posting to admin-post.php (action=isp_lead) and works without
 * JavaScript: the plugin redirects back with
 * ?is_status=success|invalid|throttled|expired|error&is_form={id}[&is_err={key}]#{id} and the
 * matching form renders its success or error state here — after a failure it is refilled with
 * what the visitor typed and the server's field messages (isp_form_feedback()). With JavaScript,
 * src/js/modules/forms.js submits the same fields as JSON to /wp-json/is/v1/lead and swaps the
 * states in place.
 *
 * Variants (visual skins, independent of the form type):
 *   light   — Contact / Booking page forms (white inputs, hairline borders, ivory success box)
 *   dark    — the ClosingCTA card (translucent inputs on onyx, gold "Thank you")
 *   section — the LeadMagnet section form (centred, max-w-xl)
 *   footer  — the footer newsletter signup
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class sets per variant, copied 1:1 from the Next.js components.
 *
 * @param string $variant light|dark|section|footer.
 * @return array<string, string>
 */
function ist_lead_form_skin( string $variant ): array {
	$skins = array(
		'light'   => array(
			'layout'        => 'grid',
			'form'          => 'space-y-4',
			'grid'          => 'grid gap-4 sm:grid-cols-2',
			'input'         => 'min-h-12 w-full border border-line bg-white px-4 py-3 text-sm text-charcoal transition-colors duration-200 placeholder:text-graysoft focus:border-gold focus:outline-none focus:ring-1 focus:ring-gold/40',
			'select'        => 'text-charcoal',
			'date'          => 'text-charcoal',
			'textarea'      => 'resize-y',
			'option'        => '',
			'consent'       => 'flex items-start gap-3 text-sm text-muted',
			'checkbox'      => 'mt-1 h-4 w-4 accent-gold',
			'error'         => 'text-sm text-burgundy',
			'field_error'   => 'mt-1.5 text-xs text-burgundy',
			'button'        => 'min-h-12 w-full cursor-pointer bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors duration-300 hover:bg-gold disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto',
			'fine'          => 'text-xs text-muted',
			'success'       => 'border-t-2 border-gold bg-ivory p-10 text-center',
			'success_tag'   => 'h3',
			'success_title' => 'text-2xl text-charcoal',
			'success_body'  => 'mt-3 text-muted',
		),
		'dark'    => array(
			'layout'        => 'stack',
			'form'          => 'flex flex-col gap-4',
			'grid'          => 'grid gap-4 sm:grid-cols-2',
			'input'         => 'min-h-12 w-full border border-line-light bg-white/[0.03] px-4 py-3 text-sm text-ivory placeholder:text-ivory/40 transition-colors focus:border-gold focus:outline-none',
			'select'        => 'cursor-pointer',
			'date'          => '[color-scheme:dark]',
			'textarea'      => 'resize-y',
			'option'        => 'text-charcoal',
			'consent'       => 'flex items-start gap-3 text-sm text-ivory/70',
			'checkbox'      => 'mt-1 h-4 w-4 accent-gold',
			'error'         => 'text-sm text-terracotta',
			'field_error'   => 'mt-1.5 text-xs text-terracotta',
			'button'        => 'mt-2 inline-flex min-h-12 cursor-pointer items-center justify-center bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors duration-300 hover:bg-gold disabled:cursor-not-allowed disabled:opacity-60',
			'fine'          => 'text-center text-xs text-ivory/45',
			'success'       => 'flex min-h-[320px] flex-col items-center justify-center text-center',
			'success_tag'   => 'span',
			'success_title' => 'gold-text font-serif text-5xl',
			'success_body'  => 'mt-4 max-w-sm text-ivory/80',
		),
		'section' => array(
			'layout'        => 'grid',
			'form'          => 'mx-auto mt-10 flex max-w-xl flex-col gap-4',
			'grid'          => 'grid gap-4 sm:grid-cols-2',
			'input'         => 'min-h-12 w-full border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none',
			'select'        => 'text-charcoal',
			'date'          => 'text-charcoal',
			'textarea'      => 'resize-y',
			'option'        => '',
			'consent'       => 'flex items-start gap-3 text-left text-sm text-muted',
			'checkbox'      => 'mt-1 h-4 w-4 accent-gold',
			'error'         => 'text-sm text-burgundy',
			'field_error'   => 'mt-1.5 text-left text-xs text-burgundy',
			'button'        => 'min-h-12 bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold disabled:opacity-60',
			'fine'          => 'text-xs text-muted',
			'success'       => 'mt-10',
			'success_tag'   => 'h3',
			'success_title' => 'text-2xl text-charcoal',
			'success_body'  => 'text-lg text-charcoal',
		),
		'footer'  => array(
			'layout'        => 'stack',
			'form'          => 'flex flex-col gap-3',
			'grid'          => 'grid gap-3',
			'input'         => 'min-h-12 w-full border border-ivory/20 bg-transparent px-4 py-3 text-sm text-ivory placeholder:text-ivory/40 focus:border-gold focus:outline-none',
			'select'        => '',
			'date'          => '[color-scheme:dark]',
			'textarea'      => 'resize-y',
			'option'        => 'text-charcoal',
			'consent'       => 'flex items-start gap-3 text-xs text-ivory/60',
			'checkbox'      => 'mt-0.5 h-4 w-4 accent-gold',
			'error'         => 'text-xs text-gold-soft',
			'field_error'   => 'mt-1.5 text-xs text-gold-soft',
			'button'        => 'min-h-12 bg-gold px-5 py-3 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold-soft disabled:opacity-60',
			'fine'          => 'text-xs text-ivory/50',
			'success'       => '',
			'success_tag'   => 'p',
			'success_title' => 'text-sm font-semibold text-ivory',
			'success_body'  => 'text-sm text-gold',
		),
	);
	return $skins[ $variant ] ?? $skins['light'];
}

/**
 * The visual variant each form type uses when none is requested.
 *
 * @param string $type Form type.
 */
function ist_lead_form_default_variant( string $type ): string {
	$map = array(
		'contact'     => 'light',
		'booking'     => 'light',
		'lead-magnet' => 'section',
		'newsletter'  => 'footer',
	);
	return $map[ $type ] ?? 'light';
}

/**
 * Default copy for a form type (and, for the lead magnet, its ClosingCTA "dark" variant).
 *
 * @param string $type    Form type.
 * @param string $variant Variant.
 * @return array{submit_label: string, loading_label: string, success_title: string, success_body: string, fine_print: string, label: string}
 */
function ist_lead_form_copy( string $type, string $variant ): array {
	$sending = __( 'Sending…', 'interior-specifics' );

	switch ( $type ) {
		case 'contact':
			$copy = array(
				'submit_label'  => __( 'Send Inquiry', 'interior-specifics' ),
				'loading_label' => $sending,
				'success_title' => __( 'Thank you', 'interior-specifics' ),
				'success_body'  => __( "We've received your inquiry and will respond within 24 hours.", 'interior-specifics' ),
				'fine_print'    => '',
				'label'         => __( 'Contact form', 'interior-specifics' ),
			);
			break;
		case 'booking':
			$copy = array(
				'submit_label'  => __( 'Request My Consultation', 'interior-specifics' ),
				'loading_label' => $sending,
				'success_title' => __( 'Request received', 'interior-specifics' ),
				'success_body'  => __( "Thank you — we'll confirm your consultation by email or phone within 24 hours.", 'interior-specifics' ),
				'fine_print'    => '',
				'label'         => __( 'Consultation request form', 'interior-specifics' ),
			);
			break;
		case 'newsletter':
			$copy = array(
				'submit_label'  => __( 'Subscribe', 'interior-specifics' ),
				'loading_label' => __( 'Subscribing…', 'interior-specifics' ),
				'success_title' => '',
				'success_body'  => __( "Thank you — you're subscribed.", 'interior-specifics' ),
				'fine_print'    => '',
				'label'         => __( 'Newsletter signup', 'interior-specifics' ),
			);
			break;
		default: // lead-magnet.
			if ( 'dark' === $variant ) {
				$copy = array(
					'submit_label'  => __( 'Send the Guide & Book Consultation', 'interior-specifics' ),
					'loading_label' => $sending,
					'success_title' => __( 'Thank you', 'interior-specifics' ),
					'success_body'  => __( "Check your inbox shortly for the guide — we'll personally follow up within 24 hours to arrange your consultation.", 'interior-specifics' ),
					'fine_print'    => __( 'No obligation. Your details are kept strictly private.', 'interior-specifics' ),
					'label'         => __( 'Guide and consultation request form', 'interior-specifics' ),
				);
			} else {
				$copy = array(
					'submit_label'  => __( 'Send Me the Guide', 'interior-specifics' ),
					'loading_label' => $sending,
					'success_title' => '',
					'success_body'  => __( "Thank you — check your inbox shortly. We'll be in touch within 24 hours.", 'interior-specifics' ),
					'fine_print'    => '',
					'label'         => __( 'Guide request form', 'interior-specifics' ),
				);
			}
	}
	return $copy;
}

/**
 * The error copy for a form type, with its fallback mailto / tel / WhatsApp links.
 *
 * @param string $type    Form type.
 * @param string $variant Variant.
 */
function ist_lead_form_error_html( string $type, string $variant ): string {
	$email    = ist_setting( 'email' );
	$new_tab  = '<span class="sr-only"> ' . esc_html__( '(opens in a new tab)', 'interior-specifics' ) . '</span>';
	$whatsapp = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer" class="underline" data-track="whatsapp">%s%s</a>',
		esc_url( ist_whatsapp_link() ),
		esc_html__( 'message us on WhatsApp', 'interior-specifics' ),
		$new_tab
	);
	$mail     = sprintf( '<a href="%s" class="underline" data-track="email">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
	$tel      = sprintf( '<a href="%s" class="underline" data-track="phone">%s</a>', esc_url( ist_phone_href(), array( 'tel' ) ), esc_html( ist_setting( 'phone_display' ) ) );

	switch ( $type ) {
		case 'contact':
			/* translators: 1: email link, 2: "message us on WhatsApp" link. */
			return sprintf( esc_html__( 'Sorry, something went wrong. Please email %1$s or %2$s.', 'interior-specifics' ), $mail, $whatsapp );
		case 'booking':
			/* translators: 1: phone link, 2: "message us on WhatsApp" link. */
			return sprintf( esc_html__( 'Sorry, something went wrong. Please call %1$s or %2$s.', 'interior-specifics' ), $tel, $whatsapp );
		case 'newsletter':
			return esc_html__( "Couldn't subscribe. Please try again later.", 'interior-specifics' );
		default:
			if ( 'dark' === $variant ) {
				/* translators: 1: "message us on WhatsApp" link, 2: email link. */
				return sprintf( esc_html__( 'Something went wrong. Please %1$s or email %2$s.', 'interior-specifics' ), $whatsapp, $mail );
			}
			/* translators: 1: "message us on WhatsApp" link, 2: email link. */
			return sprintf( esc_html__( 'Sorry, something went wrong. Please %1$s or email %2$s.', 'interior-specifics' ), $whatsapp, $mail );
	}
}

/**
 * The fallback contact links on their own ("You can also email … or message us on WhatsApp."),
 * shown after a rate-limit message so a throttled visitor still has a way through.
 *
 * @param string $type Form type.
 */
function ist_lead_form_fallback_html( string $type ): string {
	if ( 'newsletter' === $type ) {
		return '';
	}
	$email    = ist_setting( 'email' );
	$new_tab  = '<span class="sr-only"> ' . esc_html__( '(opens in a new tab)', 'interior-specifics' ) . '</span>';
	$whatsapp = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer" class="underline" data-track="whatsapp">%s%s</a>',
		esc_url( ist_whatsapp_link() ),
		esc_html__( 'message us on WhatsApp', 'interior-specifics' ),
		$new_tab
	);
	if ( 'booking' === $type ) {
		$tel = sprintf( '<a href="%s" class="underline" data-track="phone">%s</a>', esc_url( ist_phone_href(), array( 'tel' ) ), esc_html( ist_setting( 'phone_display' ) ) );
		/* translators: 1: phone link, 2: "message us on WhatsApp" link. */
		return sprintf( esc_html__( 'You can also call %1$s or %2$s.', 'interior-specifics' ), $tel, $whatsapp );
	}
	$mail = sprintf( '<a href="%s" class="underline" data-track="email">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
	/* translators: 1: email link, 2: "message us on WhatsApp" link. */
	return sprintf( esc_html__( 'You can also email %1$s or %2$s.', 'interior-specifics' ), $mail, $whatsapp );
}

/**
 * The no-JS round-trip state for a form: 'success', 'invalid' (field errors), 'throttled'
 * (rate limited), 'expired' (stale form token), 'error' or ''.
 *
 * The plugin redirects back to the page with ?is_status=…&is_form={id}; only the form whose id
 * matches shows the state, so the footer newsletter never mirrors a contact-form result.
 *
 * @param string $id Form id.
 */
function ist_lead_form_status( string $id ): string {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display of a redirect flag.
	if ( ! isset( $_GET['is_status'], $_GET['is_form'] ) ) {
		return '';
	}
	$status = sanitize_key( wp_unslash( $_GET['is_status'] ) );
	$form   = sanitize_html_class( wp_unslash( $_GET['is_form'] ) );
	// phpcs:enable
	if ( $form !== $id || ! in_array( $status, array( 'success', 'invalid', 'throttled', 'expired', 'error' ), true ) ) {
		return '';
	}
	return $status;
}

/**
 * What a failed no-JS submission left for the page it returned to: the visitor's cleaned
 * values, per-field messages and a summary message (all empty when nothing was kept).
 *
 * @return array{values: array<string, string>, fields: array<string, string>, error: string}
 */
function ist_lead_form_feedback(): array {
	$feedback = null;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- random lookup key for a short-lived transient.
	$key = isset( $_GET['is_err'] ) ? sanitize_key( wp_unslash( $_GET['is_err'] ) ) : '';
	if ( '' !== $key && function_exists( 'isp_form_feedback' ) ) {
		$feedback = isp_form_feedback( $key );
	}
	return $feedback ?? array(
		'values' => array(),
		'fields' => array(),
		'error'  => '',
	);
}

/**
 * The summary shown in place of the generic error copy for a specific no-JS failure.
 *
 * @param string $status  Round-trip status.
 * @param string $message Message the server sent back ('' when it has expired).
 */
function ist_lead_form_status_message( string $status, string $message ): string {
	if ( ! in_array( $status, array( 'invalid', 'throttled', 'expired' ), true ) ) {
		return '';
	}
	if ( '' !== $message ) {
		return $message;
	}
	$fallback = array(
		'invalid'   => __( 'Please check your details and try again.', 'interior-specifics' ),
		'throttled' => __( 'Too many submissions. Please try again in a few minutes.', 'interior-specifics' ),
		'expired'   => __( 'This page had been open for a while. Please submit the form again.', 'interior-specifics' ),
	);
	return $fallback[ $status ];
}

/**
 * The URL the plugin redirects back to after a no-JS submission (read via wp_get_referer()).
 *
 * @param string $id Form id.
 */
function ist_lead_form_return_url( string $id ): string {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	// Inside the block editor's server-side preview the request is a REST call; use the home page.
	if ( wp_is_serving_rest_request() || str_contains( $uri, '/wp-json/' ) ) {
		$uri = wp_make_link_relative( home_url( '/' ) );
	}
	$uri = remove_query_arg( array( 'is_status', 'is_form', 'is_err' ), $uri );
	return add_query_arg( 'is_form', rawurlencode( $id ), $uri );
}

/**
 * The "I agree to the privacy policy…" consent text, with the policy linked when one is set.
 */
function ist_lead_form_consent_html(): string {
	$policy = get_privacy_policy_url();
	$label  = esc_html__( 'privacy policy', 'interior-specifics' );
	if ( $policy ) {
		$label = sprintf( '<a href="%s" class="underline underline-offset-2 transition-colors hover:text-gold">%s</a>', esc_url( $policy ), $label );
	}
	/* translators: %s: "privacy policy" (linked to the privacy page). */
	return sprintf( esc_html__( 'I agree to the %s and consent to being contacted.', 'interior-specifics' ), $label );
}

/**
 * One field: visually hidden label, the control, and a slot for its server message (empty and
 * hidden unless a no-JS round trip flagged the field).
 *
 * @param string                $form_id Form id (field ids are prefixed with it).
 * @param string                $key     Field name.
 * @param array<string, mixed>  $field   Field schema from isp_form().
 * @param array<string, string> $skin    Variant classes.
 * @param string                $value   Value to refill after a failed no-JS submission.
 * @param string                $error   Server message for this field.
 */
function ist_lead_form_field( string $form_id, string $key, array $field, array $skin, string $value = '', string $error = '' ): string {
	$type        = (string) ( $field['type'] ?? 'text' );
	$id          = $form_id . '-' . sanitize_html_class( $key );
	$placeholder = (string) ( $field['placeholder'] ?? '' );
	$required    = ! empty( $field['required'] );
	$label       = trim( preg_replace( '/\s*\*\s*$/', '', $placeholder ) );
	$label       = '' !== $label ? $label : (string) ( $field['label'] ?? $key );
	$error_slot  = sprintf(
		'<p id="%1$s-error" class="%2$s" data-field-error="%3$s"%4$s>%5$s</p>',
		esc_attr( $id ),
		esc_attr( $skin['field_error'] ),
		esc_attr( $key ),
		'' === $error ? ' hidden' : '',
		esc_html( $error )
	);
	$req_attrs   = $required ? ' required aria-required="true"' : '';
	if ( '' !== $error ) {
		$req_attrs .= sprintf( ' aria-invalid="true" aria-describedby="%s-error"', esc_attr( $id ) );
	}

	if ( 'checkbox' === $type ) {
		return sprintf(
			'<div data-field="%1$s"><label class="%2$s"><input id="%3$s" type="checkbox" name="%1$s" value="on" class="%4$s"%5$s><span>%6$s</span></label>%7$s</div>',
			esc_attr( $key ),
			esc_attr( $skin['consent'] ),
			esc_attr( $id ),
			esc_attr( $skin['checkbox'] ),
			$req_attrs . ( '' !== $value ? ' checked' : '' ),
			ist_lead_form_consent_html(),
			$error_slot
		);
	}

	$label_html = sprintf( '<label for="%s" class="sr-only">%s</label>', esc_attr( $id ), esc_html( $label ) );

	if ( 'select' === $type ) {
		// Options are value => label maps from the plugin (plain lists still work).
		$choices      = function_exists( 'isp_field_options' ) ? isp_field_options( $field ) : array();
		$has_value    = '' !== $value && array_key_exists( $value, $choices );
		$option_class = $skin['option'] ? sprintf( ' class="%s"', esc_attr( $skin['option'] ) ) : '';
		$options      = sprintf( '<option value="" disabled%s%s>%s</option>', $has_value ? '' : ' selected', $option_class, esc_html( $placeholder ) );
		foreach ( $choices as $option_value => $option_label ) {
			$options .= sprintf(
				'<option%1$s value="%2$s"%3$s>%4$s</option>',
				$option_class,
				esc_attr( (string) $option_value ),
				$has_value ? selected( (string) $option_value, $value, false ) : '',
				esc_html( $option_label )
			);
		}
		$control = sprintf(
			'<select id="%1$s" name="%2$s" class="%3$s"%4$s>%5$s</select>',
			esc_attr( $id ),
			esc_attr( $key ),
			esc_attr( ist_classes( $skin['input'], $skin['select'] ) ),
			$req_attrs,
			$options
		);
	} elseif ( 'textarea' === $type ) {
		$control = sprintf(
			'<textarea id="%1$s" name="%2$s" rows="%3$d" placeholder="%4$s" class="%5$s"%6$s>%7$s</textarea>',
			esc_attr( $id ),
			esc_attr( $key ),
			max( 2, (int) ( $field['rows'] ?? 4 ) ),
			esc_attr( $placeholder ),
			esc_attr( ist_classes( $skin['input'], $skin['textarea'] ) ),
			$req_attrs,
			esc_textarea( $value )
		);
	} else {
		$input_type = in_array( $type, array( 'email', 'tel', 'date', 'number', 'url' ), true ) ? $type : 'text';
		$extra      = '';
		if ( ! empty( $field['autocomplete'] ) ) {
			$extra .= sprintf( ' autocomplete="%s"', esc_attr( (string) $field['autocomplete'] ) );
		}
		if ( 'date' === $input_type ) {
			// The validator rejects past dates, so the picker starts at today (site timezone).
			$extra .= sprintf( ' min="%s"', esc_attr( wp_date( 'Y-m-d' ) ) );
		} elseif ( '' !== $placeholder ) {
			$extra .= sprintf( ' placeholder="%s"', esc_attr( $placeholder ) );
		}
		if ( 'tel' === $input_type ) {
			// Mirrors the server check in isp_validate_lead(), so browsers catch it before a no-JS round trip.
			$extra .= sprintf( ' inputmode="tel" pattern="%s" title="%s"', esc_attr( '[0-9+\(\).\s\-]{6,25}' ), esc_attr__( 'Please enter a valid phone number.', 'interior-specifics' ) );
		}
		if ( '' !== $value ) {
			$extra .= sprintf( ' value="%s"', esc_attr( $value ) );
		}
		$control = sprintf(
			'<input id="%1$s" type="%2$s" name="%3$s" class="%4$s"%5$s%6$s>',
			esc_attr( $id ),
			esc_attr( $input_type ),
			esc_attr( $key ),
			esc_attr( ist_classes( $skin['input'], 'date' === $input_type ? $skin['date'] : '' ) ),
			$extra,
			$req_attrs
		);
	}

	return sprintf( '<div data-field="%s">%s%s%s</div>', esc_attr( $key ), $label_html, $control, $error_slot );
}

/**
 * Render a lead form with its success and error states.
 *
 * @param string               $type contact|booking|lead-magnet|newsletter.
 * @param array<string, mixed> $args {
 *     Optional.
 *
 *     @type string $variant       light|dark|section|footer (default depends on the type).
 *     @type string $id            Form id, also the no-JS return anchor (default "{type}-form").
 *     @type string $submit_label  Button label.
 *     @type string $success_title Success heading ('' for none).
 *     @type string $success_body  Success message.
 *     @type string $fine_print    Small print under the button ('' for none).
 *     @type string $class         Extra classes on the wrapper.
 * }
 * @return string Escaped HTML ('' when the Core plugin is inactive or the type is unknown).
 */
function ist_lead_form( string $type, array $args = array() ): string {
	static $used_ids = array();

	if ( ! function_exists( 'isp_form' ) ) {
		return '';
	}
	$form = isp_form( $type );
	if ( ! $form || empty( $form['fields'] ) ) {
		return '';
	}

	$variant = isset( $args['variant'] ) && in_array( $args['variant'], array( 'light', 'dark', 'section', 'footer' ), true )
		? (string) $args['variant']
		: ist_lead_form_default_variant( $type );
	$skin    = ist_lead_form_skin( $variant );
	$copy    = ist_lead_form_copy( $type, $variant );

	// Empty labels fall back to the defaults; an empty title or fine print means "none".
	foreach ( array( 'submit_label', 'success_body' ) as $key ) {
		if ( isset( $args[ $key ] ) && '' !== trim( (string) $args[ $key ] ) ) {
			$copy[ $key ] = (string) $args[ $key ];
		}
	}
	foreach ( array( 'success_title', 'fine_print' ) as $key ) {
		if ( isset( $args[ $key ] ) ) {
			$copy[ $key ] = trim( (string) $args[ $key ] );
		}
	}

	$id = sanitize_html_class( (string) ( $args['id'] ?? '' ) );
	$id = '' !== $id ? $id : $type . '-form';
	if ( isset( $used_ids[ $id ] ) ) {
		++$used_ids[ $id ];
		$id .= '-' . $used_ids[ $id ];
	} else {
		$used_ids[ $id ] = 1;
	}

	$status   = ist_lead_form_status( $id );
	$feedback = in_array( $status, array( '', 'success' ), true )
		? array(
			'values' => array(),
			'fields' => array(),
			'error'  => '',
		)
		: ist_lead_form_feedback();
	$fields   = $form['fields'];

	// Split fields: grid/stack body, then full-width textareas, then the consent checkbox.
	$body      = array();
	$textareas = array();
	$checks    = array();
	foreach ( $fields as $key => $field ) {
		$html = ist_lead_form_field(
			$id,
			(string) $key,
			(array) $field,
			$skin,
			(string) ( $feedback['values'][ $key ] ?? '' ),
			'invalid' === $status ? (string) ( $feedback['fields'][ $key ] ?? '' ) : ''
		);
		if ( 'checkbox' === ( $field['type'] ?? '' ) ) {
			$checks[] = $html;
		} elseif ( 'textarea' === ( $field['type'] ?? '' ) && 'grid' === $skin['layout'] ) {
			$textareas[] = $html;
		} else {
			$body[ (string) $key ] = $html;
		}
	}

	if ( 'grid' === $skin['layout'] ) {
		$fields_html = sprintf( '<div class="%s">%s</div>', esc_attr( $skin['grid'] ), implode( '', $body ) );
	} else {
		// Stack: one field per row, except email + phone which pair up (the ClosingCTA layout).
		$fields_html = '';
		$keys        = array_keys( $body );
		$count       = count( $keys );
		for ( $i = 0; $i < $count; $i++ ) {
			$key  = $keys[ $i ];
			$next = $keys[ $i + 1 ] ?? '';
			if ( 'dark' === $variant && 'email' === $key && 'phone' === $next ) {
				$fields_html .= sprintf( '<div class="%s">%s%s</div>', esc_attr( $skin['grid'] ), $body[ $key ], $body[ $next ] );
				++$i;
				continue;
			}
			$fields_html .= $body[ $key ];
		}
	}
	$fields_html .= implode( '', $textareas ) . implode( '', $checks );

	// Two slots: a specific message (throttled, expired, invalid — also filled by forms.js) or
	// the type's generic copy with its fallback links. Only one shows at a time; a throttled
	// message is followed by the fallback links on their own.
	$status_message = ist_lead_form_status_message( $status, $feedback['error'] );
	$fallback_html  = ist_lead_form_fallback_html( $type );
	$error_html     = sprintf(
		'<p class="%1$s" role="alert" data-lead-error%2$s><span data-lead-error-msg%3$s>%4$s</span><span data-lead-error-default%5$s>%6$s</span>%7$s</p>',
		esc_attr( $skin['error'] ),
		in_array( $status, array( '', 'success' ), true ) ? ' hidden' : '',
		'' === $status_message ? ' hidden' : '',
		esc_html( $status_message ),
		'' === $status_message ? '' : ' hidden',
		ist_lead_form_error_html( $type, $variant ),
		'' === $fallback_html ? '' : sprintf( '<span data-lead-error-fallback%1$s> %2$s</span>', 'throttled' === $status ? '' : ' hidden', $fallback_html )
	);
	$button         = sprintf(
		'<button type="submit" class="%1$s" data-loading-label="%2$s">%3$s</button>',
		esc_attr( $skin['button'] ),
		esc_attr( $copy['loading_label'] ),
		esc_html( $copy['submit_label'] )
	);
	$fine           = '' !== $copy['fine_print'] ? sprintf( '<p class="%s">%s</p>', esc_attr( $skin['fine'] ), esc_html( $copy['fine_print'] ) ) : '';

	$hidden = sprintf(
		'<input type="hidden" name="action" value="isp_lead"><input type="hidden" name="formType" value="%1$s"><input type="hidden" name="token" value="%2$s"><input type="hidden" name="anchor" value="%3$s"><input type="hidden" name="_wp_http_referer" value="%4$s"><input type="text" name="company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden">',
		esc_attr( $type ),
		esc_attr( function_exists( 'isp_form_token' ) ? isp_form_token() : '' ),
		esc_attr( $id ),
		esc_attr( ist_lead_form_return_url( $id ) )
	);

	// The newsletter error sits under its button; everywhere else it sits above.
	$actions = 'footer' === $variant ? $button . $error_html . $fine : $error_html . $button . $fine;

	$form_html = sprintf(
		'<form%1$s method="post" action="%2$s" class="%3$s" aria-label="%4$s" data-lead-form-el%5$s>%6$s%7$s%8$s</form>',
		'success' === $status ? '' : sprintf( ' id="%s"', esc_attr( $id ) ),
		esc_url( admin_url( 'admin-post.php' ) ),
		esc_attr( $skin['form'] ),
		esc_attr( $copy['label'] ),
		'success' === $status ? ' hidden' : '',
		$hidden,
		$fields_html,
		$actions
	);

	// Success state: present in the markup so JS can reveal it; visible now after a no-JS success.
	$title_html = '';
	if ( '' !== $copy['success_title'] ) {
		$title_html = sprintf( '<%1$s class="%2$s">%3$s</%1$s>', $skin['success_tag'], esc_attr( $skin['success_title'] ), esc_html( $copy['success_title'] ) );
	}
	$body_class = $skin['success_body'];
	if ( 'section' === $variant && '' !== $title_html ) {
		$body_class = ist_classes( $body_class, 'mt-3' );
	} elseif ( 'footer' === $variant && '' !== $title_html ) {
		$body_class = ist_classes( $body_class, 'mt-1' );
	}
	$success_body = sprintf( '<p class="%s">%s</p>', esc_attr( $body_class ), esc_html( $copy['success_body'] ) );
	if ( 'footer' === $variant && '' === $title_html ) {
		// NewsletterForm renders the bare paragraph.
		$success_html = sprintf(
			'<p%1$s class="%2$s" tabindex="-1" data-lead-success%3$s>%4$s</p>',
			'success' === $status ? sprintf( ' id="%s"', esc_attr( $id ) ) : '',
			esc_attr( $skin['success_body'] ),
			'success' === $status ? '' : ' hidden',
			esc_html( $copy['success_body'] )
		);
	} else {
		$success_html = sprintf(
			// The space keeps the title and body apart in the live-region announcement.
			'<div%1$s class="%2$s" tabindex="-1" data-lead-success%3$s>%4$s %5$s</div>',
			'success' === $status ? sprintf( ' id="%s"', esc_attr( $id ) ) : '',
			esc_attr( ist_classes( $skin['success'], 'focus:outline-none' ) ),
			'success' === $status ? '' : ' hidden',
			$title_html,
			$success_body
		);
	}

	return sprintf(
		'<div class="%1$s" data-lead-form data-form-type="%2$s" data-variant="%3$s">%4$s%5$s<p class="sr-only" aria-live="polite" data-lead-live></p></div>',
		esc_attr( ist_classes( 'ist-lead-form', $args['class'] ?? '' ) ),
		esc_attr( $type ),
		esc_attr( $variant ),
		$form_html,
		$success_html
	);
}
