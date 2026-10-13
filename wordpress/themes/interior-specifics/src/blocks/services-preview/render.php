<?php
/**
 * Services Preview block (port of components/home/ServicesPreview.tsx).
 *
 * Restrained cards with a slow image push and a gold captioned reveal on hover. Cards are the
 * featured is_service posts (menu order) or a hand-picked list; they rise in with a 0.1s stagger.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_count      = max( 1, min( 12, (int) $attributes['count'] ) );
$ist_query_args = array(
	'post_type'           => 'is_service',
	'post_status'         => 'publish',
	'posts_per_page'      => $ist_count,
	'orderby'             => array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
	),
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);

$ist_ids   = array_values( array_filter( array_map( 'absint', (array) $attributes['serviceIds'] ) ) );
$ist_query = null;
if ( 'selected' === $attributes['source'] && $ist_ids ) {
	$ist_query = new WP_Query(
		array_merge(
			$ist_query_args,
			array(
				'post__in'       => $ist_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $ist_ids ),
			)
		)
	);
}
// Featured services, also used when none of the picked services exist any more.
if ( ! $ist_query || ! $ist_query->have_posts() ) {
	$ist_query = new WP_Query(
		array_merge(
			$ist_query_args,
			array(
				'meta_key'   => 'isp_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small, cached CPT query.
				'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- small, cached CPT query.
			)
		)
	);
	// Nothing flagged as featured yet: fall back to the first services in menu order.
	if ( ! $ist_query->have_posts() ) {
		$ist_query = new WP_Query( $ist_query_args );
	}
}
update_post_thumbnail_cache( $ist_query ); // All card images in two queries, not two per card.

$ist_link_label = (string) $attributes['cardLinkLabel'];
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-ivory py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="max-w-2xl">
			<?php
			echo ist_eyebrow( (string) $attributes['eyebrow'], 'text-gold', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'        => (string) $attributes['title'],
					'class'       => 'mt-6 text-3xl leading-[1.12] text-charcoal sm:text-4xl lg:text-[2.75rem]',
					'accent_from' => (int) $attributes['accentFrom'],
					'accent_to'   => (int) $attributes['accentTo'],
				)
			);
			?>
			<?php if ( '' !== (string) $attributes['intro'] ) : ?>
				<p class="mt-6 text-lg text-muted" data-reveal style="--reveal-y:16px;--reveal-delay:.15s;transition-duration:.8s"><?php echo esc_html( (string) $attributes['intro'] ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $ist_query->have_posts() ) : ?>
			<div class="mt-14 grid gap-6 md:grid-cols-3" data-reveal-stagger="0.1">
				<?php
				foreach ( $ist_query->posts as $ist_service ) :
					$ist_short = has_excerpt( $ist_service )
						? (string) $ist_service->post_excerpt
						: wp_trim_words( wp_strip_all_tags( (string) $ist_service->post_content ), 18 );
					?>
					<div data-reveal-item>
						<a href="<?php echo esc_url( get_permalink( $ist_service ) ); ?>" class="group block h-full overflow-hidden border border-line bg-white transition-colors duration-300 hover:border-gold/50">
							<div class="relative aspect-[16/11] overflow-hidden">
								<?php
								echo ist_post_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
									$ist_service,
									'large', // Uncropped: the 16:11 box crops the source once, as in Next.
									array(
										'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105',
										'sizes' => '(max-width: 768px) 100vw, 33vw',
									)
								);
								?>
								<div class="absolute inset-0 bg-gradient-to-t from-charcoal/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
							</div>
							<div class="p-7">
								<h3 class="text-xl leading-[1.12] text-charcoal transition-colors duration-300 group-hover:text-gold"><?php echo esc_html( get_the_title( $ist_service ) ); ?></h3>
								<?php if ( '' !== $ist_short ) : ?>
									<p class="mt-3 text-sm text-muted"><?php echo esc_html( $ist_short ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $ist_link_label ) : ?>
									<span class="mt-5 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
										<?php echo esc_html( $ist_link_label ); ?>
										<span class="h-px w-6 bg-gold transition-all duration-300 group-hover:w-10" aria-hidden="true"></span>
									</span>
								<?php endif; ?>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>

		<?php if ( '' !== (string) $attributes['buttonLabel'] ) : ?>
			<div class="mt-14 text-center">
				<?php echo ist_button( (string) $attributes['buttonLabel'], (string) $attributes['buttonUrl'], 'dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		<?php endif; ?>
	</div>
</section>
