<?php
/**
 * Testimonials block (port of the testimonials section in app/page.tsx + components/TestimonialCarousel.tsx).
 *
 * Every slide is in the DOM; with JavaScript only the first shows until the script takes over and
 * hides the rest with `hidden`. src/js/modules/testimonials.js adds
 * the wait-mode cross-fade, wrap-around prev/next, dots, arrow keys, swipe and optional autoplay
 * (with a stop / start button).
 * Without JavaScript the controls are hidden and every quote is listed (see home-stories.css).
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_query_args = array(
	'post_type'           => 'is_testimonial',
	'post_status'         => 'publish',
	'posts_per_page'      => 50,
	'orderby'             => array(
		'menu_order' => 'ASC',
		'date'       => 'ASC',
	),
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);

$ist_ids = array_values( array_filter( array_map( 'absint', (array) $attributes['testimonialIds'] ) ) );
if ( 'selected' === $attributes['source'] && $ist_ids ) {
	$ist_query_args['post__in']       = $ist_ids;
	$ist_query_args['orderby']        = 'post__in';
	$ist_query_args['posts_per_page'] = count( $ist_ids );
}

$ist_query = new WP_Query( $ist_query_args );
// The picked testimonials were all deleted or unpublished: fall back to every testimonial.
if ( isset( $ist_query_args['post__in'] ) && ! $ist_query->have_posts() ) {
	unset( $ist_query_args['post__in'] );
	$ist_query_args['orderby']        = array(
		'menu_order' => 'ASC',
		'date'       => 'ASC',
	);
	$ist_query_args['posts_per_page'] = 50;
	$ist_query                        = new WP_Query( $ist_query_args );
}
$ist_slides = array();
foreach ( $ist_query->posts as $ist_post ) {
	$ist_quote = trim( wp_strip_all_tags( (string) $ist_post->post_content ) );
	if ( '' === $ist_quote ) {
		continue;
	}
	$ist_slides[] = array(
		'quote'    => $ist_quote,
		'name'     => get_the_title( $ist_post ),
		'location' => (string) get_post_meta( $ist_post->ID, 'isp_location', true ),
		'project'  => (string) get_post_meta( $ist_post->ID, 'isp_project_label', true ),
	);
}
wp_reset_postdata();

if ( ! $ist_slides ) {
	return;
}

$ist_count    = count( $ist_slides );
$ist_uid      = wp_unique_id( 'ist-testimonials-' );
$ist_interval = max( 4, min( 30, (int) $attributes['interval'] ) );
$ist_autoplay = ! empty( $attributes['autoplay'] ) && $ist_count > 1 ? $ist_interval * 1000 : 0;
$ist_control  = 'flex h-11 w-11 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold focus-visible:border-gold focus-visible:text-gold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'grain relative bg-charcoal py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="mx-auto max-w-2xl text-center">
			<?php
			echo ist_eyebrow( (string) $attributes['eyebrow'], 'eyebrow--center justify-center text-gold-soft', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'        => (string) $attributes['title'],
					'class'       => 'mt-6 text-3xl leading-[1.12] text-ivory sm:text-4xl lg:text-[2.75rem]',
					'accent_from' => (int) $attributes['accentFrom'],
					'accent_to'   => (int) $attributes['accentTo'],
					'id'          => $ist_uid . '-title',
				)
			);
			?>
		</div>

		<div class="mt-14" data-reveal="section">
			<div
				class="mx-auto max-w-3xl text-center"
				data-testimonials
				data-autoplay="<?php echo esc_attr( (string) $ist_autoplay ); ?>"
				role="region"
				aria-roledescription="<?php echo esc_attr_x( 'carousel', 'ARIA role description', 'interior-specifics' ); ?>"
				aria-label="<?php esc_attr_e( 'Client testimonials', 'interior-specifics' ); ?>"
			>
				<div class="relative min-h-[220px]" id="<?php echo esc_attr( $ist_uid . '-slides' ); ?>" data-testimonials-stage aria-live="polite">
					<?php foreach ( $ist_slides as $ist_i => $ist_slide ) : ?>
						<figure
							class="ist-testimonial"
							data-testimonial-slide
							role="group"
							aria-roledescription="<?php echo esc_attr_x( 'slide', 'ARIA role description', 'interior-specifics' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: number of slides. */ __( '%1$d of %2$d', 'interior-specifics' ), $ist_i + 1, $ist_count ) ); ?>"
						>
							<div class="mb-6 font-serif text-5xl text-gold/40" aria-hidden="true">&ldquo;</div>
							<blockquote class="font-accent text-2xl italic leading-relaxed text-ivory md:text-3xl"><?php echo esc_html( $ist_slide['quote'] ); ?></blockquote>
							<figcaption class="mt-8">
								<div class="text-sm font-semibold uppercase tracking-[0.15em] text-gold"><?php echo esc_html( $ist_slide['name'] ); ?></div>
								<?php
								$ist_meta = implode( ' · ', array_filter( array( $ist_slide['location'], $ist_slide['project'] ) ) );
								if ( '' !== $ist_meta ) :
									?>
									<div class="mt-1 text-sm text-ivory/60"><?php echo esc_html( $ist_meta ); ?></div>
								<?php endif; ?>
							</figcaption>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( $ist_count > 1 ) : ?>
					<div class="mt-10 flex items-center justify-center gap-6" data-testimonials-controls>
						<button type="button" class="<?php echo esc_attr( $ist_control ); ?>" data-testimonial-prev aria-controls="<?php echo esc_attr( $ist_uid . '-slides' ); ?>" aria-label="<?php esc_attr_e( 'Previous testimonial', 'interior-specifics' ); ?>"><span aria-hidden="true">&lsaquo;</span></button>
						<div class="flex gap-2">
							<?php for ( $ist_i = 0; $ist_i < $ist_count; $ist_i++ ) : ?>
								<button
									type="button"
									class="<?php echo esc_attr( ist_classes( 'relative h-2 w-2 rounded-full transition-colors after:absolute after:-inset-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold', 0 === $ist_i ? 'bg-gold' : 'bg-ivory/30' ) ); ?>"
									data-testimonial-dot="<?php echo esc_attr( (string) $ist_i ); ?>"
									aria-controls="<?php echo esc_attr( $ist_uid . '-slides' ); ?>"
									aria-label="<?php echo esc_attr( sprintf( /* translators: %d: testimonial number. */ __( 'Go to testimonial %d', 'interior-specifics' ), $ist_i + 1 ) ); ?>"
									<?php echo 0 === $ist_i ? 'aria-current="true"' : ''; ?>
								></button>
							<?php endfor; ?>
						</div>
						<button type="button" class="<?php echo esc_attr( $ist_control ); ?>" data-testimonial-next aria-controls="<?php echo esc_attr( $ist_uid . '-slides' ); ?>" aria-label="<?php esc_attr_e( 'Next testimonial', 'interior-specifics' ); ?>"><span aria-hidden="true">&rsaquo;</span></button>
						<?php if ( $ist_autoplay ) : ?>
							<?php // Stops the rotation for good (WCAG 2.2.2); shown by testimonials.js once autoplay runs. ?>
							<button type="button" class="<?php echo esc_attr( $ist_control ); ?>" data-testimonial-autoplay data-label-pause="<?php esc_attr_e( 'Stop rotating testimonials', 'interior-specifics' ); ?>" data-label-play="<?php esc_attr_e( 'Start rotating testimonials', 'interior-specifics' ); ?>" aria-label="<?php esc_attr_e( 'Stop rotating testimonials', 'interior-specifics' ); ?>" hidden>
								<svg class="h-3 w-3" viewBox="0 0 12 12" aria-hidden="true" focusable="false" data-icon-pause><path fill="currentColor" d="M2.5 1.5h2.25v9H2.5zm4.75 0h2.25v9H7.25z"/></svg>
								<svg class="h-3 w-3 translate-x-px" viewBox="0 0 12 12" aria-hidden="true" focusable="false" data-icon-play hidden><path fill="currentColor" d="M3 1.25v9.5L10.5 6z"/></svg>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
