<?php
/**
 * Story Hero block (port of components/home/StoryHero.tsx).
 *
 * Full-bleed opening scene that slides beneath the transparent header. On load the copy settles
 * into place (data-settle) while the headline wipes up word by word; on scroll the image scales and
 * drifts while the copy fades away (data-scrub). The scroll cue line bobs via CSS (home-hero.css).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_eyebrow   = (string) ( $attributes['eyebrow'] ?? '' );
$ist_title     = (string) ( $attributes['title'] ?? '' );
$ist_body      = (string) ( $attributes['body'] ?? '' );
$ist_primary   = (string) ( $attributes['primaryUrl'] ?? '' );
$ist_secondary = (string) ( $attributes['secondaryUrl'] ?? '' );
$ist_vertical  = (string) ( $attributes['verticalLabel'] ?? '' );

if ( '' === $ist_primary ) {
	$ist_primary = ist_booking_url();
}

if ( '' !== $ist_vertical ) {
	$ist_year     = ist_setting( 'founded_year' );
	$ist_vertical = trim( str_replace( '{year}', '' !== $ist_year ? $ist_year : '2009', $ist_vertical ) );
}

// Stats: the block's own list, or the headline stats from the site settings.
$ist_stats = array();
foreach ( (array) ( $attributes['stats'] ?? array() ) as $ist_stat ) {
	if ( is_array( $ist_stat ) && ( '' !== trim( (string) ( $ist_stat['value'] ?? '' ) ) || '' !== trim( (string) ( $ist_stat['label'] ?? '' ) ) ) ) {
		$ist_stats[] = array(
			'value' => (string) ( $ist_stat['value'] ?? '' ),
			'label' => (string) ( $ist_stat['label'] ?? '' ),
		);
	}
}
if ( ! $ist_stats ) {
	foreach ( ist_stats() as $ist_stat ) {
		if ( '' !== (string) $ist_stat['value'] || '' !== (string) $ist_stat['label'] ) {
			$ist_stats[] = $ist_stat;
		}
	}
}

$ist_wrapper = get_block_wrapper_attributes(
	array( 'class' => 'grain relative -mt-[88px] flex min-h-[100svh] items-center overflow-hidden bg-onyx' )
);
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes(). ?> data-scrub>
	<div class="absolute inset-0" data-scrub-media="scale:0.18;y:110">
		<?php
		echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			(int) ( $attributes['imageId'] ?? 0 ),
			(string) ( $attributes['imageUrl'] ?? '' ),
			(string) ( $attributes['imageAlt'] ?? '' ),
			'ist-hero',
			array(
				'class'   => 'absolute inset-0 h-full w-full object-cover',
				'sizes'   => '100vw',
				'loading' => 'eager',
			)
		);
		?>
		<div class="hero-overlay absolute inset-0"></div>
		<div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_50%_0%,transparent_55%,rgba(18,17,16,0.55))]"></div>
	</div>

	<?php if ( '' !== $ist_vertical ) : ?>
		<div class="pointer-events-none absolute left-6 top-1/2 hidden -translate-y-1/2 lg:block">
			<span class="block origin-left -rotate-90 text-[10px] font-medium uppercase tracking-[0.4em] text-ivory/50" data-settle style="--settle-delay:.9s;--settle-y:22px"><?php echo esc_html( $ist_vertical ); ?></span>
		</div>
	<?php endif; ?>

	<div class="<?php echo esc_attr( ist_container_class( 'relative z-10 pt-[88px]' ) ); ?>">
		<div class="max-w-4xl" data-scrub-fade="end:0.5;y:-80;yend:0.55">
			<?php if ( '' !== $ist_eyebrow ) : ?>
				<span class="eyebrow text-gold-soft" data-settle style="--settle-delay:.15s;--settle-y:22px"><?php echo esc_html( $ist_eyebrow ); ?></span>
			<?php endif; ?>

			<?php
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'        => $ist_title,
					'tag'         => 'h1',
					'class'       => 'display mt-7 text-5xl text-ivory sm:text-6xl lg:text-7xl xl:text-[5.25rem]',
					'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
					'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
					'delay'       => 0.25,
				)
			);
			?>

			<?php if ( '' !== $ist_body ) : ?>
				<p class="mt-7 max-w-xl text-lg text-ivory/85" data-settle style="--settle-delay:.75s;--settle-y:22px"><?php echo esc_html( $ist_body ); ?></p>
			<?php endif; ?>

			<?php
			$ist_buttons = ist_button( (string) ( $attributes['primaryLabel'] ?? '' ), $ist_primary, 'primary' )
				. ist_button( (string) ( $attributes['secondaryLabel'] ?? '' ), $ist_secondary, 'outline' );
			if ( '' !== $ist_buttons ) :
				?>
				<div class="mt-10 flex flex-col gap-4 sm:flex-row" data-settle style="--settle-delay:.9s;--settle-y:22px">
					<?php echo $ist_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in ist_button(). ?>
				</div>
			<?php endif; ?>

			<?php if ( $ist_stats ) : ?>
				<div class="mt-14 flex flex-wrap items-stretch gap-x-10 gap-y-6 border-t border-line-light pt-7" data-settle style="--settle-delay:1.05s;--settle-y:22px">
					<?php foreach ( $ist_stats as $ist_i => $ist_stat ) : ?>
						<div<?php echo $ist_i > 0 ? ' class="border-ivory/10 sm:border-l sm:pl-10"' : ''; ?>>
							<div class="font-serif text-2xl text-gold md:text-[1.7rem]"><?php echo esc_html( $ist_stat['value'] ); ?></div>
							<div class="mt-1 text-xs uppercase tracking-[0.18em] text-ivory/60"><?php echo esc_html( $ist_stat['label'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $attributes['showScrollCue'] ) ) : ?>
		<div class="absolute bottom-8 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center gap-2 text-ivory/70" data-scrub-fade="end:0.12" aria-hidden="true">
			<span class="text-[10px] uppercase tracking-[0.3em]"><?php echo esc_html( (string) ( $attributes['scrollLabel'] ?? '' ) ); ?></span>
			<span class="ist-scroll-cue block h-9 w-px bg-gradient-to-b from-gold to-transparent"></span>
		</div>
	<?php endif; ?>
</section>
