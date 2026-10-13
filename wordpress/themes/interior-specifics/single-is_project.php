<?php
/**
 * Project case study, /showroom/<slug>/ (port of app/showroom/[slug]/page.tsx).
 *
 * Hero, facts and scope, challenge / solution, gallery with lightbox, results and testimonial,
 * optional extra notes from the editor, previous / next (wrapping) and the closing CTA.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_id       = (int) get_queried_object_id();
$ist_has_core = function_exists( 'isp_meta' );
$ist_meta     = static fn( string $key ): string => function_exists( 'isp_meta' ) ? (string) isp_meta( $ist_id, $key ) : '';
$ist_title    = get_the_title( $ist_id );
$ist_type     = function_exists( 'isp_project_type' ) ? isp_project_type( $ist_id ) : null;
$ist_type_nm  = $ist_type ? $ist_type->name : '';
$ist_location = $ist_meta( 'location' );
$ist_year     = $ist_meta( 'year' );
$ist_thumb    = (int) get_post_thumbnail_id( $ist_id );

// Gallery: the hero image first, then the gallery attachments (deduplicated, images only).
// Their posts and meta are loaded in two queries up front rather than two per image.
$ist_gallery_ids = array_merge( array( $ist_thumb ), array_map( 'absint', (array) ( $ist_has_core ? isp_meta( $ist_id, 'gallery' ) : array() ) ) );
ist_prime_attachments( $ist_gallery_ids );
$ist_gallery_ids = array_values(
	array_unique(
		array_filter(
			$ist_gallery_ids,
			static fn( $id ) => $id && wp_attachment_is_image( $id )
		)
	)
);

add_filter(
	'document_title_parts',
	static function ( array $parts ) use ( $ist_title, $ist_type_nm ): array {
		$parts['title'] = $ist_type_nm
			/* translators: 1: project title, 2: project type (e.g. Residential). */
			? sprintf( __( '%1$s — %2$s Case Study', 'interior-specifics' ), $ist_title, $ist_type_nm )
			/* translators: %s: project title. */
			: sprintf( __( '%s — Case Study', 'interior-specifics' ), $ist_title );
		return $parts;
	}
);

add_action(
	'wp_head',
	static function () use ( $ist_id, $ist_title, $ist_location, $ist_gallery_ids ): void {
		/**
		 * Whether the theme prints CreativeWork JSON-LD for a project. Return false when an SEO
		 * plugin (or the Core plugin) already outputs structured data for projects.
		 *
		 * @param bool $enabled Default true.
		 * @param int  $post_id Project ID.
		 */
		if ( ! apply_filters( 'ist_project_schema', true, $ist_id ) ) {
			return;
		}
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'CreativeWork',
			'name'        => $ist_title,
			'description' => wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $ist_id ) ),
			'url'         => get_permalink( $ist_id ),
			'image'       => array_values( array_filter( array_map( static fn( $id ) => wp_get_attachment_image_url( $id, 'ist-hero' ), $ist_gallery_ids ) ) ),
			'creator'     => array(
				'@type' => 'Organization',
				'name'  => ist_setting( 'name' ) ? ist_setting( 'name' ) : get_bloginfo( 'name' ),
			),
		);
		if ( $ist_location ) {
			$schema['locationCreated'] = array(
				'@type' => 'Place',
				'name'  => $ist_location,
			);
		}
		printf( "<script type=\"application/ld+json\">%s</script>\n", wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) );
	}
);

get_header();

while ( have_posts() ) :
	the_post();

	$ist_alt = $ist_location
		/* translators: 1: project title, 2: project type (e.g. Residential), 3: location. */
		? sprintf( __( '%1$s — %2$s interior design in %3$s', 'interior-specifics' ), $ist_title, $ist_type_nm ? $ist_type_nm : __( 'Luxury', 'interior-specifics' ), $ist_location )
		: $ist_title;
	?>

	<section class="grain relative -mt-[88px] flex min-h-[72vh] items-end overflow-hidden bg-onyx">
		<?php
		echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			$ist_thumb,
			'',
			$ist_alt,
			'ist-hero',
			array(
				'class'   => 'absolute inset-0 h-full w-full object-cover',
				'sizes'   => '100vw',
				'loading' => 'eager',
			)
		);
		?>
		<div class="hero-overlay absolute inset-0"></div>
		<div class="<?php echo esc_attr( ist_container_class( 'relative z-10 pb-16 pt-[88px]' ) ); ?>">
			<a href="<?php echo esc_url( (string) get_post_type_archive_link( 'is_project' ) ); ?>" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft transition-colors hover:text-gold focus:outline-none focus-visible:text-gold focus-visible:underline">
				<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Showroom', 'interior-specifics' ); ?>
			</a>
			<?php
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'  => $ist_title,
					'tag'   => 'h1',
					'delay' => 0.15,
					'class' => 'display mt-5 max-w-3xl text-4xl text-ivory sm:text-5xl lg:text-6xl',
				)
			);
			$ist_hero_meta = implode( ' · ', array_filter( array( $ist_type_nm, $ist_location, $ist_year ) ) );
			if ( $ist_hero_meta ) :
				?>
				<p class="mt-4 text-sm uppercase tracking-[0.2em] text-ivory/70"><?php echo esc_html( $ist_hero_meta ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$ist_facts = array_filter(
		array(
			__( 'Location', 'interior-specifics' )   => $ist_location,
			__( 'Type', 'interior-specifics' )       => $ist_type_nm,
			__( 'Investment', 'interior-specifics' ) => $ist_meta( 'budget' ),
			__( 'Timeline', 'interior-specifics' )   => $ist_meta( 'timeline' ),
			__( 'Completed', 'interior-specifics' )  => $ist_year,
		)
	);

	// Scope chips, in the order they were entered when the taxonomy keeps term order.
	$ist_scope_tax = get_taxonomy( 'is_project_scope' );
	$ist_scope     = $ist_scope_tax && ! empty( $ist_scope_tax->sort )
		? wp_get_object_terms( $ist_id, 'is_project_scope', array( 'orderby' => 'term_order' ) )
		: get_the_terms( $ist_id, 'is_project_scope' );
	$ist_scope     = ( $ist_scope && ! is_wp_error( $ist_scope ) ) ? $ist_scope : array();

	if ( $ist_facts || $ist_scope ) :
		// Literal classes so Tailwind generates them: one column per fact on large screens.
		$ist_cols = array(
			1 => 'lg:grid-cols-1',
			2 => 'lg:grid-cols-2',
			3 => 'lg:grid-cols-3',
			4 => 'lg:grid-cols-4',
			5 => 'lg:grid-cols-5',
		);
		?>
		<section class="bg-white py-16 md:py-20">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<?php if ( $ist_facts ) : ?>
					<dl class="<?php echo esc_attr( ist_classes( 'm-0 grid gap-px overflow-hidden border border-line bg-line', count( $ist_facts ) > 1 ? 'sm:grid-cols-2' : '', $ist_cols[ count( $ist_facts ) ] ) ); ?>">
						<?php foreach ( $ist_facts as $ist_label => $ist_value ) : ?>
							<div class="bg-white p-6">
								<dt class="block text-xs font-semibold uppercase tracking-[0.15em] text-gold"><?php echo esc_html( $ist_label ); ?></dt>
								<dd class="m-0 mt-2 block text-charcoal"><?php echo esc_html( $ist_value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<?php if ( $ist_scope ) : ?>
					<ul class="<?php echo esc_attr( ist_classes( 'flex flex-wrap gap-2', $ist_facts ? 'mt-8' : '' ) ); ?>" aria-label="<?php esc_attr_e( 'Project scope', 'interior-specifics' ); ?>">
						<?php foreach ( $ist_scope as $ist_scope_term ) : ?>
							<li class="border border-line px-4 py-1.5 text-xs uppercase tracking-[0.12em] text-muted"><?php echo esc_html( $ist_scope_term->name ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ist_narrative = array_filter(
		array(
			__( 'The Challenge', 'interior-specifics' ) => $ist_meta( 'challenge' ),
			__( 'Our Solution', 'interior-specifics' )  => $ist_meta( 'solution' ),
		)
	);
	if ( $ist_narrative ) :
		?>
		<section class="bg-ivory py-24 md:py-32">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-2 md:gap-16">
					<?php
					$ist_n = 0;
					foreach ( $ist_narrative as $ist_label => $ist_text ) :
						?>
						<div data-reveal="section"<?php echo $ist_n ? ' style="--reveal-delay:.1s"' : ''; ?>>
							<?php echo ist_eyebrow( $ist_label, 'text-gold', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
							<?php foreach ( preg_split( '/\R{2,}/', trim( $ist_text ) ) as $ist_paragraph ) : ?>
								<p class="mt-6 text-lg text-muted"><?php echo esc_html( $ist_paragraph ); ?></p>
							<?php endforeach; ?>
						</div>
						<?php
						++$ist_n;
					endforeach;
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ist_gallery_ids ) : ?>
		<section class="bg-white py-16 md:py-24">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div class="mb-12" data-reveal="section">
					<?php
					echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
						array(
							'eyebrow' => __( 'Gallery', 'interior-specifics' ),
							'title'   => __( 'Inside the project', 'interior-specifics' ),
							'align'   => 'left',
						)
					);
					?>
				</div>
				<?php
				get_template_part(
					'template-parts/project-gallery',
					null,
					array(
						'images' => array_map( static fn( $id ) => array( 'id' => $id ), $ist_gallery_ids ),
						'title'  => $ist_title,
					)
				);
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ist_results = $ist_meta( 'results' );
	$ist_quote   = $ist_meta( 'testimonial_quote' );
	$ist_quoter  = $ist_meta( 'testimonial_name' );
	if ( $ist_results || $ist_quote ) :
		?>
		<section class="grain relative bg-charcoal py-24 md:py-32">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div class="mx-auto max-w-3xl text-center">
					<div data-reveal="section">
						<?php
						echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
							array(
								'eyebrow' => __( 'The Outcome', 'interior-specifics' ),
								'title'   => __( 'Results', 'interior-specifics' ),
								'light'   => true,
							)
						);
						if ( $ist_results ) :
							foreach ( preg_split( '/\R{2,}/', trim( $ist_results ) ) as $ist_paragraph ) :
								?>
								<p class="mt-7 text-lg text-ivory/80"><?php echo esc_html( $ist_paragraph ); ?></p>
								<?php
							endforeach;
						endif;
						?>
					</div>
					<?php if ( $ist_quote ) : ?>
						<figure class="m-0" data-reveal="section" style="--reveal-delay:.1s">
							<blockquote class="accent mt-12 text-2xl leading-relaxed text-ivory md:text-3xl">&ldquo;<?php echo esc_html( $ist_quote ); ?>&rdquo;</blockquote>
							<?php if ( $ist_quoter ) : ?>
								<figcaption class="mt-5 text-sm uppercase tracking-[0.15em] text-gold"><?php echo esc_html( $ist_quoter ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== trim( (string) get_post_field( 'post_content', $ist_id ) ) ) : ?>
		<section class="bg-white">
			<div class="is-prose">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$ist_adjacent = function_exists( 'isp_adjacent_projects' ) ? isp_adjacent_projects( $ist_id ) : array(
		'prev' => null,
		'next' => null,
	);
	if ( $ist_adjacent['prev'] || $ist_adjacent['next'] ) :
		?>
		<nav class="bg-ivory py-14" aria-label="<?php esc_attr_e( 'More projects', 'interior-specifics' ); ?>">
			<div class="<?php echo esc_attr( ist_container_class() ); ?>">
				<div class="grid gap-4 sm:grid-cols-2">
					<?php if ( $ist_adjacent['prev'] ) : ?>
						<a href="<?php echo esc_url( get_permalink( $ist_adjacent['prev'] ) ); ?>" rel="prev" class="group flex flex-col border border-line bg-white p-7 transition-colors duration-300 hover:border-gold focus:outline-none focus-visible:border-charcoal focus-visible:ring-2 focus-visible:ring-charcoal">
							<span class="text-xs font-semibold uppercase tracking-[0.15em] text-gold"><span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Previous Project', 'interior-specifics' ); ?></span>
							<span class="mt-2 text-lg text-charcoal transition-colors group-hover:text-gold group-focus-visible:text-gold"><?php echo esc_html( get_the_title( $ist_adjacent['prev'] ) ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $ist_adjacent['next'] ) : ?>
						<a href="<?php echo esc_url( get_permalink( $ist_adjacent['next'] ) ); ?>" rel="next" class="group flex flex-col items-end border border-line bg-white p-7 text-right transition-colors duration-300 hover:border-gold focus:outline-none focus-visible:border-charcoal focus-visible:ring-2 focus-visible:ring-charcoal sm:col-start-2">
							<span class="text-xs font-semibold uppercase tracking-[0.15em] text-gold"><?php esc_html_e( 'Next Project', 'interior-specifics' ); ?> <span aria-hidden="true">&rarr;</span></span>
							<span class="mt-2 text-lg text-charcoal transition-colors group-hover:text-gold group-focus-visible:text-gold"><?php echo esc_html( get_the_title( $ist_adjacent['next'] ) ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</nav>
	<?php endif; ?>

	<?php
	ist_cta_section(
		array(
			'eyebrow'       => __( 'Begin', 'interior-specifics' ),
			'title'         => __( 'Imagine what we could do with your space', 'interior-specifics' ),
			'primary_label' => __( 'Book Your Private Consultation', 'interior-specifics' ),
		)
	);
endwhile;

get_footer();
