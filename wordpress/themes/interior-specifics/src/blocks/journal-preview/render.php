<?php
/**
 * Journal Preview block (port of components/home/JournalPreview.tsx).
 *
 * Image-led editorial cards for the latest posts (optionally one category), each with a
 * "{category} · {read time}" line, a gold title on hover and a slow image push.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => max( 1, min( 9, (int) $attributes['count'] ) ),
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);
if ( (int) $attributes['category'] > 0 ) {
	$ist_query_args['cat'] = (int) $attributes['category'];
}
$ist_query = new WP_Query( $ist_query_args );
update_post_thumbnail_cache( $ist_query ); // All card images in two queries, not two per card.
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-white py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
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
			</div>
			<?php if ( '' !== (string) $attributes['linkLabel'] ) : ?>
				<div data-reveal style="--reveal-y:16px">
					<a <?php echo ist_link_attrs( (string) $attributes['linkUrl'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> class="group inline-flex items-center gap-3 text-sm font-medium uppercase tracking-[0.18em] text-charcoal transition-colors hover:text-gold">
						<?php echo esc_html( (string) $attributes['linkLabel'] ); ?>
						<span class="h-px w-10 bg-gold transition-all duration-300 group-hover:w-16" aria-hidden="true"></span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $ist_query->have_posts() ) : ?>
			<div class="mt-14 grid gap-8 md:grid-cols-3" data-reveal-stagger="0.1">
				<?php
				foreach ( $ist_query->posts as $ist_post ) :
					$ist_categories = get_the_category( $ist_post->ID );
					$ist_meta       = array_filter(
						array(
							$ist_categories ? $ist_categories[0]->name : '',
							function_exists( 'isp_read_time' ) ? isp_read_time( $ist_post->ID ) : '',
						)
					);
					?>
					<article data-reveal-item>
						<a href="<?php echo esc_url( get_permalink( $ist_post ) ); ?>" class="group block">
							<div class="relative aspect-[16/11] overflow-hidden">
								<?php
								echo ist_post_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
									$ist_post,
									'large', // Uncropped: the 16:11 box crops the source once, as in Next.
									array(
										'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105',
										'sizes' => '(max-width: 768px) 100vw, 33vw',
									)
								);
								?>
							</div>
							<?php if ( $ist_meta ) : ?>
								<span class="mt-5 inline-block text-xs font-semibold uppercase tracking-[0.18em] text-gold"><?php echo esc_html( implode( ' · ', $ist_meta ) ); ?></span>
							<?php endif; ?>
							<h3 class="mt-2 text-lg leading-[1.12] text-charcoal transition-colors duration-300 group-hover:text-gold"><?php echo esc_html( get_the_title( $ist_post ) ); ?></h3>
							<?php if ( has_excerpt( $ist_post ) ) : ?>
								<p class="mt-2 text-sm text-muted"><?php echo esc_html( (string) $ist_post->post_excerpt ); ?></p>
							<?php else : ?>
								<p class="mt-2 text-sm text-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $ist_post->post_content ) ), 22 ) ); ?></p>
							<?php endif; ?>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</section>
