<?php
/**
 * Project card (port of the components/PortfolioGrid.tsx card, also used by the FeaturedWork
 * block). A slow image push, a growing gold underline and a drawer with the blurb and the
 * investment range. The drawer opens on hover, on keyboard focus and is always open on touch
 * devices (hover: none) — see src/css/parts/projects.css.
 *
 * Args:
 *   post        WP_Post|int  Project (required).
 *   index       int          Position in its grid; drives the reveal stagger delay.
 *   heading     string       h2|h3 (default h3).
 *   reveal      bool         Rise into view on scroll (default true). Pass false when a parent
 *                            already animates the card (e.g. a [data-reveal-stagger] wrapper).
 *   hidden      bool         Render hidden (server-side ?type= filtering on the showroom).
 *   aspect      string       Aspect classes (default aspect-[4/5]).
 *   padding     string       Bottom panel padding classes (default p-6).
 *   title_class string       Heading classes (default mt-2 text-xl text-ivory).
 *   sizes       string       sizes attribute for the image.
 *   ring        string       Focus ring colour class (default focus-visible:ring-charcoal for light
 *                            sections; pass focus-visible:ring-gold on dark ones, where gold is 7:1+).
 *   ring_offset string       Focus ring offset colour class (default focus-visible:ring-offset-ivory).
 *   class       string       Extra link classes.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'post'        => 0,
		'index'       => 0,
		'heading'     => 'h3',
		'reveal'      => true,
		'hidden'      => false,
		'aspect'      => 'aspect-[4/5]',
		'padding'     => 'p-6',
		'title_class' => 'mt-2 text-xl text-ivory',
		'sizes'       => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
		'ring'        => 'focus-visible:ring-charcoal',
		'ring_offset' => 'focus-visible:ring-offset-ivory',
		'class'       => '',
	)
);

$ist_project = get_post( $args['post'] );
if ( ! $ist_project instanceof WP_Post || 'is_project' !== $ist_project->post_type ) {
	return;
}

$ist_id       = (int) $ist_project->ID;
$ist_title    = get_the_title( $ist_project );
$ist_type     = function_exists( 'isp_project_type' ) ? isp_project_type( $ist_id ) : null;
$ist_location = function_exists( 'isp_meta' ) ? (string) isp_meta( $ist_id, 'location' ) : '';
$ist_budget   = function_exists( 'isp_meta' ) ? (string) isp_meta( $ist_id, 'budget' ) : '';
$ist_blurb    = has_excerpt( $ist_project ) ? (string) $ist_project->post_excerpt : '';
$ist_tag      = in_array( $args['heading'], array( 'h2', 'h3', 'h4' ), true ) ? $args['heading'] : 'h3';
$ist_kicker   = implode( ' · ', array_filter( array( $ist_type ? $ist_type->name : '', $ist_location ) ) );

$ist_alt = $ist_location
	/* translators: 1: project title, 2: project type (e.g. Residential), 3: location. */
	? sprintf( __( '%1$s — %2$s interior design in %3$s', 'interior-specifics' ), $ist_title, $ist_type ? $ist_type->name : __( 'Luxury', 'interior-specifics' ), $ist_location )
	: $ist_title;

$ist_link_attrs = array(
	'href'              => get_permalink( $ist_project ),
	'class'             => ist_classes(
		'ist-card group relative block overflow-hidden bg-charcoal focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
		$args['ring'],
		$args['ring_offset'],
		$args['aspect'],
		$args['class']
	),
	'data-project-type' => $ist_type ? $ist_type->slug : '',
);
?>
<a
	<?php
	foreach ( $ist_link_attrs as $ist_attr => $ist_value ) {
		printf( '%s="%s" ', esc_attr( $ist_attr ), 'href' === $ist_attr ? esc_url( $ist_value ) : esc_attr( $ist_value ) );
	}
	if ( $args['reveal'] ) {
		printf( 'data-reveal="rise" style="--reveal-delay:%ss" ', esc_attr( (string) ( ( (int) $args['index'] % 3 ) * 0.08 ) ) );
	}
	if ( $args['hidden'] ) {
		echo 'hidden ';
	}
	?>
>
	<?php
	echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		(int) get_post_thumbnail_id( $ist_project ),
		'',
		$ist_alt,
		'large',
		array(
			'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06] group-focus-visible:scale-[1.06]',
			'sizes' => $args['sizes'],
		)
	);
	?>
	<div class="absolute inset-0 bg-gradient-to-t from-onyx/90 via-onyx/25 to-transparent"></div>

	<div class="<?php echo esc_attr( ist_classes( 'absolute inset-x-0 bottom-0', $args['padding'] ) ); ?>">
		<?php if ( $ist_kicker ) : ?>
			<span class="block text-xs font-semibold uppercase tracking-[0.2em] text-gold"><?php echo esc_html( $ist_kicker ); ?></span>
		<?php endif; ?>
		<<?php echo esc_html( $ist_tag ); ?> class="<?php echo esc_attr( $args['title_class'] ); ?>">
			<span class="inline-block">
				<?php echo esc_html( $ist_title ); ?>
				<span class="ist-card-underline mt-1 block h-px w-0 bg-gold transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:w-full group-focus-visible:w-full"></span>
			</span>
		</<?php echo esc_html( $ist_tag ); ?>>

		<div class="ist-card-drawer grid grid-rows-[0fr] opacity-0 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:grid-rows-[1fr] group-hover:opacity-100 group-focus-visible:grid-rows-[1fr] group-focus-visible:opacity-100">
			<div class="overflow-hidden">
				<?php if ( $ist_blurb ) : ?>
					<p class="pt-3 text-sm text-ivory/80"><?php echo esc_html( $ist_blurb ); ?></p>
				<?php endif; ?>
				<?php if ( $ist_budget ) : ?>
					<p class="mt-2 text-sm font-semibold text-gold-soft">
						<?php
						/* translators: %s: budget range, e.g. ₦80M – ₦120M. */
						echo esc_html( sprintf( __( 'Investment: %s', 'interior-specifics' ), $ist_budget ) );
						?>
					</p>
				<?php endif; ?>
				<span class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
					<?php esc_html_e( 'View Case Study', 'interior-specifics' ); ?>
					<svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none stroke-current stroke-2" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
			</div>
		</div>
	</div>
</a>
