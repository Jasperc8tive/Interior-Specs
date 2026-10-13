<?php
/**
 * Service card (port of the card in app/services/page.tsx): image push and overlay on hover,
 * title turning gold, the short description, and a "From ₦…" label whose gold line grows.
 *
 * Args:
 *   post    int|WP_Post  Service (defaults to the current post).
 *   index   int          Position in the grid; drives the reveal delay (index % 3) × 80ms.
 *   heading string       Heading tag for the title (h2 on the archive, h3 inside sections).
 *   reveal  bool         Wrap in a SectionReveal-style rise (default true).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'post'    => null,
		'index'   => 0,
		'heading' => 'h2',
		'reveal'  => true,
	)
);

$ist_service = get_post( $args['post'] );
if ( ! $ist_service instanceof WP_Post ) {
	return;
}

$ist_heading = in_array( $args['heading'], array( 'h2', 'h3', 'h4' ), true ) ? $args['heading'] : 'h2';
$ist_title   = get_the_title( $ist_service );
$ist_short   = has_excerpt( $ist_service )
	? get_the_excerpt( $ist_service )
	: wp_trim_words( wp_strip_all_tags( (string) $ist_service->post_content ), 18 );
$ist_from    = function_exists( 'isp_meta' ) ? (string) isp_meta( $ist_service->ID, 'investment_from' ) : (string) get_post_meta( $ist_service->ID, 'isp_investment_from', true );
$ist_delay   = ( absint( $args['index'] ) % 3 ) * 0.08;

if ( $args['reveal'] ) {
	printf(
		'<div data-reveal="section"%s>',
		$ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''
	);
}
?>
<a href="<?php echo esc_url( get_permalink( $ist_service ) ); ?>" class="group flex h-full flex-col overflow-hidden border border-line bg-white transition-colors duration-300 hover:border-gold/50">
	<div class="relative aspect-[16/10] overflow-hidden">
		<?php
		echo ist_post_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			$ist_service,
			'large', // Uncropped, so the 16:10 box crops the source once (as in Next) and srcset applies.
			array(
				'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105',
				'sizes' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
			)
		);
		?>
		<div class="absolute inset-0 bg-gradient-to-t from-charcoal/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
	</div>
	<div class="flex flex-1 flex-col p-7">
		<<?php echo esc_html( $ist_heading ); ?> class="text-xl text-charcoal transition-colors duration-300 group-hover:text-gold"><?php echo esc_html( $ist_title ); ?></<?php echo esc_html( $ist_heading ); ?>>
		<?php if ( '' !== $ist_short ) : ?>
			<p class="mt-3 flex-1 text-sm text-muted"><?php echo esc_html( $ist_short ); ?></p>
		<?php else : ?>
			<span class="flex-1"></span>
		<?php endif; ?>
		<span class="mt-6 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
			<?php
			if ( '' !== $ist_from ) {
				/* translators: %s: starting investment, e.g. ₦25M. */
				echo esc_html( sprintf( __( 'From %s', 'interior-specifics' ), $ist_from ) );
			} else {
				esc_html_e( 'Explore', 'interior-specifics' );
			}
			?>
			<span class="h-px w-6 bg-gold transition-all duration-300 group-hover:w-10" aria-hidden="true"></span>
		</span>
	</div>
</a>
<?php
if ( $args['reveal'] ) {
	echo '</div>';
}
