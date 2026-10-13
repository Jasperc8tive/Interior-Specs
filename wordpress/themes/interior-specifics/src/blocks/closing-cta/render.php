<?php
/**
 * Closing CTA block (port of components/home/ClosingCTA.tsx).
 *
 * A dark onyx canvas with a faint architectural backdrop and a gold glow: the invitation,
 * the complimentary-guide offer, WhatsApp / phone links, and the lead-magnet form card. The
 * form itself comes from the shared renderer ist_lead_form() (inc/forms.php).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_before   = trim( (string) $attributes['bodyBefore'] );
$ist_guide    = trim( (string) $attributes['guideTitle'] );
$ist_after    = trim( (string) $attributes['bodyAfter'] );
$ist_wa_label = (string) $attributes['whatsappLabel'];
$ist_phone    = ! empty( $attributes['showPhone'] ) ? ist_setting( 'phone_display' ) : '';
$ist_alt      = (string) $attributes['imageAlt'];
$ist_form     = function_exists( 'ist_lead_form' )
	? ist_lead_form(
		'lead-magnet',
		array(
			'variant'       => 'dark',
			'id'            => 'closing-cta-form',
			'submit_label'  => (string) $attributes['submitLabel'],
			'fine_print'    => (string) $attributes['finePrint'],
			'success_title' => (string) $attributes['successTitle'],
			'success_body'  => (string) $attributes['successBody'],
		)
	)
	: '';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'grain relative overflow-hidden bg-onyx py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="absolute inset-0 opacity-[0.12]"<?php echo '' === $ist_alt ? ' aria-hidden="true"' : ''; ?>>
		<?php
		echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			(int) $attributes['imageId'],
			(string) $attributes['imageUrl'],
			$ist_alt,
			'ist-hero',
			array(
				'class' => 'absolute inset-0 h-full w-full object-cover',
				'sizes' => '100vw',
			)
		);
		?>
	</div>
	<div class="absolute inset-0 bg-[radial-gradient(80%_60%_at_50%_0%,rgba(201,169,97,0.12),transparent_70%)]" aria-hidden="true"></div>

	<div class="<?php echo esc_attr( ist_container_class( 'relative' ) ); ?>">
		<div class="grid gap-14 lg:grid-cols-2 lg:gap-20">
			<div class="lg:pt-6">
				<?php
				echo ist_eyebrow( (string) $attributes['eyebrow'], 'text-gold-soft', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'text'        => (string) $attributes['title'],
						'class'       => 'mt-6 text-4xl leading-[1.12] text-ivory sm:text-5xl lg:text-[3.25rem]',
						'accent_from' => (int) $attributes['accentFrom'],
						'accent_to'   => (int) $attributes['accentTo'],
					)
				);
				?>

				<?php if ( '' !== $ist_before || '' !== $ist_guide || '' !== $ist_after ) : ?>
					<p class="mt-7 max-w-md text-lg text-ivory/80" data-reveal style="--reveal-y:18px;--reveal-delay:.12s">
						<?php
						echo esc_html( $ist_before );
						if ( '' !== $ist_guide ) {
							printf( ' <span class="accent text-gold-soft">%s</span> ', esc_html( $ist_guide ) );
						} elseif ( '' !== $ist_before && '' !== $ist_after ) {
							echo ' ';
						}
						echo esc_html( $ist_after );
						?>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $ist_wa_label || '' !== $ist_phone ) : ?>
					<div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-line-light pt-7 text-sm text-ivory/70" data-reveal style="--reveal-y:18px;--reveal-delay:.2s">
						<?php if ( '' !== $ist_wa_label ) : ?>
							<a href="<?php echo esc_url( ist_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 transition-colors hover:text-gold">
								<?php echo esc_html( $ist_wa_label ); ?><span class="sr-only"> <?php esc_html_e( '(opens in a new tab)', 'interior-specifics' ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( '' !== $ist_wa_label && '' !== $ist_phone ) : ?>
							<span aria-hidden="true" class="h-3 w-px bg-line-light"></span>
						<?php endif; ?>
						<?php if ( '' !== $ist_phone ) : ?>
							<a href="<?php echo esc_url( ist_phone_href(), array( 'tel' ) ); ?>" class="inline-flex items-center gap-2 transition-colors hover:text-gold"><?php echo esc_html( $ist_phone ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="border border-line-light bg-white/[0.02] p-7 backdrop-blur-sm md:p-9" data-reveal style="--reveal-delay:.15s">
				<?php echo $ist_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by ist_lead_form(). ?>
			</div>
		</div>
	</div>
</section>
