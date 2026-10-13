<?php
/**
 * Closing call-to-action band (port of components/CTASection.tsx), used at the foot of most
 * pages: an onyx grain canvas with a soft gold glow, a fading eyebrow, a masked word-by-word
 * heading, an intro that rises in, then the booking and WhatsApp buttons.
 *
 * Args (all optional; omitted keys fall back to the CTASection.tsx defaults):
 *   eyebrow, title, intro, primary_label, primary_url (default: the booking page from
 *   settings), secondary_label (the WhatsApp button; '' hides it), accent_from, accent_to,
 *   wrapper (pre-built, escaped wrapper attributes from get_block_wrapper_attributes()).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_cta = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow'         => __( 'Begin', 'interior-specifics' ),
		'title'           => __( 'Ready to transform your space?', 'interior-specifics' ),
		'intro'           => __( "Every project is unique. Let's discuss yours over a private consultation.", 'interior-specifics' ),
		'primary_label'   => __( 'Book Your Private Consultation', 'interior-specifics' ),
		'primary_url'     => '',
		'secondary_label' => __( 'Chat on WhatsApp', 'interior-specifics' ),
		'accent_from'     => -1,
		'accent_to'       => -1,
		'wrapper'         => '',
	)
);

$ist_cta_class   = 'grain relative overflow-hidden bg-onyx py-24 md:py-32';
$ist_cta_wrapper = $ist_cta['wrapper'] ? $ist_cta['wrapper'] : 'class="' . esc_attr( $ist_cta_class ) . '"';
$ist_cta_url     = '' !== trim( (string) $ist_cta['primary_url'] ) ? (string) $ist_cta['primary_url'] : ist_booking_url();
$ist_cta_intro   = (string) $ist_cta['intro'];
$ist_cta_primary = (string) $ist_cta['primary_label'];
$ist_cta_second  = (string) $ist_cta['secondary_label'];
?>
<section <?php echo $ist_cta_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by get_block_wrapper_attributes() or escaped above. ?>>
	<div class="absolute inset-0 bg-[radial-gradient(80%_60%_at_50%_0%,rgba(201,169,97,0.12),transparent_70%)]" aria-hidden="true"></div>
	<div class="<?php echo esc_attr( ist_container_class( 'relative' ) ); ?>">
		<div class="mx-auto flex max-w-2xl flex-col items-center gap-9 text-center">
			<div class="flex flex-col items-center gap-5">
				<?php
				echo ist_eyebrow( (string) $ist_cta['eyebrow'], 'eyebrow--center justify-center text-gold-soft' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'text'        => (string) $ist_cta['title'],
						'tag'         => 'h2',
						// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
						'class'       => 'text-3xl leading-[1.12] text-ivory sm:text-4xl lg:text-[2.9rem]',
						'accent_from' => (int) $ist_cta['accent_from'],
						'accent_to'   => (int) $ist_cta['accent_to'],
					)
				);
				?>
				<?php if ( '' !== $ist_cta_intro ) : ?>
					<p class="max-w-xl text-lg text-ivory/80" data-reveal style="--reveal-y:16px;--reveal-delay:.12s"><?php echo esc_html( $ist_cta_intro ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $ist_cta_primary || '' !== $ist_cta_second ) : ?>
				<div class="flex flex-col gap-4 sm:flex-row" data-reveal style="--reveal-y:16px;--reveal-delay:.2s">
					<?php
					echo ist_button( $ist_cta_primary, $ist_cta_url, 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					echo ist_button( $ist_cta_second, ist_whatsapp_link(), 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
