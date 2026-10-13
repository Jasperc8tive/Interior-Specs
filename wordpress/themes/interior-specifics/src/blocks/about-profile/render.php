<?php
/**
 * Company Profile block — the About page "#profile" section (app/about/page.tsx): a left-aligned
 * RevealHeading with story paragraphs beside a 4:3 image that drifts with scroll (Parallax,
 * distance 34), then the gold-ruled Mission / Vision cards.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_paragraphs = array_values(
	array_filter(
		array_map(
			static fn( $p ) => is_array( $p ) ? trim( (string) ( $p['text'] ?? '' ) ) : '',
			is_array( $attributes['paragraphs'] ?? null ) ? $attributes['paragraphs'] : array()
		),
		'strlen'
	)
);
$ist_cards      = array_values(
	array_filter(
		is_array( $attributes['cards'] ?? null ) ? $attributes['cards'] : array(),
		static fn( $card ) => is_array( $card ) && ( '' !== trim( (string) ( $card['title'] ?? '' ) ) || '' !== trim( (string) ( $card['body'] ?? '' ) ) )
	)
);
$ist_image      = ist_image(
	(int) ( $attributes['imageId'] ?? 0 ),
	(string) ( $attributes['imageUrl'] ?? '' ),
	(string) ( $attributes['imageAlt'] ?? '' ),
	'large',
	array(
		'class' => 'absolute inset-0 h-full w-full scale-110 object-cover',
		'sizes' => '(max-width: 1024px) 100vw, 50vw',
	)
);
$ist_anchor     = (string) ( $attributes['anchor'] ?? '' );
?>
<section 
<?php
echo get_block_wrapper_attributes(
	array(
		'class' => 'bg-white py-24 md:py-32',
		'id'    => '' !== $ist_anchor ? $ist_anchor : 'profile',
	)
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. 
?>
>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
			<div data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow'     => (string) ( $attributes['eyebrow'] ?? '' ),
						'title'       => (string) ( $attributes['title'] ?? '' ),
						'align'       => 'left',
						'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
						'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
						// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
						'title_class' => 'max-w-3xl text-3xl leading-[1.12] md:text-4xl lg:text-[2.75rem]',
					)
				);
				?>
				<?php if ( $ist_paragraphs ) : ?>
					<div class="mt-7 space-y-5 text-lg text-muted">
						<?php foreach ( $ist_paragraphs as $ist_paragraph ) : ?>
							<p><?php echo esc_html( $ist_paragraph ); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $ist_image ) : ?>
				<div class="relative aspect-[4/3] overflow-hidden" data-reveal="section" style="--reveal-delay:.1s">
					<div class="absolute inset-0">
						<div class="relative h-full w-full" data-parallax="34">
							<?php echo $ist_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $ist_cards ) : ?>
			<div class="mt-20 grid gap-6 md:grid-cols-2">
				<?php foreach ( $ist_cards as $ist_i => $ist_card ) : ?>
					<?php $ist_delay = round( $ist_i * 0.1, 2 ); ?>
					<div class="h-full" data-reveal="section"<?php echo $ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''; ?>>
						<div class="group h-full border-t-2 border-gold bg-ivory p-10 transition-colors duration-300 hover:bg-ivory-dark">
							<?php if ( '' !== (string) ( $ist_card['title'] ?? '' ) ) : ?>
								<h3 class="text-2xl leading-[1.12] text-charcoal"><?php echo esc_html( (string) $ist_card['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== (string) ( $ist_card['body'] ?? '' ) ) : ?>
								<p class="mt-4 text-muted"><?php echo esc_html( (string) $ist_card['body'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
