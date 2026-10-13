<?php
/**
 * Featured Work block (port of components/home/FeaturedWork.tsx).
 *
 * Magazine-style portfolio: an asymmetric lead pair, a supporting trio and a cinematic
 * full-width closer. Cards reveal the investment range on hover / focus (always on touch).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_query_args = array(
	'post_type'           => 'is_project',
	'post_status'         => 'publish',
	'posts_per_page'      => 6,
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);

$ist_selected = array_values( array_filter( array_map( 'absint', (array) ( $attributes['projectIds'] ?? array() ) ) ) );

if ( 'selected' === ( $attributes['source'] ?? 'featured' ) && $ist_selected ) {
	$ist_query = new WP_Query(
		$ist_query_args + array(
			'post__in' => $ist_selected,
			'orderby'  => 'post__in',
		)
	);
} else {
	$ist_order = array(
		'menu_order' => 'ASC',
		'date'       => 'DESC',
	);
	$ist_query = new WP_Query(
		$ist_query_args + array(
			'orderby'    => $ist_order,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- at most 6 rows, cached by the page cache.
			'meta_query' => array(
				array(
					'key'   => 'isp_featured',
					'value' => '1',
				),
			),
		)
	);
	// Nothing flagged as featured yet: show the first projects instead of an empty section.
	if ( ! $ist_query->have_posts() ) {
		$ist_query = new WP_Query( $ist_query_args + array( 'orderby' => $ist_order ) );
	}
}

$ist_projects = $ist_query->posts;
update_post_thumbnail_cache( $ist_query ); // All card images in two queries, not two per card.
wp_reset_postdata();

if ( ! $ist_projects ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		printf(
			'<p style="padding:2rem;text-align:center">%s</p>',
			esc_html__( 'Featured Work: publish some projects under Showroom to fill this section.', 'interior-specifics' )
		);
	}
	return;
}

$ist_lead   = array_slice( $ist_projects, 0, 2 );
$ist_trio   = array_slice( $ist_projects, 2, 3 );
$ist_closer = $ist_projects[5] ?? null;

$ist_card = static function ( WP_Post $project, string $wrapper_class, string $aspect, bool $wide = false ): void {
	printf( '<div data-reveal-item class="%s">', esc_attr( $wrapper_class ) );
	get_template_part(
		'template-parts/portfolio-card',
		null,
		array(
			'post'        => $project,
			'heading'     => 'h3',
			'reveal'      => false,
			'aspect'      => $aspect,
			'class'       => 'w-full',
			'ring'        => 'focus-visible:ring-gold',
			'ring_offset' => 'focus-visible:ring-offset-onyx',
			'padding'     => $wide ? 'p-7 md:p-10' : 'p-7',
			'title_class' => 'mt-2 text-xl text-ivory md:text-2xl',
			'sizes'       => $wide ? '100vw' : '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 40vw',
		)
	);
	echo '</div>';
};

$ist_anchor  = ! empty( $attributes['anchor'] ) ? (string) $attributes['anchor'] : 'portfolio';
$ist_wrapper = get_block_wrapper_attributes(
	array(
		'id'    => $ist_anchor,
		'class' => 'grain relative bg-onyx py-24 md:py-32',
	)
);
$ist_link    = (string) ( $attributes['linkUrl'] ?? '' );
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes. ?>>
	<div class="<?php echo esc_attr( ist_container_class( 'relative' ) ); ?>">
		<div class="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
			<div class="max-w-2xl">
				<?php
				echo ist_eyebrow( (string) $attributes['eyebrow'], 'text-gold-soft', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'text'        => (string) $attributes['title'],
						'class'       => 'mt-6 text-3xl text-ivory sm:text-4xl lg:text-[2.75rem]',
						'accent_from' => (int) $attributes['accentFrom'],
						'accent_to'   => (int) $attributes['accentTo'],
					)
				);
				?>
			</div>
			<?php if ( '' !== (string) $attributes['linkLabel'] && '' !== $ist_link ) : ?>
				<div data-reveal style="--reveal-y:16px">
					<a <?php echo ist_link_attrs( $ist_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> class="group inline-flex items-center gap-3 text-sm font-medium uppercase tracking-[0.18em] text-ivory/80 transition-colors hover:text-gold focus:outline-none focus-visible:text-gold">
						<?php echo esc_html( (string) $attributes['linkLabel'] ); ?>
						<span class="h-px w-10 bg-gold transition-all duration-300 group-hover:w-16 group-focus-visible:w-16" aria-hidden="true"></span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="mt-14 flex flex-col gap-5 md:mt-16" data-reveal-stagger="0.1">
			<div class="grid gap-5 md:grid-cols-12">
				<?php
				$ist_lead_spans = 2 === count( $ist_lead ) ? array( 'md:col-span-7', 'md:col-span-5' ) : array( 'md:col-span-12' );
				foreach ( $ist_lead as $ist_i => $ist_project ) {
					$ist_card( $ist_project, $ist_lead_spans[ $ist_i ], 'aspect-[4/3] md:aspect-[16/12]' );
				}
				?>
			</div>
			<?php if ( $ist_trio ) : ?>
				<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
					<?php
					$ist_trio_count = count( $ist_trio );
					foreach ( $ist_trio as $ist_i => $ist_project ) {
						if ( 1 === $ist_trio_count ) {
							$ist_card( $ist_project, 'sm:col-span-2 lg:col-span-3', 'aspect-[4/5] sm:aspect-[16/10] md:aspect-[21/9]', true );
						} else {
							$ist_card( $ist_project, 2 === $ist_i ? 'sm:col-span-2 lg:col-span-1' : '', 'aspect-[4/5]' );
						}
					}
					?>
				</div>
			<?php endif; ?>
			<?php
			if ( $ist_closer ) {
				$ist_card( $ist_closer, '', 'aspect-[16/10] md:aspect-[21/9]', true );
			}
			?>
		</div>
	</div>
</section>
