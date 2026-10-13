<?php
/**
 * Booking Scheduler block (port of the scheduler section of app/book/page.tsx).
 *
 * Left: RevealHeading, intro and the consultation request form. Right: Calendly's inline widget
 * when a Calendly URL is set in Settings → Interior Specifics (its script is enqueued only when
 * this block renders), otherwise the "Instant scheduling" card with phone and WhatsApp links.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_calendly = ist_setting( 'calendly_url' );
$ist_calendly = $ist_calendly && preg_match( '#^https://([a-z0-9-]+\.)?calendly\.com/#i', $ist_calendly ) ? $ist_calendly : '';
$ist_preview  = wp_is_serving_rest_request();
$ist_sched    = '' !== trim( (string) $attributes['schedulerTitle'] ) ? (string) $attributes['schedulerTitle'] : __( 'Schedule a consultation', 'interior-specifics' );

if ( $ist_calendly && ! $ist_preview ) {
	wp_enqueue_script(
		'calendly-widget',
		'https://assets.calendly.com/assets/external/widget.js',
		array(),
		null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- third-party script, versioned by Calendly.
		array(
			'in_footer' => true,
			'strategy'  => 'async',
		)
	);
}

$ist_call  = trim( (string) $attributes['callLabel'] );
$ist_phone = ist_setting( 'phone_display' );
$ist_wa    = trim( (string) $attributes['whatsappLabel'] );
$ist_hours = ! empty( $attributes['showHours'] ) ? ist_setting( 'hours' ) : '';
$ist_intro = trim( (string) $attributes['intro'] );

$ist_form = function_exists( 'ist_lead_form' )
	? ist_lead_form(
		'booking',
		array(
			'variant'       => 'light',
			'id'            => 'booking-form',
			'submit_label'  => (string) $attributes['submitLabel'],
			'success_title' => (string) $attributes['successTitle'],
			'success_body'  => (string) $attributes['successBody'],
		)
	)
	: '';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-white py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="grid gap-12 lg:grid-cols-5 lg:gap-16">
			<div class="lg:col-span-3" data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow'     => (string) $attributes['eyebrow'],
						'title'       => (string) $attributes['title'],
						'align'       => 'left',
						'accent_from' => (int) $attributes['accentFrom'],
						'accent_to'   => (int) $attributes['accentTo'],
					)
				);
				?>
				<?php if ( '' !== $ist_intro ) : ?>
					<p class="mt-5 text-lg text-muted"><?php echo esc_html( $ist_intro ); ?></p>
				<?php endif; ?>
				<div class="mt-8">
					<?php echo $ist_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by ist_lead_form(). ?>
				</div>
			</div>

			<div class="lg:col-span-2" data-reveal="section" style="--reveal-delay:.1s">
				<?php if ( $ist_calendly && $ist_preview ) : ?>
					<div class="flex h-[640px] w-full items-center justify-center border border-line bg-ivory p-8 text-center text-sm uppercase tracking-[0.2em] text-muted">
						<?php esc_html_e( 'Calendly scheduler', 'interior-specifics' ); ?>
					</div>
				<?php elseif ( $ist_calendly ) : ?>
					<?php // Without JS the slot holds a plain iframe of the scheduler (as the Next page did); Calendly's script fills it otherwise. ?>
					<div class="calendly-inline-widget w-full border border-line" data-url="<?php echo esc_url( $ist_calendly ); ?>" style="min-width:320px;height:640px" role="region" aria-label="<?php echo esc_attr( $ist_sched ); ?>"><noscript><iframe src="<?php echo esc_url( $ist_calendly ); ?>" title="<?php echo esc_attr( $ist_sched ); ?>" class="block h-full w-full border-0" loading="lazy"></iframe></noscript></div>
				<?php else : ?>
					<div class="flex h-full flex-col justify-center border-t-2 border-gold bg-ivory p-8">
						<?php if ( '' !== trim( (string) $attributes['fallbackTitle'] ) ) : ?>
							<h3 class="text-xl text-charcoal"><?php echo esc_html( (string) $attributes['fallbackTitle'] ); ?></h3>
						<?php endif; ?>
						<?php if ( '' !== trim( (string) $attributes['fallbackBody'] ) ) : ?>
							<p class="mt-3 text-sm text-muted"><?php echo esc_html( (string) $attributes['fallbackBody'] ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $ist_call || '' !== $ist_wa ) : ?>
							<div class="mt-6 space-y-2 text-sm">
								<?php if ( '' !== $ist_call ) : ?>
									<a href="<?php echo esc_url( ist_phone_href(), array( 'tel' ) ); ?>" class="block text-charcoal transition-colors hover:text-gold" data-track="phone"><?php echo esc_html( $ist_call . ' ' . $ist_phone ); ?></a>
								<?php endif; ?>
								<?php if ( '' !== $ist_wa ) : ?>
									<a href="<?php echo esc_url( ist_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer" class="block text-charcoal transition-colors hover:text-gold" data-track="whatsapp"><?php echo esc_html( $ist_wa ); ?><span class="sr-only"> <?php esc_html_e( '(opens in a new tab)', 'interior-specifics' ); ?></span></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $ist_hours ) : ?>
							<p class="mt-6 text-xs text-muted"><?php echo esc_html( $ist_hours ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
