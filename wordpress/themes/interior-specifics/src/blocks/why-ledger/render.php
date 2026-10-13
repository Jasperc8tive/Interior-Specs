<?php
/**
 * Why Ledger block (port of components/home/WhyLedger.tsx): reasons to trust the house,
 * presented as a full-width ledger of hairline rows that warm to gold on hover. Rows rise in
 * one after another (100ms apart) when the list scrolls into view.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_rows = array_values(
	array_filter(
		is_array( $attributes['rows'] ?? null ) ? $attributes['rows'] : array(),
		static fn( $row ) => is_array( $row ) && ( '' !== trim( (string) ( $row['title'] ?? '' ) ) || '' !== trim( (string) ( $row['body'] ?? '' ) ) )
	)
);
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-ivory py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="max-w-2xl">
			<?php
			echo ist_eyebrow( (string) ( $attributes['eyebrow'] ?? '' ), 'text-gold', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'        => (string) ( $attributes['title'] ?? '' ),
					'class'       => 'mt-6 text-3xl leading-[1.12] text-charcoal sm:text-4xl lg:text-[2.75rem]',
					'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
					'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
				)
			);
			?>
		</div>

		<?php if ( $ist_rows ) : ?>
			<ul class="mt-14 border-t border-line" data-reveal-stagger="0.1">
				<?php foreach ( $ist_rows as $ist_i => $ist_row ) : ?>
					<li class="border-b border-line" data-reveal-item>
						<div class="group grid items-baseline gap-x-8 gap-y-3 py-9 transition-colors duration-300 md:grid-cols-[6rem_1fr_1.4fr] md:py-10">
							<span class="font-serif text-3xl text-gold/70 tabular-nums transition-colors duration-300 group-hover:text-gold md:text-4xl" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $ist_i + 1 ) ); ?></span>
							<h3 class="text-xl leading-[1.12] text-charcoal transition-colors duration-300 group-hover:text-gold md:text-2xl"><?php echo esc_html( (string) ( $ist_row['title'] ?? '' ) ); ?></h3>
							<p class="text-muted md:pl-6"><?php echo esc_html( (string) ( $ist_row['body'] ?? '' ) ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
