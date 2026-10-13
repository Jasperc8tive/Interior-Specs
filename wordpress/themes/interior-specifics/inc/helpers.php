<?php
/**
 * Shared presentation helpers — PHP ports of the Next.js primitives (components/ui.tsx,
 * AnimatedHeading, RevealHeading, Logo). Every helper returns escaped HTML.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

/*
-------------------------------------------------------------------------
 * Settings (proxied to the Core plugin, with safe fallbacks)
 * ---------------------------------------------------------------------- */

/**
 * A site setting from Settings → Interior Specifics.
 *
 * @param string $key Setting key (see isp_default_settings()).
 */
function ist_setting( string $key ): string {
	return function_exists( 'isp_setting' ) ? isp_setting( $key ) : '';
}

/**
 * WhatsApp chat link.
 */
function ist_whatsapp_link(): string {
	return function_exists( 'isp_whatsapp_link' ) ? isp_whatsapp_link() : 'https://wa.me/';
}

/**
 * Single-line postal address.
 */
function ist_address(): string {
	return function_exists( 'isp_address' ) ? isp_address() : '';
}

/**
 * Booking page URL (absolute).
 */
function ist_booking_url(): string {
	return function_exists( 'isp_booking_url' ) ? isp_booking_url() : home_url( '/book/' );
}

/**
 * The tel: href for the studio phone.
 */
function ist_phone_href(): string {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', ist_setting( 'phone_href' ) );
}

/**
 * Headline stats [['value' => '15+', 'label' => 'Years'], ...].
 *
 * @return array<int, array{value: string, label: string}>
 */
function ist_stats(): array {
	return function_exists( 'isp_stats' ) ? isp_stats() : array();
}

/*
-------------------------------------------------------------------------
 * Markup primitives
 * ---------------------------------------------------------------------- */

/**
 * The site container: max 1280px with 24px / 40px gutters.
 *
 * @param string $extra Extra classes.
 */
function ist_container_class( string $extra = '' ): string {
	return trim( 'mx-auto w-full max-w-7xl px-6 md:px-10 ' . $extra );
}

/**
 * Join class names, skipping empties.
 *
 * @param string|false|null ...$classes Class strings.
 */
function ist_classes( ...$classes ): string {
	return trim( implode( ' ', array_filter( array_map( 'strval', $classes ) ) ) );
}

/**
 * Whether a URL leaves the site (opens in a new tab).
 *
 * @param string $url URL.
 */
function ist_is_external( string $url ): bool {
	if ( ! preg_match( '#^https?://#i', $url ) ) {
		return false;
	}
	return wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST );
}

/**
 * Resolve an editor-entered link: "/about/" style paths become absolute site URLs.
 *
 * @param string $url URL or site path.
 */
function ist_url( string $url ): string {
	if ( '' === $url ) {
		return '';
	}
	if ( str_starts_with( $url, '/' ) && ! str_starts_with( $url, '//' ) ) {
		return home_url( $url );
	}
	return $url;
}

/**
 * Attribute string for a link: href plus new-tab handling for external URLs.
 *
 * @param string $url URL or site path.
 */
function ist_link_attrs( string $url ): string {
	$url   = ist_url( $url );
	$attrs = 'href="' . esc_url( $url ) . '"';
	if ( ist_is_external( $url ) ) {
		$attrs .= ' target="_blank" rel="noopener noreferrer"';
	}
	return $attrs;
}

/**
 * Button variants from components/ui.tsx.
 *
 * @param string $variant primary|secondary|outline|dark.
 * @param string $ring    Focus ring colour class replacing the variant's own, e.g.
 *                        focus-visible:ring-charcoal on a light surface where gold is under 3:1.
 */
function ist_button_classes( string $variant = 'primary', string $ring = '' ): string {
	$variants = array(
		'primary'   => array( 'bg-terracotta text-charcoal hover:bg-gold', 'focus-visible:ring-gold' ),
		'secondary' => array( 'bg-burgundy text-white hover:bg-burgundy/90', 'focus-visible:ring-burgundy' ),
		'outline'   => array( 'border border-gold text-gold hover:bg-gold hover:text-charcoal', 'focus-visible:ring-gold' ),
		'dark'      => array( 'bg-charcoal text-ivory hover:bg-charcoal-light', 'focus-visible:ring-charcoal' ),
	);
	$classes  = $variants[ $variant ] ?? $variants['primary'];
	return 'inline-flex items-center justify-center min-h-12 px-7 py-3 text-sm font-medium uppercase tracking-[0.15em] transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 ' . $classes[0] . ' ' . ( '' !== $ring ? $ring : $classes[1] );
}

/**
 * A link styled as a button (components/ui.tsx Button with href).
 *
 * @param string $label   Visible label.
 * @param string $url     URL or site path.
 * @param string $variant primary|secondary|outline|dark.
 * @param string $class   Extra classes.
 * @param string $extra   Extra raw attributes (already escaped), e.g. data-* hooks.
 */
function ist_button( string $label, string $url, string $variant = 'primary', string $class = '', string $extra = '' ): string {
	if ( '' === $label ) {
		return '';
	}
	return sprintf(
		'<a %1$s class="%2$s" %3$s>%4$s</a>',
		ist_link_attrs( $url ),
		esc_attr( ist_classes( ist_button_classes( $variant ), $class ) ),
		$extra,
		esc_html( $label )
	);
}

/**
 * Eyebrow label with the gold tick.
 *
 * @param string $text   Text.
 * @param string $class  Extra classes (colour, e.g. text-gold / text-gold-soft; eyebrow--center).
 * @param bool   $reveal Fade in on scroll.
 */
function ist_eyebrow( string $text, string $class = 'text-gold', bool $reveal = true ): string {
	if ( '' === $text ) {
		return '';
	}
	return sprintf(
		'<span class="%s"%s>%s</span>',
		esc_attr( ist_classes( 'eyebrow', $class ) ),
		$reveal ? ' data-reveal="fade"' : '',
		esc_html( $text )
	);
}

/**
 * Masked word-by-word heading reveal (port of components/home/AnimatedHeading.tsx).
 *
 * Words wipe up one after another (70ms apart) when the heading scrolls into view. An optional
 * word range renders in the gold Cormorant italic accent. Screen readers get the plain text via
 * aria-label; with no JS or reduced motion it is an ordinary heading.
 *
 * @param array{text?: string, tag?: string, class?: string, accent_from?: int, accent_to?: int, accent_class?: string, delay?: float, id?: string} $args Options.
 */
function ist_animated_heading( array $args ): string {
	$args = wp_parse_args(
		$args,
		array(
			'text'         => '',
			'tag'          => 'h2',
			'class'        => '',
			'accent_from'  => -1,
			'accent_to'    => -1,
			'accent_class' => 'accent text-gold',
			'delay'        => 0,
			'id'           => '',
		)
	);
	$text = trim( (string) $args['text'] );
	if ( '' === $text ) {
		return '';
	}
	$tag   = in_array( $args['tag'], array( 'h1', 'h2', 'h3', 'h4', 'p' ), true ) ? $args['tag'] : 'h2';
	$words = preg_split( '/\s+/', $text );
	$from  = (int) $args['accent_from'];
	$to    = (int) $args['accent_to'];
	$out   = array();

	$last = count( $words ) - 1;
	foreach ( $words as $i => $word ) {
		$is_accent = $from >= 0 && $i >= $from && $i <= $to;
		// The word gap lives inside each mask (as in the React DOM) so text-wrap: balance
		// measures and breaks lines exactly like the original.
		$out[] = sprintf(
			'<span class="w"><span class="%s" style="--i:%d">%s</span>%s</span>',
			esc_attr( ist_classes( 'w__i', $is_accent ? $args['accent_class'] : '' ) ),
			$i,
			esc_html( $word ),
			$i < $last ? '&nbsp;' : ''
		);
	}

	$style = $args['delay'] ? sprintf( ' style="--reveal-delay:%ss"', esc_attr( (string) (float) $args['delay'] ) ) : '';
	$id    = $args['id'] ? sprintf( ' id="%s"', esc_attr( $args['id'] ) ) : '';

	return sprintf(
		'<%1$s%2$s class="%3$s" aria-label="%4$s"%5$s><span aria-hidden="true">%6$s</span></%1$s>',
		$tag,
		$id,
		esc_attr( ist_classes( 'ist-words', $args['class'] ) ),
		esc_attr( $text ),
		$style,
		implode( '', $out )
	);
}

/**
 * Section heading: eyebrow, animated heading, gold rule, intro (port of components/RevealHeading.tsx).
 *
 * @param array{eyebrow?: string, title?: string, intro?: string, align?: string, light?: bool, accent_from?: int, accent_to?: int, tag?: string, class?: string, title_class?: string} $args Options.
 */
function ist_reveal_heading( array $args ): string {
	$args   = wp_parse_args(
		$args,
		array(
			'eyebrow'     => '',
			'title'       => '',
			'intro'       => '',
			'align'       => 'center',
			'light'       => false,
			'accent_from' => -1,
			'accent_to'   => -1,
			'tag'         => 'h2',
			'class'       => '',
			'title_class' => 'max-w-3xl text-3xl md:text-4xl lg:text-[2.75rem]',
		)
	);
	$center = 'center' === $args['align'];
	$light  = (bool) $args['light'];

	$html  = sprintf( '<div class="%s">', esc_attr( ist_classes( 'flex flex-col gap-5', $center ? 'items-center text-center' : 'items-start text-left', $args['class'] ) ) );
	$html .= ist_eyebrow( (string) $args['eyebrow'], ist_classes( $center ? 'eyebrow--center justify-center' : '', $light ? 'text-gold-soft' : 'text-gold' ) );
	$html .= ist_animated_heading(
		array(
			'text'        => $args['title'],
			'tag'         => $args['tag'],
			'class'       => ist_classes( $args['title_class'], $light ? 'text-ivory' : 'text-charcoal' ),
			'accent_from' => $args['accent_from'],
			'accent_to'   => $args['accent_to'],
		)
	);
	$html .= sprintf( '<div class="%s"></div>', esc_attr( ist_classes( 'gold-rule', $center ? 'mx-auto' : '' ) ) );
	if ( '' !== (string) $args['intro'] ) {
		$html .= sprintf(
			'<p class="%s" data-reveal style="--reveal-y:16px;--reveal-delay:.15s">%s</p>',
			esc_attr( ist_classes( 'max-w-2xl text-base md:text-lg', $light ? 'text-ivory/80' : 'text-muted' ) ),
			esc_html( (string) $args['intro'] )
		);
	}
	return $html . '</div>';
}

/**
 * Responsive image: a Media Library attachment (with srcset) or a plain external URL fallback.
 *
 * @param int                  $id    Attachment ID (preferred).
 * @param string               $url   Fallback URL when no attachment is set.
 * @param string               $alt   Alt text ('' for decorative images).
 * @param string               $size  Registered size, e.g. ist-card, ist-hero, large.
 * @param array<string, mixed> $attrs Extra attributes: class, sizes, loading, fetchpriority.
 */
function ist_image( int $id, string $url, string $alt, string $size = 'large', array $attrs = array() ): string {
	$attrs = array_merge(
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attrs
	);
	if ( 'eager' === $attrs['loading'] ) {
		$attrs['fetchpriority'] = $attrs['fetchpriority'] ?? 'high';
	}

	if ( $id && wp_attachment_is_image( $id ) ) {
		$attrs['alt'] = $alt;
		return (string) wp_get_attachment_image( $id, $size, false, $attrs );
	}
	if ( '' === $url ) {
		return '';
	}

	$html = sprintf( '<img src="%s" alt="%s"', esc_url( $url ), esc_attr( $alt ) );
	foreach ( $attrs as $name => $value ) {
		if ( 'sizes' === $name || null === $value || false === $value ) {
			continue;
		}
		$html .= sprintf( ' %s="%s"', esc_attr( (string) $name ), esc_attr( (string) $value ) );
	}
	return $html . '>';
}

/**
 * Attachment ID of a template's hero image, chosen under Settings → Interior Specifics → Page
 * heroes. A context without its own image uses the default hero; 0 when neither is set, and the
 * hero then renders on its charcoal background.
 *
 * @param string $context services|showroom|journal|default.
 */
function ist_page_hero_image_id( string $context = 'default' ): int {
	foreach ( array_unique( array( $context, 'default' ) ) as $key ) {
		$id = (int) ist_setting( 'hero_' . $key );
		if ( $id && wp_attachment_is_image( $id ) ) {
			return $id;
		}
	}
	return 0;
}

/**
 * Load attachment posts and their meta up front (two queries in all) before rendering them with
 * ist_image(), which otherwise costs two queries per image. For WP_Query results use core's
 * update_post_thumbnail_cache() instead.
 *
 * @param array<int, int|string> $ids Attachment IDs (empties are skipped).
 */
function ist_prime_attachments( array $ids ): void {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	if ( $ids ) {
		_prime_post_caches( $ids, false, true );
	}
}

/**
 * The featured image of a post (or empty string).
 *
 * @param int|WP_Post          $post  Post.
 * @param string               $size  Size.
 * @param array<string, mixed> $attrs Attributes (class, loading, sizes...).
 */
function ist_post_image( $post, string $size = 'large', array $attrs = array() ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$thumb = (int) get_post_thumbnail_id( $post );
	$alt   = $thumb ? (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) : '';
	return ist_image( $thumb, '', '' !== $alt ? $alt : get_the_title( $post ), $size, $attrs );
}

/**
 * The brand roundel (port of components/Logo.tsx): gold circle, tick ring, curved wordmark, I | S.
 *
 * @param string $class Size classes.
 * @param string $title Accessible name; '' renders it decorative.
 */
function ist_logo( string $class = 'h-10 w-10', string $title = '' ): string {
	static $n = 0;
	++$n;
	$arc   = 'ist-arc-' . $n;
	$ticks = '';
	for ( $angle = 0; $angle < 360; $angle += 6 ) {
		if ( min( $angle, 360 - $angle ) > 52 ) {
			$ticks .= sprintf( '<line x1="50" y1="6" x2="50" y2="10.5" transform="rotate(%d 50 50)"/>', $angle );
		}
	}
	$label = $title ? sprintf( ' role="img" aria-label="%s"', esc_attr( $title ) ) : ' aria-hidden="true" focusable="false"';
	$serif = 'font-family:var(--font-serif)';

	return sprintf(
		'<svg viewBox="0 0 100 100" class="%1$s"%2$s><circle cx="50" cy="50" r="50" fill="#C9A961"/><g stroke="#FFFFFF" stroke-width="1.1" stroke-linecap="round" opacity="0.9">%3$s</g><defs><path id="%4$s" d="M 14 50 A 36 36 0 0 1 86 50" fill="none"/></defs><text fill="#FFFFFF" font-size="7.2" letter-spacing="2.4" text-anchor="middle" style="%5$s"><textPath href="#%4$s" startOffset="50%%">INTERIOR · SPECIFICS</textPath></text><text x="36" y="64" text-anchor="middle" font-size="40" fill="#FFFFFF" style="%5$s">I</text><line x1="50" y1="40" x2="50" y2="64" stroke="#FFFFFF" stroke-width="1.6"/><text x="63" y="64" text-anchor="middle" font-size="40" fill="#FFFFFF" style="%5$s">S</text></svg>',
		esc_attr( $class ),
		$label,
		$ticks,
		esc_attr( $arc ),
		$serif
	);
}

/**
 * Inline SVG icons (decorative; give the parent control an accessible name).
 *
 * @param string $name  chevron-down|arrow-right|arrow-left|plus|close|whatsapp.
 * @param string $class Classes.
 */
function ist_icon( string $name, string $class = 'h-4 w-4' ): string {
	$paths = array(
		'chevron-down' => '<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'arrow-right'  => '<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
		'arrow-left'   => '<path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
		'plus'         => '<path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
		'close'        => '<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
		'whatsapp'     => '<path fill="currentColor" d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.978-1.042zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf( '<svg viewBox="0 0 24 24" class="%s" aria-hidden="true" focusable="false">%s</svg>', esc_attr( $class ), $paths[ $name ] );
}
