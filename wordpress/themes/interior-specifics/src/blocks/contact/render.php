<?php
/**
 * Contact Section block (port of the body of app/contact/page.tsx).
 *
 * Left: RevealHeading + the contact form (ist_lead_form). Right: the "Get in touch" card built
 * from Settings → Interior Specifics, then the office map (a lazy Google Maps iframe once
 * map_embed_url is set, the original placeholder box until then). Below: the FAQ as native
 * <details>, so it opens with the keyboard and without JavaScript.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_email   = ist_setting( 'email' );
$ist_details = array(
	array(
		'label' => (string) $attributes['officeLabel'],
		'value' => ist_address(),
	),
	array(
		'label' => (string) $attributes['phoneLabel'],
		'value' => ist_setting( 'phone_display' ),
		'href'  => ist_phone_href(),
		'track' => 'phone',
	),
	array(
		'label' => (string) $attributes['emailLabel'],
		'value' => $ist_email,
		'href'  => 'mailto:' . $ist_email,
		'track' => 'email',
	),
	array(
		'label'    => (string) $attributes['whatsappLabel'],
		'value'    => (string) $attributes['whatsappText'],
		'href'     => ist_whatsapp_link(),
		'track'    => 'whatsapp',
		'external' => true,
	),
	array(
		'label' => (string) $attributes['hoursLabel'],
		'value' => ist_setting( 'hours' ),
	),
);
$ist_details = array_filter(
	$ist_details,
	static fn( $row ) => '' !== trim( $row['label'] ) && '' !== trim( $row['value'] )
);

$ist_map_url = ist_setting( 'map_embed_url' );
// Editors sometimes paste Google's whole <iframe> snippet; keep only its src.
if ( str_contains( $ist_map_url, '<iframe' ) && preg_match( '/src=["\']([^"\']+)["\']/i', $ist_map_url, $ist_match ) ) {
	$ist_map_url = html_entity_decode( $ist_match[1] );
}
$ist_map_label = (string) $attributes['mapLabel'];

$ist_faqs = array_filter(
	is_array( $attributes['faqs'] ) ? $attributes['faqs'] : array(),
	static fn( $faq ) => is_array( $faq ) && '' !== trim( (string) ( $faq['q'] ?? '' ) )
);

$ist_form = function_exists( 'ist_lead_form' )
	? ist_lead_form(
		'contact',
		array(
			'variant'       => 'light',
			'id'            => 'contact-form',
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
				<div class="mt-8">
					<?php echo $ist_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by ist_lead_form(). ?>
				</div>
			</div>

			<div class="lg:col-span-2" data-reveal="section" style="--reveal-delay:.1s">
				<div class="border-t-2 border-gold bg-ivory p-8">
					<?php if ( '' !== trim( (string) $attributes['cardTitle'] ) ) : ?>
						<h2 class="text-2xl text-charcoal"><?php echo esc_html( (string) $attributes['cardTitle'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $ist_details ) : ?>
						<dl class="mt-7 space-y-6 text-sm">
							<?php foreach ( $ist_details as $ist_row ) : ?>
								<div>
									<dt class="text-xs font-semibold uppercase tracking-[0.18em] text-gold"><?php echo esc_html( $ist_row['label'] ); ?></dt>
									<dd class="mt-1.5">
										<?php if ( ! empty( $ist_row['href'] ) ) : ?>
											<a href="<?php echo esc_url( $ist_row['href'], array( 'http', 'https', 'mailto', 'tel' ) ); ?>"<?php echo ! empty( $ist_row['external'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="text-charcoal transition-colors hover:text-gold" data-track="<?php echo esc_attr( $ist_row['track'] ); ?>">
												<?php echo esc_html( $ist_row['value'] ); ?>
												<?php
												if ( ! empty( $ist_row['external'] ) ) :
													?>
													<span class="sr-only"> <?php esc_html_e( '(opens in a new tab)', 'interior-specifics' ); ?></span><?php endif; ?>
											</a>
										<?php else : ?>
											<span class="text-muted"><?php echo esc_html( $ist_row['value'] ); ?></span>
										<?php endif; ?>
									</dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $attributes['showMap'] ) ) : ?>
					<?php if ( '' !== $ist_map_url ) : ?>
						<iframe
							src="<?php echo esc_url( $ist_map_url ); ?>"
							title="<?php echo esc_attr( '' !== $ist_map_label ? $ist_map_label : __( 'Office location map', 'interior-specifics' ) ); ?>"
							class="mt-6 block aspect-[4/3] w-full border border-line bg-ivory"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							allowfullscreen
						></iframe>
					<?php else : ?>
						<div class="mt-6 flex aspect-[4/3] items-center justify-center border border-line bg-ivory text-sm uppercase tracking-[0.2em] text-muted">
							<?php echo esc_html( $ist_map_label ); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $ist_faqs ) : ?>
			<div class="ist-faq mx-auto mt-24 max-w-3xl">
				<div data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => (string) $attributes['faqEyebrow'],
							'title'   => (string) $attributes['faqTitle'],
						)
					);
					?>
				</div>
				<div class="mt-10 divide-y divide-line border-y border-line">
					<?php foreach ( $ist_faqs as $ist_faq ) : ?>
						<details class="group py-6">
							<summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg text-charcoal transition-colors group-hover:text-gold">
								<?php echo esc_html( (string) $ist_faq['q'] ); ?>
								<span class="text-2xl font-light text-gold transition-transform duration-300 group-open:rotate-45" aria-hidden="true">+</span>
							</summary>
							<p class="mt-3 text-muted"><?php echo esc_html( (string) ( $ist_faq['a'] ?? '' ) ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
