<?php
/**
 * Process Timeline block: the About page "How We Work" section (app/about/page.tsx) with
 * components/ProcessTimeline.tsx — a centred RevealHeading, then a row of hairline-separated
 * step cards that rise in turn (100ms apart) when the row scrolls into view.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_steps = array_values(
	array_filter(
		is_array( $attributes['steps'] ?? null ) ? $attributes['steps'] : array(),
		static fn( $step ) => is_array( $step ) && ( '' !== trim( (string) ( $step['title'] ?? '' ) ) || '' !== trim( (string) ( $step['detail'] ?? '' ) ) )
	)
);

// One row on desktop for up to six steps; literal class names so Tailwind generates them.
$ist_columns = array(
	1 => 'md:grid-cols-1',
	2 => 'md:grid-cols-2',
	3 => 'md:grid-cols-3',
	4 => 'md:grid-cols-4',
	5 => 'md:grid-cols-5',
	6 => 'md:grid-cols-6',
);
$ist_grid    = $ist_columns[ count( $ist_steps ) ] ?? 'md:grid-cols-3 lg:grid-cols-4';
$ist_heading = ist_reveal_heading(
	array(
		'eyebrow'     => (string) ( $attributes['eyebrow'] ?? '' ),
		'title'       => (string) ( $attributes['title'] ?? '' ),
		'intro'       => (string) ( $attributes['intro'] ?? '' ),
		'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
		'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
		// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
		'title_class' => 'max-w-3xl text-3xl leading-[1.12] md:text-4xl lg:text-[2.75rem]',
	)
);
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-white py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( '' !== trim( (string) ( $attributes['title'] ?? '' ) ) ) : ?>
			<div data-reveal="section">
				<?php echo $ist_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		<?php endif; ?>

		<?php if ( $ist_steps ) : ?>
			<div class="mt-16">
				<ol class="<?php echo esc_attr( ist_classes( 'grid gap-px overflow-hidden border border-line bg-line', $ist_grid ) ); ?>" data-reveal-stagger="0.1">
					<?php foreach ( $ist_steps as $ist_step ) : ?>
						<li class="h-full" data-reveal-item>
							<div class="group flex h-full flex-col gap-3 bg-ivory p-7 transition-colors duration-300 hover:bg-white">
								<span class="font-serif text-3xl text-gold"><?php echo esc_html( (string) ( $ist_step['n'] ?? '' ) ); ?></span>
								<h3 class="text-sm leading-[1.12] uppercase text-charcoal"><?php echo esc_html( (string) ( $ist_step['title'] ?? '' ) ); ?></h3>
								<p class="text-sm text-muted"><?php echo esc_html( (string) ( $ist_step['detail'] ?? '' ) ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		<?php endif; ?>
	</div>
</section>
