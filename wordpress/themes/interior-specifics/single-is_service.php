<?php
/**
 * Single service, /services/{slug}/ (port of app/services/[slug]/page.tsx): hero, overview with
 * a sticky investment card, the process grid, related projects, FAQs and the closing CTA.
 *
 * Data: title, excerpt (short), content (overview paragraphs), featured image, and the Core
 * plugin's isp_investment_from, isp_process, isp_faqs and isp_related_projects meta.
 * JSON-LD is printed by the SEO layer, not here.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$ist_id      = get_the_ID();
	$ist_title   = get_the_title();
	$ist_meta    = static function ( string $key ) use ( $ist_id ) {
		return function_exists( 'isp_meta' ) ? isp_meta( $ist_id, $key ) : get_post_meta( $ist_id, 'isp_' . $key, true );
	};
	$ist_from    = (string) $ist_meta( 'investment_from' );
	$ist_process = array_values( array_filter( (array) $ist_meta( 'process' ), 'is_array' ) );
	$ist_faqs    = array_values( array_filter( (array) $ist_meta( 'faqs' ), 'is_array' ) );

	// Related projects: chosen in the editor, else the first three by menu order (as in Next).
	$ist_related_ids = array_filter( array_map( 'absint', (array) $ist_meta( 'related_projects' ) ) );
	$ist_related     = new WP_Query(
		$ist_related_ids
			? array(
				'post_type'           => 'is_project',
				'post_status'         => 'publish',
				'post__in'            => $ist_related_ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $ist_related_ids ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
			: array(
				'post_type'           => 'is_project',
				'post_status'         => 'publish',
				'orderby'             => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
	);
	update_post_thumbnail_cache( $ist_related );

	ist_page_hero(
		array(
			'eyebrow'   => __( 'Service', 'interior-specifics' ),
			'title'     => $ist_title,
			'intro'     => has_excerpt() ? get_the_excerpt() : '',
			'image_id'  => (int) get_post_thumbnail_id(),
			'image_url' => '',
			'image_alt' => $ist_title,
		)
	);
	?>

	<section class="bg-white py-24 md:py-32">
		<div class="<?php echo esc_attr( ist_container_class() ); ?>">
			<div class="grid gap-12 lg:grid-cols-3 lg:gap-16">
				<div class="lg:col-span-2" data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => __( 'Overview', 'interior-specifics' ),
							'title'   => __( 'What this service includes', 'interior-specifics' ),
							'align'   => 'left',
						)
					);
					?>
					<div class="ist-service-overview mt-7 space-y-5 text-lg text-muted">
						<?php the_content(); ?>
					</div>
				</div>

				<aside class="h-fit border-t-2 border-gold bg-ivory p-8 lg:sticky lg:top-28" data-reveal="section" style="--reveal-delay:.1s" aria-label="<?php esc_attr_e( 'Investment', 'interior-specifics' ); ?>">
					<span class="eyebrow text-gold"><?php esc_html_e( 'Investment', 'interior-specifics' ); ?></span>
					<?php if ( '' !== $ist_from ) : ?>
						<p class="mt-4 font-serif text-4xl text-charcoal">
							<?php
							/* translators: %s: starting investment, e.g. ₦25M. */
							echo esc_html( sprintf( __( 'From %s', 'interior-specifics' ), $ist_from ) );
							?>
						</p>
					<?php endif; ?>
					<p class="mt-4 text-sm text-muted"><?php esc_html_e( 'Every project is unique. We\'ll provide a clear, documented quote after your consultation.', 'interior-specifics' ); ?></p>
					<a href="<?php echo esc_url( ist_booking_url() ); ?>" class="mt-6 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
						<?php esc_html_e( 'Book a Consultation', 'interior-specifics' ); ?>
						<span class="h-px w-6 bg-gold" aria-hidden="true"></span>
					</a>
				</aside>
			</div>
		</div>
	</section>

	<?php if ( $ist_process ) : ?>
		<section class="bg-ivory py-24 md:py-32">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => __( 'Process', 'interior-specifics' ),
							'title'   => __( 'How it works', 'interior-specifics' ),
						)
					);
					?>
				</div>
				<ol class="mt-16 grid gap-px overflow-hidden border border-line bg-line sm:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $ist_process as $ist_i => $ist_step ) : ?>
						<li class="ist-service-step" data-reveal="section"<?php echo $ist_i ? ' style="--reveal-delay:' . esc_attr( (string) ( $ist_i * 0.08 ) ) . 's"' : ''; ?>>
							<div class="group h-full bg-ivory p-8 transition-colors duration-300 hover:bg-white">
								<span class="font-serif text-3xl text-gold/80 transition-colors duration-300 group-hover:text-gold" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $ist_i + 1 ) ); ?></span>
								<h3 class="mt-4 text-lg text-charcoal"><?php echo esc_html( (string) ( $ist_step['step'] ?? '' ) ); ?></h3>
								<?php if ( ! empty( $ist_step['detail'] ) ) : ?>
									<p class="mt-2 text-sm text-muted"><?php echo esc_html( (string) $ist_step['detail'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ist_related->have_posts() ) : ?>
		<section class="grain relative bg-onyx py-24 md:py-32">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => __( 'Portfolio', 'interior-specifics' ),
							'title'   => __( 'Recent work', 'interior-specifics' ),
							'light'   => true,
						)
					);
					?>
				</div>
				<div class="mt-16">
					<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
						<?php
						$ist_index = 0;
						while ( $ist_related->have_posts() ) :
							$ist_related->the_post();
							get_template_part(
								'template-parts/portfolio-card',
								null,
								array(
									'post'        => get_post(),
									'index'       => $ist_index,
									'heading'     => 'h3',
									// Static, as in Next's services/[slug] PortfolioGrid.
									'reveal'      => false,
									'ring'        => 'focus-visible:ring-gold',
									'ring_offset' => 'focus-visible:ring-offset-onyx',
								)
							);
							++$ist_index;
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="bg-white py-24 md:py-32">
		<div class="<?php echo esc_attr( ist_container_class() ); ?>">
			<?php if ( $ist_faqs ) : ?>
				<div data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => __( 'Questions', 'interior-specifics' ),
							'title'   => __( 'Frequently asked', 'interior-specifics' ),
						)
					);
					?>
				</div>
				<div class="ist-faq mx-auto mt-12 max-w-3xl divide-y divide-line border-y border-line">
					<?php foreach ( $ist_faqs as $ist_faq ) : ?>
						<?php
						if ( empty( $ist_faq['q'] ) ) {
							continue;
						}
						?>
						<details class="group py-6">
							<summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg text-charcoal transition-colors group-hover:text-gold">
								<?php echo esc_html( (string) $ist_faq['q'] ); ?>
								<span class="text-2xl font-light text-gold transition-transform duration-300 group-open:rotate-45" aria-hidden="true">+</span>
							</summary>
							<p class="mt-3 text-muted"><?php echo esc_html( (string) ( $ist_faq['a'] ?? '' ) ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $ist_faqs ? 'mt-12 text-center text-sm text-muted' : 'text-center text-sm text-muted' ); ?>">
				<?php
				printf(
					/* translators: %s: link to the services overview. */
					esc_html__( 'Explore more services on our %s.', 'interior-specifics' ),
					sprintf(
						'<a href="%s" class="text-gold underline-offset-4 hover:underline">%s</a>',
						esc_url( get_post_type_archive_link( 'is_service' ) ? (string) get_post_type_archive_link( 'is_service' ) : home_url( '/services/' ) ),
						esc_html__( 'services overview', 'interior-specifics' )
					)
				);
				?>
			</div>
		</div>
	</section>

	<?php
	ist_cta_section(
		array(
			'eyebrow'       => __( 'Begin', 'interior-specifics' ),
			/* translators: %s: service title. */
			'title'         => sprintf( __( 'Start your %s project', 'interior-specifics' ), $ist_title ),
			'primary_label' => __( 'Book Your Consultation', 'interior-specifics' ),
		)
	);
endwhile;

get_footer();
