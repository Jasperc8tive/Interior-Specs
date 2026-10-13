<?php
/**
 * Credential Marquee block (port of the credential band in app/page.tsx + CredentialMarquee.tsx).
 *
 * CSS-only infinite loop: the list renders twice inside one track that slides by exactly one set
 * (translateX -50%), with faded edges. The duplicate set is hidden from assistive technology.
 * The loop pauses while hovered, and a small pause / play button (shown on hover, on focus and
 * while paused; wired in src/js/modules/marquee.js) stops it for good (WCAG 2.2.2).
 * Under prefers-reduced-motion it becomes a static, wrapping, centred row (home-hero.css).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_items = array();
foreach ( (array) ( $attributes['items'] ?? array() ) as $ist_item ) {
	$ist_text = is_array( $ist_item ) ? trim( (string) ( $ist_item['text'] ?? '' ) ) : trim( (string) $ist_item );
	if ( '' !== $ist_text ) {
		$ist_items[] = $ist_text;
	}
}

if ( ! $ist_items ) {
	return;
}

$ist_speed = (float) ( $attributes['speed'] ?? 36 );
$ist_speed = $ist_speed > 0 ? min( 600.0, max( 4.0, $ist_speed ) ) : 36.0;

$ist_wrapper = get_block_wrapper_attributes( array( 'class' => 'grain group/marquee relative bg-onyx py-6' ) );
?>
<div <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes(). ?> data-marquee>
	<div class="ist-marquee group relative flex overflow-hidden" style="<?php echo esc_attr( '--marquee-duration:' . $ist_speed . 's' ); ?>">
		<div class="ist-marquee__track flex shrink-0 items-center">
			<?php for ( $ist_set = 0; $ist_set < 2; $ist_set++ ) : ?>
				<ul class="ist-marquee__set flex shrink-0 items-center" role="list"<?php echo $ist_set ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $ist_items as $ist_text ) : ?>
						<li class="ist-marquee__item flex items-center">
							<span class="ist-marquee__text whitespace-nowrap px-7 text-xs font-medium uppercase tracking-[0.24em] text-ivory/55"><?php echo esc_html( $ist_text ); ?></span>
							<span class="ist-marquee__dot h-1 w-1 rounded-full bg-gold/60" aria-hidden="true"></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endfor; ?>
		</div>
	</div>
	<?php // Outside .ist-marquee so the edge fade does not mask it and hovering it does not pause the loop. ?>
	<button
		type="button"
		class="absolute left-4 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-ivory/25 bg-onyx/90 text-ivory/70 opacity-0 transition-[opacity,color,border-color] duration-300 hover:border-gold hover:text-gold focus-visible:border-gold focus-visible:text-gold focus-visible:opacity-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold group-hover/marquee:opacity-100 group-data-paused/marquee:opacity-100 motion-reduce:hidden md:left-6"
		data-marquee-toggle
		data-label-pause="<?php esc_attr_e( 'Pause the scrolling credentials', 'interior-specifics' ); ?>"
		data-label-play="<?php esc_attr_e( 'Play the scrolling credentials', 'interior-specifics' ); ?>"
		aria-label="<?php esc_attr_e( 'Pause the scrolling credentials', 'interior-specifics' ); ?>"
		hidden
	>
		<svg class="h-3 w-3 group-data-paused/marquee:hidden" viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M2.5 1.5h2.25v9H2.5zm4.75 0h2.25v9H7.25z"/></svg>
		<svg class="hidden h-3 w-3 translate-x-px group-data-paused/marquee:block" viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path fill="currentColor" d="M3 1.25v9.5L10.5 6z"/></svg>
	</button>
</div>
