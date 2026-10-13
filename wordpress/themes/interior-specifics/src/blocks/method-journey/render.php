<?php
/**
 * Method Journey block (port of components/home/MethodJourney.tsx + lib/method.ts).
 *
 * Both layouts are server-rendered inside one section#method:
 *  - .ist-method__stage — the desktop scrollytelling: the section grows to N × 100vh while a
 *    sticky 100vh stage cross-fades one act at a time. Shown only on md+ with motion allowed
 *    (CSS pre-pins under html.js-motion so there is no layout jump; src/js/modules/method-journey.js
 *    confirms with .is-pinned and drives the active act from scroll progress).
 *  - .ist-method__stack — stacked full-bleed acts with the full story: the no-JS, phone and
 *    reduced-motion layout, and what search engines read.
 * Only one layout is displayed at a time, so assistive tech never meets both.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_acts = array_values(
	array_filter(
		is_array( $attributes['acts'] ?? null ) ? $attributes['acts'] : array(),
		static fn( $act ) => is_array( $act ) && ( '' !== trim( (string) ( $act['title'] ?? '' ) ) || '' !== trim( (string) ( $act['narrative'] ?? '' ) ) )
	)
);

if ( ! $ist_acts ) {
	return;
}

$ist_count   = count( $ist_acts );
$ist_eyebrow = (string) ( $attributes['eyebrow'] ?? '' );
$ist_title   = (string) ( $attributes['title'] ?? '' );
$ist_intro   = (string) ( $attributes['intro'] ?? '' );
$ist_anchor  = (string) ( $attributes['anchor'] ?? '' );

/**
 * Full-bleed act image (attachment with srcset, or the default Unsplash URL).
 *
 * @param array<string, mixed> $act Act.
 */
$ist_act_image = static function ( array $act ): string {
	$title = (string) ( $act['title'] ?? '' );
	$alt   = trim( (string) ( $act['imageAlt'] ?? '' ) );
	if ( '' === $alt && '' !== $title ) {
		/* translators: %s: method act title, e.g. Discover. */
		$alt = sprintf( __( '%s — the Interior Specifics Method', 'interior-specifics' ), $title );
	}
	return ist_image(
		(int) ( $act['imageId'] ?? 0 ),
		(string) ( $act['imageUrl'] ?? '' ),
		$alt,
		'ist-hero',
		array(
			'class' => 'absolute inset-0 h-full w-full object-cover',
			'sizes' => '100vw',
		)
	);
};

// Each act image renders twice (stage and stack); load them all in two queries up front.
ist_prime_attachments( array_column( $ist_acts, 'imageId' ) );

$ist_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'ist-method relative bg-charcoal',
		'id'    => '' !== $ist_anchor ? $ist_anchor : 'method',
		'style' => '--acts:' . $ist_count,
	)
);
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. ?> data-method-journey>
	<div class="ist-method__stage sticky top-0 h-screen overflow-hidden">
		<?php if ( '' !== $ist_title ) : ?>
			<h2 class="screen-reader-text"><?php echo esc_html( $ist_title ); ?></h2>
		<?php endif; ?>

		<?php foreach ( $ist_acts as $ist_i => $ist_act ) : ?>
			<div class="<?php echo esc_attr( ist_classes( 'ist-method__scene absolute inset-0', 0 === $ist_i ? 'is-active' : '' ) ); ?>" data-method-scene>
				<div class="ist-method__media absolute inset-0">
					<?php echo $ist_act_image( $ist_act ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in ist_image(). ?>
					<div class="absolute inset-0 bg-charcoal/55"></div>
					<div class="hero-overlay absolute inset-0"></div>
				</div>

				<div class="ist-method__copy absolute inset-0 z-10 flex items-center">
					<div class="<?php echo esc_attr( ist_container_class() ); ?>">
						<div class="max-w-2xl">
							<span class="font-serif text-6xl text-gold/80 md:text-7xl"><?php echo esc_html( (string) ( $ist_act['n'] ?? '' ) ); ?></span>
							<h3 class="mt-3 text-4xl leading-[1.12] text-ivory sm:text-5xl"><?php echo esc_html( (string) ( $ist_act['title'] ?? '' ) ); ?></h3>
							<div class="gold-rule mt-6"></div>
							<p class="mt-6 max-w-xl text-lg text-ivory/85"><?php echo esc_html( (string) ( $ist_act['narrative'] ?? '' ) ); ?></p>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php if ( '' !== $ist_eyebrow ) : ?>
			<div class="pointer-events-none absolute inset-x-0 top-0 z-20 pt-[120px]" aria-hidden="true">
				<div class="<?php echo esc_attr( ist_container_class() ); ?>">
					<span class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-soft"><?php echo esc_html( $ist_eyebrow ); ?></span>
				</div>
			</div>
		<?php endif; ?>

		<ol class="absolute right-8 top-1/2 z-20 hidden -translate-y-1/2 flex-col gap-5 lg:flex" aria-label="<?php esc_attr_e( 'Method acts', 'interior-specifics' ); ?>">
			<?php foreach ( $ist_acts as $ist_i => $ist_act ) : ?>
				<li class="<?php echo esc_attr( ist_classes( 'group flex justify-end', 0 === $ist_i ? 'is-active' : '' ) ); ?>" data-method-rail>
					<button type="button" class="flex items-center justify-end gap-3 text-right focus:outline-none focus-visible:ring-1 focus-visible:ring-gold focus-visible:ring-offset-4 focus-visible:ring-offset-charcoal" data-method-goto="<?php echo esc_attr( (string) $ist_i ); ?>"<?php echo 0 === $ist_i ? ' aria-current="step"' : ''; ?>>
						<span class="text-xs uppercase tracking-[0.2em] text-ivory/40 transition-colors duration-300 group-hover:text-ivory/70 group-[.is-active]:text-gold"><?php echo esc_html( (string) ( $ist_act['title'] ?? '' ) ); ?></span>
						<span class="h-2 w-2 rounded-full bg-ivory/30 transition-all duration-300 group-[.is-active]:scale-125 group-[.is-active]:bg-gold" aria-hidden="true"></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>

	<div class="ist-method__stack">
		<div class="<?php echo esc_attr( ist_container_class( 'py-16 md:py-20' ) ); ?>">
			<div data-reveal="section">
				<div class="flex flex-col items-center gap-4 text-center">
					<?php if ( '' !== $ist_eyebrow ) : ?>
						<span class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-soft"><?php echo esc_html( $ist_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $ist_title ) : ?>
						<h2 class="max-w-3xl text-3xl leading-[1.12] text-ivory md:text-4xl lg:text-[2.75rem]"><?php echo esc_html( $ist_title ); ?></h2>
					<?php endif; ?>
					<div class="gold-rule mx-auto"></div>
					<?php if ( '' !== $ist_intro ) : ?>
						<p class="max-w-2xl text-base text-ivory/80 md:text-lg"><?php echo esc_html( $ist_intro ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php foreach ( $ist_acts as $ist_act ) : ?>
			<div class="relative flex min-h-[70vh] items-center">
				<?php echo $ist_act_image( $ist_act ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in ist_image(). ?>
				<div class="absolute inset-0 bg-charcoal/60"></div>
				<div class="hero-overlay absolute inset-0"></div>
				<div class="<?php echo esc_attr( ist_container_class( 'relative z-10' ) ); ?>">
					<div class="max-w-xl" data-reveal="section">
						<span class="font-serif text-5xl text-gold/80"><?php echo esc_html( (string) ( $ist_act['n'] ?? '' ) ); ?></span>
						<h3 class="mt-3 text-3xl leading-[1.12] text-ivory sm:text-4xl"><?php echo esc_html( (string) ( $ist_act['title'] ?? '' ) ); ?></h3>
						<div class="gold-rule mt-5"></div>
						<p class="mt-5 text-lg text-ivory/85"><?php echo esc_html( (string) ( $ist_act['narrative'] ?? '' ) ); ?></p>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
