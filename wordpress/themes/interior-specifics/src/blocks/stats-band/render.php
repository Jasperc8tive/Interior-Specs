<?php
/**
 * Stats Band block (port of components/home/StatsBand.tsx + Counter.tsx).
 *
 * The final values are rendered server-side (no-JS, SEO, reduced motion all see real numbers);
 * src/js/modules/counters.js counts [data-count] numbers up from zero when they scroll into view
 * and fades [data-count-fade] text values in. The animated figure is hidden from assistive
 * technology, which reads a static copy instead, so screen readers never hear the count.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_stats = array();
foreach ( (array) ( $attributes['stats'] ?? array() ) as $ist_stat ) {
	if ( is_array( $ist_stat ) && ( '' !== trim( (string) ( $ist_stat['value'] ?? '' ) ) || '' !== trim( (string) ( $ist_stat['label'] ?? '' ) ) ) ) {
		$ist_stats[] = array(
			'value' => trim( (string) ( $ist_stat['value'] ?? '' ) ),
			'label' => trim( (string) ( $ist_stat['label'] ?? '' ) ),
		);
	}
}
if ( ! $ist_stats ) {
	$ist_stats = array(
		array(
			'value' => '15+',
			'label' => __( 'Years of practice', 'interior-specifics' ),
		),
		array(
			'value' => '200+',
			'label' => __( 'Projects delivered', 'interior-specifics' ),
		),
		array(
			'value' => '12',
			'label' => __( 'In-house disciplines', 'interior-specifics' ),
		),
		array(
			'value' => '100%',
			'label' => __( 'Turnkey delivery', 'interior-specifics' ),
		),
	);
}
$ist_stats = array_slice( $ist_stats, 0, 4 );

// Literal class names so Tailwind generates them.
$ist_cols = array(
	1 => 'grid-cols-1',
	2 => 'grid-cols-2',
	3 => 'grid-cols-2 sm:grid-cols-3',
	4 => 'grid-cols-2 sm:grid-cols-4',
);

$ist_wrapper = get_block_wrapper_attributes( array( 'class' => 'grain relative border-y border-line-light bg-charcoal py-16 md:py-20' ) );
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes(). ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<dl class="<?php echo esc_attr( ist_classes( 'grid gap-y-10', $ist_cols[ count( $ist_stats ) ] ) ); ?>" data-reveal-stagger="0.12" data-reveal-amount="0.3">
			<?php foreach ( $ist_stats as $ist_i => $ist_stat ) : ?>
				<?php
				// Same parse as Counter.tsx: optional non-digit prefix, a number (commas allowed), any suffix.
				$ist_numeric = (bool) preg_match( '/^(\D*)(\d[\d,]*)(.*)$/su', $ist_stat['value'], $ist_match );
				$ist_hook    = $ist_numeric ? sprintf( ' data-count="%s"', esc_attr( str_replace( ',', '', $ist_match[2] ) ) ) : ' data-count-fade';
				?>
				<div class="<?php echo esc_attr( ist_classes( 'px-2 text-center', $ist_i > 0 ? 'sm:border-l sm:border-line-light' : '' ) ); ?>" data-reveal-item>
					<dt class="sr-only"><?php echo esc_html( $ist_stat['label'] ); ?></dt>
					<dd>
						<span class="block font-serif text-4xl text-gold md:text-5xl lg:text-[3.25rem]" aria-hidden="true"<?php echo $ist_hook; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>><?php echo esc_html( $ist_stat['value'] ); ?></span>
						<span class="sr-only"><?php echo esc_html( $ist_stat['value'] ); ?></span>
						<span class="mt-3 block text-xs uppercase tracking-[0.18em] text-ivory/60" aria-hidden="true"><?php echo esc_html( $ist_stat['label'] ); ?></span>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
