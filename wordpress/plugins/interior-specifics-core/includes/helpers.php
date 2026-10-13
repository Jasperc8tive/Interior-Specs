<?php
/**
 * Settings access and small shared helpers. The theme reads everything through these.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default site settings — mirrors the Next.js lib/site.ts so a fresh install looks complete.
 *
 * @return array<string, string>
 */
function isp_default_settings(): array {
	return array(
		'name'              => 'Interior Specifics',
		'legal_name'        => 'Interior Specifics Ltd.',
		'tagline'           => 'Designing Spaces Worthy of Achievement',
		'secondary_tagline' => 'The Intersection of Success and Sanctuary',
		'description'       => "Bespoke luxury interior design for Nigeria's most discerning homeowners and businesses. Concept to completion, under one roof.",
		'founded_year'      => '2009',
		'email'             => 'info@interiorspecifics.com',
		'phone_display'     => '+234 801 234 5678',
		'phone_href'        => '+2348012345678',
		'whatsapp'          => '2348012345678',
		'whatsapp_message'  => "Hello Interior Specifics, I'd like to discuss a project.",
		'address_street'    => 'Plot 697 Amodu Tijani Close, off Sanusi Fafunwa Street',
		'address_city'      => 'Victoria Island, Lagos',
		'address_country'   => 'Nigeria',
		'hours'             => 'Mon–Fri: 9am–6pm · Sat: 10am–4pm',
		'booking_url'       => '/book/',
		'header_cta_label'  => 'Book Consultation',
		'calendly_url'      => '',
		'map_embed_url'     => '',
		'ga4_id'            => '',
		'social_instagram'  => 'https://instagram.com/',
		'social_linkedin'   => 'https://linkedin.com/',
		'social_pinterest'  => 'https://pinterest.com/',
		'social_youtube'    => 'https://youtube.com/',
		'stat_1_value'      => '15+',
		'stat_1_label'      => 'Years',
		'stat_2_value'      => '200+',
		'stat_2_label'      => 'Projects',
		'stat_3_value'      => 'Lagos',
		'stat_3_label'      => 'Premier Design Firm',
		'lead_to_email'     => '',
		'lead_from_email'   => '',
		'lead_from_name'    => 'Interior Specifics',
		'guide_url'         => '',
		'hero_services'     => '',
		'hero_showroom'     => '',
		'hero_journal'      => '',
		'hero_default'      => '',
	);
}

/**
 * Keys whose empty value is meaningful (an integration that is switched off),
 * so they must not fall back to a default.
 *
 * @return string[]
 */
function isp_optional_setting_keys(): array {
	return array( 'calendly_url', 'map_embed_url', 'ga4_id', 'lead_to_email', 'lead_from_email', 'guide_url', 'social_instagram', 'social_linkedin', 'social_pinterest', 'social_youtube' );
}

/**
 * All settings merged over defaults.
 *
 * @return array<string, string>
 */
function isp_settings(): array {
	$saved = get_option( 'isp_settings', array() );
	return array_merge( isp_default_settings(), is_array( $saved ) ? $saved : array() );
}

/**
 * One setting. Required values fall back to their default so the site never renders blanks.
 *
 * @param string $key Setting key.
 */
function isp_setting( string $key ): string {
	$value = (string) ( isp_settings()[ $key ] ?? '' );
	if ( '' === $value && ! in_array( $key, isp_optional_setting_keys(), true ) ) {
		$value = (string) ( isp_default_settings()[ $key ] ?? '' );
	}
	return $value;
}

/**
 * Where lead emails go: the configured recipient, else the public email.
 */
function isp_lead_recipient(): string {
	$to = isp_setting( 'lead_to_email' );
	return is_email( $to ) ? $to : isp_setting( 'email' );
}

/**
 * The three headline stats (hero, stats band).
 *
 * @return array<int, array{value: string, label: string}>
 */
function isp_stats(): array {
	$stats = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$stats[] = array(
			'value' => isp_setting( "stat_{$i}_value" ),
			'label' => isp_setting( "stat_{$i}_label" ),
		);
	}
	return $stats;
}

/**
 * The wa.me deep link with the prefilled greeting.
 */
function isp_whatsapp_link(): string {
	return 'https://wa.me/' . preg_replace( '/\D+/', '', isp_setting( 'whatsapp' ) ) . '?text=' . rawurlencode( isp_setting( 'whatsapp_message' ) );
}

/**
 * Full single-line postal address.
 */
function isp_address(): string {
	return implode( ', ', array_filter( array( isp_setting( 'address_street' ), isp_setting( 'address_city' ), isp_setting( 'address_country' ) ) ) );
}

/**
 * Booking page URL, made absolute when stored as a path.
 */
function isp_booking_url(): string {
	$url = isp_setting( 'booking_url' );
	return str_starts_with( $url, '/' ) ? home_url( $url ) : $url;
}

/**
 * Social profile URLs keyed by network, skipping empty ones.
 *
 * @return array<string, string>
 */
function isp_socials(): array {
	$out = array();
	foreach ( array( 'instagram', 'linkedin', 'pinterest', 'youtube' ) as $network ) {
		$url = isp_setting( "social_{$network}" );
		if ( $url ) {
			$out[ $network ] = $url;
		}
	}
	return $out;
}

/**
 * Read a registered isp_* meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key without the isp_ prefix.
 * @return mixed Null when unset.
 */
function isp_meta( int $post_id, string $key ) {
	$value = get_post_meta( $post_id, 'isp_' . $key, true );
	return ( '' === $value || array() === $value ) ? null : $value;
}

/**
 * Reading time for a post: explicit override, else ~200 words per minute.
 *
 * @param int $post_id Post ID.
 */
function isp_read_time( int $post_id ): string {
	$override = get_post_meta( $post_id, 'isp_read_time', true );
	if ( $override ) {
		return (string) $override;
	}
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	$mins  = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: minutes. */
	return sprintf( _n( '%d min read', '%d min read', $mins, 'interior-specifics-core' ), $mins );
}

/**
 * The visitor IP as the server sees it ('' when unavailable).
 */
function isp_client_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Hash of the visitor IP — stored on lead records instead of the raw IP.
 */
function isp_ip_hash(): string {
	return substr( wp_hash( 'isp_ip|' . isp_client_ip() ), 0, 20 );
}

/**
 * Rate-limit bucket for the visitor. IPv4 addresses count individually; IPv6 addresses are
 * grouped by their /64 network, since one client usually controls a whole /64 and could
 * otherwise rotate through endless buckets.
 */
function isp_rate_bucket(): string {
	$ip     = isp_client_ip();
	$packed = '' !== $ip ? inet_pton( $ip ) : false;
	if ( false !== $packed && 16 === strlen( $packed ) ) {
		$mapped = str_repeat( chr( 0 ), 10 ) . chr( 255 ) . chr( 255 );
		if ( str_starts_with( $packed, $mapped ) ) {
			// IPv4-mapped IPv6 (::ffff:a.b.c.d): bucket by the IPv4 address.
			$ip = (string) inet_ntop( substr( $packed, 12 ) );
		} else {
			$ip = inet_ntop( substr( $packed, 0, 8 ) . str_repeat( chr( 0 ), 8 ) ) . '/64';
		}
	}
	return substr( wp_hash( 'isp_rl|' . $ip ), 0, 20 );
}
