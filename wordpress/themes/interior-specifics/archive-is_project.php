<?php
/**
 * Showroom archive, /showroom/ (port of app/showroom/page.tsx).
 *
 * Hero, the filterable case-study grid (every project is rendered; ?type=<slug> or a
 * /showroom/type/<slug>/ term page pre-filters it on the server, src/js/modules/showroom-filter.js
 * filters on click), the showroom visit band and the closing call to action.
 * taxonomy-is_project_type.php loads this template with that term preselected.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_types   = function_exists( 'isp_project_types' ) ? isp_project_types() : array();
$ist_archive = get_post_type_archive_link( 'is_project' );
$ist_archive = $ist_archive ? $ist_archive : home_url( '/showroom/' );

// Preselected type: the term of a /showroom/type/<slug>/ page, overridden by ?type=<slug>.
$ist_active = '';
$ist_term   = is_tax( 'is_project_type' ) ? get_queried_object() : null;
if ( $ist_term instanceof WP_Term ) {
	$ist_active = $ist_term->slug;
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display filter: it only chooses which cards start visible and changes no state, so no nonce is needed.
$ist_param = isset( $_GET['type'] ) && is_string( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
if ( '' !== $ist_param ) {
	$ist_active = $ist_param;
}
if ( ! isset( $ist_types[ $ist_active ] ) ) {
	$ist_active = '';
}

add_filter(
	'document_title_parts',
	static function ( array $parts ) use ( $ist_term ): array {
		$parts['title'] = $ist_term instanceof WP_Term
			/* translators: %s: project type, e.g. Residential. */
			? sprintf( __( '%s Interiors — Showroom', 'interior-specifics' ), $ist_term->name )
			: __( 'Showroom — Our Portfolio of Luxury Interiors', 'interior-specifics' );
		return $parts;
	}
);

// Every project, in editor order; filtering is client-side so all cards are in the page.
$ist_projects = new WP_Query(
	array(
		'post_type'           => 'is_project',
		'post_status'         => 'publish',
		'posts_per_page'      => 100,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);
update_post_thumbnail_cache( $ist_projects ); // Every card image in two queries, not two per card.

// Per-type counts for the live-region announcement.
$ist_counts = array( 'all' => count( $ist_projects->posts ) );
foreach ( $ist_projects->posts as $ist_post ) {
	$ist_post_type_term = function_exists( 'isp_project_type' ) ? isp_project_type( $ist_post->ID ) : null;
	if ( $ist_post_type_term ) {
		$ist_counts[ $ist_post_type_term->slug ] = ( $ist_counts[ $ist_post_type_term->slug ] ?? 0 ) + 1;
	}
}
$ist_shown = '' === $ist_active ? $ist_counts['all'] : ( $ist_counts[ $ist_active ] ?? 0 );

$ist_filters = array( 'all' => __( 'All', 'interior-specifics' ) ) + $ist_types;

get_header();

ist_page_hero(
	/**
	 * Filters the showroom archive hero. The image is chosen under Settings → Interior
	 * Specifics → Page heroes.
	 *
	 * @param array<string, mixed> $args ist_page_hero() args.
	 */
	apply_filters(
		'ist_showroom_archive_hero',
		array(
			'eyebrow'     => __( 'Showroom & Portfolio', 'interior-specifics' ),
			'title'       => __( 'Spaces that speak of achievement', 'interior-specifics' ),
			'accent_from' => 3,
			'accent_to'   => 4,
			'intro'       => __( 'Explore our completed work and visit our Lagos showroom to discover curated furniture, fabrics and finishes.', 'interior-specifics' ),
			'image_id'    => ist_page_hero_image_id( 'showroom' ),
			'image_url'   => '',
			'image_alt'   => __( 'Curated luxury showroom interior', 'interior-specifics' ),
		)
	)
);
?>

<section class="bg-ivory py-24 md:py-32">
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div data-reveal="section">
			<?php
			echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'eyebrow' => __( 'Case Studies', 'interior-specifics' ),
					'title'   => __( 'Our recent projects', 'interior-specifics' ),
					'intro'   => __( 'Filter by discipline, then open any project to see the full story behind it.', 'interior-specifics' ),
				)
			);
			?>
		</div>

		<div class="mt-16" data-showroom-filter data-base-url="<?php echo esc_url( $ist_archive ); ?>" data-initial="<?php echo esc_attr( $ist_active ? $ist_active : 'all' ); ?>">
			<form method="get" action="<?php echo esc_url( $ist_archive ); ?>">
				<div class="flex flex-wrap justify-center gap-3" role="group" aria-label="<?php esc_attr_e( 'Filter projects by type', 'interior-specifics' ); ?>">
					<?php
					foreach ( $ist_filters as $ist_slug => $ist_name ) :
						$ist_count   = $ist_counts[ $ist_slug ] ?? 0;
						$ist_pressed = ( 'all' === $ist_slug && '' === $ist_active ) || $ist_slug === $ist_active;
						/* translators: 1: filter name (All, Residential...), 2: number of projects. */
						$ist_status = sprintf( _n( '%1$s: %2$d project', '%1$s: %2$d projects', $ist_count, 'interior-specifics' ), $ist_name, $ist_count );
						?>
						<button
							type="submit"
							<?php if ( 'all' !== $ist_slug ) : ?>
								name="type" value="<?php echo esc_attr( $ist_slug ); ?>"
							<?php endif; ?>
							class="min-h-11 cursor-pointer px-6 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition-colors duration-300 aria-pressed:bg-charcoal aria-pressed:text-ivory not-aria-pressed:border not-aria-pressed:border-line not-aria-pressed:text-muted not-aria-pressed:hover:border-gold not-aria-pressed:hover:text-charcoal focus:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2 focus-visible:ring-offset-ivory"
							aria-pressed="<?php echo $ist_pressed ? 'true' : 'false'; ?>"
							data-filter="<?php echo esc_attr( $ist_slug ); ?>"
							data-status="<?php echo esc_attr( $ist_status ); ?>"
						><?php echo esc_html( $ist_name ); ?></button>
					<?php endforeach; ?>
				</div>
			</form>
			<p class="screen-reader-text" role="status" data-showroom-status></p>

			<div class="mt-12">
				<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-showroom-grid>
					<?php
					foreach ( $ist_projects->posts as $ist_index => $ist_post ) {
						$ist_post_type_term = function_exists( 'isp_project_type' ) ? isp_project_type( $ist_post->ID ) : null;
						get_template_part(
							'template-parts/portfolio-card',
							null,
							array(
								'post'    => $ist_post,
								'index'   => $ist_index,
								'heading' => 'h3',
								// Static, as in Next's PortfolioGrid: no rise on scroll or when a filter shows a card.
								'reveal'  => false,
								'hidden'  => '' !== $ist_active && ( ! $ist_post_type_term || $ist_post_type_term->slug !== $ist_active ),
							)
						);
					}
					wp_reset_postdata();
					?>
				</div>
				<p class="py-16 text-center text-muted" data-showroom-empty<?php echo $ist_shown ? ' hidden' : ''; ?>><?php esc_html_e( 'New case studies in this category are on their way. In the meantime, explore our other work.', 'interior-specifics' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="grain relative bg-charcoal py-24 md:py-32">
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
			<div data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow' => __( 'Visit Us', 'interior-specifics' ),
						'title'   => __( 'Book a private showroom tour', 'interior-specifics' ),
						'align'   => 'left',
						'light'   => true,
					)
				);
				?>
				<p class="mt-7 text-lg text-ivory/75"><?php esc_html_e( 'Step inside our Lagos showroom to explore curated collections of furniture, lighting, textiles and art — and discuss your project with our team in person.', 'interior-specifics' ); ?></p>
				<?php
				$ist_street = implode( ', ', array_filter( array( ist_setting( 'address_street' ), ist_setting( 'address_city' ) ) ) );
				$ist_hours  = ist_setting( 'hours' );
				if ( $ist_street || $ist_hours ) :
					?>
					<ul class="mt-7 space-y-3 border-t border-line-light pt-7 text-sm text-ivory/70">
						<?php if ( $ist_street ) : ?>
							<li><?php echo esc_html( $ist_street ); ?></li>
						<?php endif; ?>
						<?php if ( $ist_hours ) : ?>
							<li><?php echo esc_html( $ist_hours ); ?></li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
				<div class="mt-8 flex flex-col gap-4 sm:flex-row">
					<?php
					echo ist_button( __( 'Book a Showroom Tour', 'interior-specifics' ), ist_booking_url(), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					echo ist_button( __( 'Schedule a Virtual Consultation', 'interior-specifics' ), '/contact/', 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					?>
				</div>
			</div>
			<?php
			$ist_map = ist_setting( 'map_embed_url' );
			if ( $ist_map && str_starts_with( $ist_map, 'https://' ) ) :
				?>
				<div class="relative aspect-[4/3] overflow-hidden border border-line-light bg-charcoal-light" data-reveal="section" style="--reveal-delay:.1s">
					<iframe class="absolute inset-0 h-full w-full border-0" src="<?php echo esc_url( $ist_map ); ?>" title="<?php esc_attr_e( 'Map of our Lagos showroom', 'interior-specifics' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				</div>
			<?php else : ?>
				<div class="flex aspect-[4/3] items-center justify-center border border-line-light bg-charcoal-light text-sm uppercase tracking-[0.2em] text-ivory/40" data-reveal="section" style="--reveal-delay:.1s">
					<?php esc_html_e( 'Map · Lagos Showroom', 'interior-specifics' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
ist_cta_section();

get_footer();
