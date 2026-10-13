<?php
/**
 * Mail transport.
 *
 * - Production: when the RESEND_API_KEY constant is defined (wp-config.php), every wp_mail()
 *   call is delivered through the Resend HTTP API — no SMTP plugin needed.
 * - Local: when IS_SMTP_HOST is defined (docker-compose sets it), PHPMailer talks to Mailpit.
 * - Otherwise WordPress' default mailer is used untouched.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'wp_mail_from', 'isp_mail_from' );
/**
 * Use the configured sender for all site mail.
 *
 * @param string $from Default from address.
 */
function isp_mail_from( string $from ): string {
	$configured = isp_setting( 'lead_from_email' );
	if ( is_email( $configured ) ) {
		return $configured;
	}
	// WordPress defaults to wordpress@{host}; on hosts without a dotted domain (localhost,
	// internal names) PHPMailer rejects that address, so use the public email instead.
	$domain = substr( (string) strrchr( $from, '@' ), 1 );
	return ( is_email( $from ) && str_contains( $domain, '.' ) ) ? $from : isp_setting( 'email' );
}

add_filter( 'wp_mail_from_name', 'isp_mail_from_name' );
/**
 * Use the configured sender name for all site mail.
 *
 * @param string $name Default from name.
 */
function isp_mail_from_name( string $name ): string {
	$configured = isp_setting( 'lead_from_name' );
	return $configured ? $configured : $name;
}

add_action( 'phpmailer_init', 'isp_local_smtp' );
/**
 * Route mail to Mailpit in local development.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer.
 */
function isp_local_smtp( $phpmailer ): void {
	if ( ! defined( 'IS_SMTP_HOST' ) || ! IS_SMTP_HOST ) {
		return;
	}
	$phpmailer->isSMTP();
	$phpmailer->Host        = IS_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$phpmailer->Port        = defined( 'IS_SMTP_PORT' ) ? (int) IS_SMTP_PORT : 1025; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$phpmailer->SMTPAuth    = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$phpmailer->SMTPAutoTLS = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}

/**
 * Whether the Resend transport is configured.
 */
function isp_resend_enabled(): bool {
	return defined( 'RESEND_API_KEY' ) && is_string( RESEND_API_KEY ) && '' !== RESEND_API_KEY;
}

add_filter( 'pre_wp_mail', 'isp_resend_send', 10, 2 );
/**
 * Short-circuit wp_mail() and deliver through Resend.
 *
 * @param null|bool            $return Short-circuit value from earlier filters.
 * @param array<string, mixed> $atts   wp_mail() arguments: to, subject, message, headers, attachments.
 * @return null|bool Null to fall through to PHPMailer, bool when handled.
 */
function isp_resend_send( $return, array $atts ) {
	if ( null !== $return || ! isp_resend_enabled() ) {
		return $return;
	}

	$headers = isp_parse_mail_headers( $atts['headers'] ?? array() );
	$to      = is_array( $atts['to'] ) ? $atts['to'] : explode( ',', (string) $atts['to'] );
	$to      = array_values( array_filter( array_map( 'trim', $to ) ) );

	$from_email = apply_filters( 'wp_mail_from', $headers['from_email'] ?? 'wordpress@' . preg_replace( '/^www\./', '', (string) wp_parse_url( network_home_url(), PHP_URL_HOST ) ) );
	$from_name  = apply_filters( 'wp_mail_from_name', $headers['from_name'] ?? 'WordPress' );

	// Same order as core wp_mail(): the header sets type and charset, then the filters decide.
	$content_type = $headers['content_type'] ?? 'text/plain';
	$charset      = '';
	if ( preg_match( '/;\s*charset\s*=\s*"?([\w.:-]+)"?/i', $content_type, $m ) ) {
		$charset = $m[1];
	}
	$content_type = (string) apply_filters( 'wp_mail_content_type', trim( explode( ';', $content_type )[0] ) );
	$charset      = (string) apply_filters( 'wp_mail_charset', '' !== $charset ? $charset : get_bloginfo( 'charset' ) );

	$subject = isp_mail_to_utf8( (string) $atts['subject'], $charset );
	$message = isp_mail_to_utf8( (string) $atts['message'], $charset );

	$payload = array(
		'from'    => isp_format_address( (string) $from_email, (string) $from_name ),
		'to'      => $to,
		'subject' => $subject,
	);
	if ( str_contains( strtolower( $content_type ), 'text/html' ) ) {
		$payload['html'] = $message;
	} else {
		$payload['text'] = $message;
	}
	foreach ( array( 'reply_to', 'cc', 'bcc' ) as $field ) {
		if ( ! empty( $headers[ $field ] ) ) {
			$payload[ $field ] = $headers[ $field ];
		}
	}
	foreach ( (array) ( $atts['attachments'] ?? array() ) as $path ) {
		if ( is_string( $path ) && is_readable( $path ) ) {
			$payload['attachments'][] = array(
				'filename' => basename( $path ),
				'content'  => base64_encode( (string) file_get_contents( $path ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- local attachment, Resend expects base64.
			);
		}
	}

	$response = wp_remote_post(
		'https://api.resend.com/emails',
		array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . RESEND_API_KEY,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
		)
	);

	$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	if ( $code >= 200 && $code < 300 ) {
		do_action( 'wp_mail_succeeded', $atts );
		return true;
	}

	$message = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response );
	do_action( 'wp_mail_failed', new WP_Error( 'isp_resend_failed', 'Resend: ' . $message, $atts ) );
	return false;
}

/**
 * Pull the headers Resend needs out of wp_mail()'s string|array headers.
 *
 * @param string|string[] $headers Raw headers.
 * @return array<string, mixed>
 */
function isp_parse_mail_headers( $headers ): array {
	$lines = is_array( $headers ) ? $headers : preg_split( "/\r\n|\n/", (string) $headers );
	$out   = array();
	foreach ( (array) $lines as $line ) {
		if ( ! str_contains( (string) $line, ':' ) ) {
			continue;
		}
		[ $name, $value ] = array_map( 'trim', explode( ':', (string) $line, 2 ) );
		switch ( strtolower( $name ) ) {
			case 'from':
				if ( preg_match( '/^(.*)<(.+)>$/', $value, $m ) ) {
					$out['from_name']  = trim( $m[1], " \"'" );
					$out['from_email'] = trim( $m[2] );
				} else {
					$out['from_email'] = $value;
				}
				break;
			case 'content-type':
				$out['content_type'] = strtolower( $value );
				break;
			case 'reply-to':
				// Re-format each address so the display name is quoted correctly for Resend.
				// Split on commas outside quoted names ("Doe, Jane" <j@example.com>).
				foreach ( (array) preg_split( '/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $value ) as $address ) {
					$address = trim( $address );
					if ( preg_match( '/^(.*)<(.+)>$/', $address, $m ) ) {
						$out['reply_to'][] = isp_format_address( trim( $m[2] ), $m[1] );
					} elseif ( '' !== $address ) {
						$out['reply_to'][] = $address;
					}
				}
				break;
			case 'cc':
			case 'bcc':
				$out[ strtolower( $name ) ] = array_map( 'trim', explode( ',', $value ) );
				break;
		}
	}
	return $out;
}

/**
 * A safe display name for an address header built from visitor input: letters, digits,
 * spaces, apostrophes, hyphens and full stops only, at most 64 characters. Commas, angle
 * brackets, quotes and backslashes would otherwise split or corrupt the header.
 *
 * @param string $name Raw name.
 */
function isp_mail_display_name( string $name ): string {
	$name = (string) preg_replace( '/[^\p{L}\p{M}\p{N} .\'-]+/u', ' ', $name );
	return mb_substr( trim( (string) preg_replace( '/\s+/', ' ', $name ) ), 0, 64 );
}

/**
 * "Name <email>" with the name quoted (RFC 5322) when it holds anything beyond plain atext.
 *
 * @param string $email Address.
 * @param string $name  Display name ('' for the bare address).
 */
function isp_format_address( string $email, string $name = '' ): string {
	$name = trim( str_replace( array( "\r", "\n" ), '', $name ), " \t\"" );
	if ( '' === $name ) {
		return $email;
	}
	if ( preg_match( '/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $name ) ) {
		$name = '"' . addcslashes( $name, '"\\' ) . '"';
	}
	return sprintf( '%s <%s>', $name, $email );
}

/**
 * Convert text in the wp_mail_charset charset to UTF-8 for the JSON API. Unknown charsets
 * are passed through unchanged.
 *
 * @param string $text    Text.
 * @param string $charset Source charset.
 */
function isp_mail_to_utf8( string $text, string $charset ): string {
	if ( '' === $charset || 'UTF-8' === strtoupper( $charset ) || 'UTF8' === strtoupper( $charset ) || ! function_exists( 'mb_convert_encoding' ) ) {
		return $text;
	}
	try {
		$converted = mb_convert_encoding( $text, 'UTF-8', $charset );
	} catch ( ValueError ) {
		return $text;
	}
	return is_string( $converted ) ? $converted : $text;
}
