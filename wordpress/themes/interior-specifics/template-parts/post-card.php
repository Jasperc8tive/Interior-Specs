<?php
/**
 * Journal card (port of the cards in app/blog/page.tsx).
 *
 * Args:
 * - post    WP_Post|int  The post. Defaults to the current global post, so index.php can call
 *                        get_template_part( 'template-parts/post-card' ) inside the loop.
 * - index   int          Position in the grid; staggers the reveal by (index % 3) × 0.08s.
 * - variant string       'grid' (default) or 'featured' (the wide lg:grid-cols-2 lead card).
 * - heading string       Heading tag for grid cards: h2 (default) or h3 (when a featured h2 precedes).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'post'    => null,
		'index'   => 0,
		'variant' => 'grid',
		'heading' => 'h2',
	)
);

$ist_post = get_post( $args['post'] );
if ( ! $ist_post instanceof WP_Post ) {
	return;
}

$ist_id       = (int) $ist_post->ID;
$ist_title    = get_the_title( $ist_post );
$ist_link     = get_permalink( $ist_post );
$ist_excerpt  = wp_strip_all_tags( get_the_excerpt( $ist_post ) );
$ist_cats     = 'post' === $ist_post->post_type ? get_the_category( $ist_id ) : array();
$ist_category = $ist_cats ? $ist_cats[0]->name : '';
$ist_thumb    = (int) get_post_thumbnail_id( $ist_post );

if ( '' === $ist_category && 'post' !== $ist_post->post_type ) {
	// Other post types (index.php fallback) label the card with their type name instead.
	$ist_type     = get_post_type_object( $ist_post->post_type );
	$ist_category = $ist_type ? $ist_type->labels->singular_name : '';
}

// Charcoal outline: the cards sit on white, where gold is only 2.25:1.
$ist_focus = 'focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-charcoal';

if ( 'featured' === $args['variant'] ) :
	$ist_read_time = function_exists( 'isp_read_time' ) ? isp_read_time( $ist_id ) : '';
	$ist_meta      = implode( ' · ', array_filter( array( $ist_category, $ist_read_time ) ) );
	?>
	<div class="ist-post-card ist-post-card--featured" data-reveal="section">
		<a href="<?php echo esc_url( $ist_link ); ?>" class="<?php echo esc_attr( 'group grid overflow-hidden bg-ivory lg:grid-cols-2 ' . $ist_focus ); ?>">
			<div class="relative aspect-[16/10] overflow-hidden bg-ivory-dark lg:aspect-auto">
				<?php
				echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					$ist_thumb,
					'',
					'',
					'large',
					array(
						'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105',
						'sizes' => '(max-width: 1024px) 100vw, 50vw',
					)
				);
				?>
			</div>
			<div class="flex flex-col justify-center p-8 md:p-12">
				<?php if ( $ist_meta ) : ?>
					<span class="text-xs font-semibold uppercase tracking-[0.2em] text-gold"><?php echo esc_html( $ist_meta ); ?></span>
				<?php endif; ?>
				<h2 class="mt-3 text-2xl leading-[1.12] text-charcoal md:text-3xl"><?php echo esc_html( $ist_title ); ?></h2>
				<?php if ( $ist_excerpt ) : ?>
					<p class="mt-4 text-graysoft"><?php echo esc_html( $ist_excerpt ); ?></p>
				<?php endif; ?>
				<span class="mt-6 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal group-hover:text-gold"><?php esc_html_e( 'Read Article →', 'interior-specifics' ); ?></span>
			</div>
		</a>
	</div>
	<?php
	return;
endif;

$ist_heading = in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h2';
$ist_delay   = ( absint( $args['index'] ) % 3 ) * 0.08;
$ist_meta    = implode( ' · ', array_filter( array( $ist_category, get_the_date( 'j F Y', $ist_post ) ) ) );
?>
<div class="ist-post-card" data-reveal="section"<?php echo $ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''; ?>>
	<a href="<?php echo esc_url( $ist_link ); ?>" class="<?php echo esc_attr( 'group block ' . $ist_focus ); ?>">
		<div class="relative aspect-[16/10] overflow-hidden bg-ivory">
			<?php
			echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				$ist_thumb,
				'',
				'',
				'large', // Uncropped, so the 16:10 box crops the source once (as in Next) and srcset applies.
				array(
					'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105',
					'sizes' => '(max-width: 768px) 100vw, 33vw',
				)
			);
			?>
		</div>
		<?php if ( $ist_meta ) : ?>
			<span class="mt-4 inline-block text-xs font-semibold uppercase tracking-[0.15em] text-gold"><?php echo esc_html( $ist_meta ); ?></span>
		<?php endif; ?>
		<<?php echo esc_html( $ist_heading ); ?> class="mt-2 text-lg leading-[1.12] text-charcoal group-hover:text-gold"><?php echo esc_html( $ist_title ); ?></<?php echo esc_html( $ist_heading ); ?>>
		<?php if ( $ist_excerpt ) : ?>
			<p class="mt-2 text-sm text-graysoft"><?php echo esc_html( $ist_excerpt ); ?></p>
		<?php endif; ?>
	</a>
</div>
