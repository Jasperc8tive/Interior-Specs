<?php
/**
 * Numbered Grid block — the About "Our core values" section and the Book "What to Expect"
 * section (app/about/page.tsx, app/book/page.tsx): a centred RevealHeading above a hairline
 * grid whose numbered cells rise in turn and lighten on hover.
 *
 * 4 columns: sm:2 / lg:4, cells 80ms apart (About). 3 columns: md:3, cells 100ms apart (Book).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_items = array_values(
	array_filter(
		is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array(),
		static fn( $item ) => is_array( $item ) && ( '' !== trim( (string) ( $item['title'] ?? '' ) ) || '' !== trim( (string) ( $item['body'] ?? '' ) ) )
	)
);

$ist_three = 3 === (int) ( $attributes['columns'] ?? 4 );
$ist_white = 'white' === ( $attributes['background'] ?? 'ivory' );
$ist_title = trim( (string) ( $attributes['title'] ?? '' ) );

// Literal class names so Tailwind generates them.
$ist_grid_cols = $ist_three ? 'md:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-4';
$ist_step      = $ist_three ? 0.1 : 0.08;
$ist_body_gap  = $ist_three ? 'mt-2' : 'mt-3';
$ist_cell_bg   = $ist_white ? 'bg-white hover:bg-ivory' : 'bg-ivory hover:bg-white';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $ist_white ? 'bg-white py-24 md:py-32' : 'bg-ivory py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( '' !== $ist_title || '' !== (string) ( $attributes['eyebrow'] ?? '' ) ) : ?>
			<div data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow'     => (string) ( $attributes['eyebrow'] ?? '' ),
						'title'       => $ist_title,
						'intro'       => (string) ( $attributes['intro'] ?? '' ),
						'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
						'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
						// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
						'title_class' => 'max-w-3xl text-3xl leading-[1.12] md:text-4xl lg:text-[2.75rem]',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<?php if ( $ist_items ) : ?>
			<div class="<?php echo esc_attr( ist_classes( 'mt-16 grid gap-px overflow-hidden border border-line bg-line', $ist_grid_cols ) ); ?>">
				<?php foreach ( $ist_items as $ist_i => $ist_item ) : ?>
					<?php
					// The reveal lives on the grid cell; the hover lives on the inner panel so the
					// reveal transition never overrides its 300ms colour transition.
					$ist_delay = round( $ist_i * $ist_step, 2 );
					?>
					<div class="h-full" data-reveal="section"<?php echo $ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''; ?>>
						<div class="<?php echo esc_attr( ist_classes( 'group h-full p-8 transition-colors duration-300', $ist_cell_bg ) ); ?>">
							<span class="font-serif text-3xl text-gold/80 transition-colors duration-300 group-hover:text-gold"><?php echo esc_html( sprintf( '%02d', $ist_i + 1 ) ); ?></span>
							<?php if ( '' !== (string) ( $ist_item['title'] ?? '' ) ) : ?>
								<h3 class="mt-4 text-lg leading-[1.12] text-charcoal"><?php echo esc_html( (string) $ist_item['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== (string) ( $ist_item['body'] ?? '' ) ) : ?>
								<p class="<?php echo esc_attr( ist_classes( $ist_body_gap, 'text-sm text-muted' ) ); ?>"><?php echo esc_html( (string) $ist_item['body'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
