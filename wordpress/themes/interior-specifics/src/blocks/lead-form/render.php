<?php
/**
 * Lead Form block. With its defaults it is components/LeadMagnet.tsx: an ivory band with the
 * "Free Guide" SectionHeading and the guide request form, revealed as one piece on scroll.
 * The other styles wrap the same shared renderer (ist_lead_form) in a matching band.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_type    = in_array( $attributes['type'], array( 'lead-magnet', 'contact', 'booking', 'newsletter' ), true ) ? (string) $attributes['type'] : 'lead-magnet';
$ist_variant = in_array( $attributes['variant'], array( 'section', 'light', 'dark', 'footer' ), true ) ? (string) $attributes['variant'] : 'section';
$ist_light   = in_array( $ist_variant, array( 'dark', 'footer' ), true );

$ist_args = array(
	'variant' => $ist_variant,
	'id'      => ! empty( $attributes['anchor'] ) ? $attributes['anchor'] . '-form' : $ist_type . '-form',
);
// Empty copy fields keep the form's standard wording.
foreach ( array(
	'submitLabel'  => 'submit_label',
	'successTitle' => 'success_title',
	'successBody'  => 'success_body',
	'finePrint'    => 'fine_print',
) as $ist_attr => $ist_arg ) {
	if ( '' !== trim( (string) $attributes[ $ist_attr ] ) ) {
		$ist_args[ $ist_arg ] = (string) $attributes[ $ist_attr ];
	}
}

$ist_form = function_exists( 'ist_lead_form' ) ? ist_lead_form( $ist_type, $ist_args ) : '';
if ( '' === $ist_form ) {
	return;
}

$ist_eyebrow = trim( (string) $attributes['eyebrow'] );
$ist_title   = trim( (string) $attributes['title'] );
$ist_intro   = trim( (string) $attributes['intro'] );

$ist_bands                                    = array(
	'section' => array( 'bg-ivory py-20 md:py-28', 'mx-auto max-w-2xl text-center', '' ),
	'light'   => array( 'bg-white py-20 md:py-28', 'mx-auto max-w-3xl', 'mt-10' ),
	'dark'    => array( 'grain relative overflow-hidden bg-onyx py-20 md:py-28', 'relative mx-auto max-w-xl', 'mt-10 border border-line-light bg-white/[0.02] p-7 backdrop-blur-sm md:p-9' ),
	'footer'  => array( 'bg-charcoal py-16 md:py-20', 'mx-auto max-w-md', 'mt-8' ),
);
list( $ist_band, $ist_inner, $ist_form_wrap ) = $ist_bands[ $ist_variant ];
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $ist_band ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="<?php echo esc_attr( $ist_inner ); ?>" data-reveal="section">
			<?php if ( '' !== $ist_eyebrow || '' !== $ist_title || '' !== $ist_intro ) : ?>
				<?php // Port of SectionHeading (components/ui.tsx): static, centred. ?>
				<div class="flex flex-col items-center gap-4 text-center">
					<?php if ( '' !== $ist_eyebrow ) : ?>
						<span class="<?php echo esc_attr( ist_classes( 'text-xs font-semibold uppercase tracking-[0.25em]', $ist_light ? 'text-gold-soft' : 'text-gold' ) ); ?>"><?php echo esc_html( $ist_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $ist_title ) : ?>
						<h2 class="<?php echo esc_attr( ist_classes( 'max-w-3xl text-3xl md:text-4xl lg:text-[2.75rem]', $ist_light ? 'text-ivory' : 'text-charcoal' ) ); ?>"><?php echo esc_html( $ist_title ); ?></h2>
					<?php endif; ?>
					<div class="gold-rule mx-auto"></div>
					<?php if ( '' !== $ist_intro ) : ?>
						<p class="<?php echo esc_attr( ist_classes( 'max-w-2xl text-base md:text-lg', $ist_light ? 'text-ivory/80' : 'text-graysoft' ) ); ?>"><?php echo esc_html( $ist_intro ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( '' !== $ist_form_wrap ) : ?>
				<div class="<?php echo esc_attr( $ist_form_wrap ); ?>"><?php echo $ist_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by ist_lead_form(). ?></div>
			<?php else : ?>
				<?php echo $ist_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by ist_lead_form(). ?>
			<?php endif; ?>
		</div>
	</div>
</section>
