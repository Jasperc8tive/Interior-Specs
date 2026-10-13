<?php
/**
 * Awards & Clients block — the About page "#awards" section (app/about/page.tsx): a charcoal
 * grain canvas with a light RevealHeading, a hairline grid of awards that rise 60ms apart, and
 * the closing clients line (p#clients, the "Clients & Case Studies" menu target).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_awards  = array_values(
	array_filter(
		array_map(
			static fn( $item ) => is_array( $item ) ? trim( (string) ( $item['text'] ?? '' ) ) : '',
			is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array()
		),
		'strlen'
	)
);
$ist_title   = trim( (string) ( $attributes['title'] ?? '' ) );
$ist_clients = trim( (string) ( $attributes['clientsLine'] ?? '' ) );
$ist_anchor  = (string) ( $attributes['anchor'] ?? '' );
?>
<section 
<?php
echo get_block_wrapper_attributes(
	array(
		'class' => 'grain relative bg-charcoal py-24 md:py-32',
		'id'    => '' !== $ist_anchor ? $ist_anchor : 'awards',
	)
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. 
?>
>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( '' !== $ist_title || '' !== (string) ( $attributes['eyebrow'] ?? '' ) ) : ?>
			<div data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow'     => (string) ( $attributes['eyebrow'] ?? '' ),
						'title'       => $ist_title,
						'intro'       => (string) ( $attributes['intro'] ?? '' ),
						'light'       => true,
						'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
						'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
						// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
						'title_class' => 'max-w-3xl text-3xl leading-[1.12] md:text-4xl lg:text-[2.75rem]',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<?php if ( $ist_awards ) : ?>
			<ul class="mx-auto mt-14 grid max-w-3xl gap-px overflow-hidden border border-line-light bg-line-light sm:grid-cols-2">
				<?php foreach ( $ist_awards as $ist_i => $ist_award ) : ?>
					<?php
					// Reveal on the grid cell; the hover lives on the inner panel so the reveal
					// transition never overrides its 300ms colour transition.
					$ist_delay = round( $ist_i * 0.06, 2 );
					?>
					<li class="h-full" data-reveal="section"<?php echo $ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''; ?>>
						<div class="group flex h-full items-center bg-charcoal p-7 transition-colors duration-300 hover:bg-charcoal-light">
							<p class="text-sm text-ivory/80"><?php echo esc_html( $ist_award ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( '' !== $ist_clients ) : ?>
			<p id="clients" class="mx-auto mt-12 max-w-2xl text-center text-ivory/60"><?php echo esc_html( $ist_clients ); ?></p>
		<?php endif; ?>
	</div>
</section>
