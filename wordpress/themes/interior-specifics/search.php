<?php
/**
 * Search results across pages, posts, services and projects.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

ist_page_hero(
	array(
		'eyebrow'   => __( 'Search', 'interior-specifics' ),
		/* translators: %s: search query. */
		'title'     => sprintf( __( 'Results for “%s”', 'interior-specifics' ), get_search_query() ),
		'image_id'  => ist_page_hero_image_id(),
		'image_url' => '',
	)
);
?>
<section class="bg-white py-24 md:py-32">
	<div class="<?php echo esc_attr( ist_container_class( 'max-w-3xl' ) ); ?>">
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex gap-3">
			<label for="ist-search" class="screen-reader-text"><?php esc_html_e( 'Search for:', 'interior-specifics' ); ?></label>
			<input id="ist-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" class="min-h-12 w-full border border-line bg-white px-4 py-3 text-sm text-charcoal focus:border-charcoal focus:outline-none focus:ring-1 focus:ring-charcoal">
			<button type="submit" class="<?php echo esc_attr( ist_button_classes( 'primary', 'focus-visible:ring-charcoal' ) ); ?>"><?php esc_html_e( 'Search', 'interior-specifics' ); ?></button>
		</form>

		<?php if ( have_posts() ) : ?>
			<ol class="mt-12 divide-y divide-line border-y border-line">
				<?php
				while ( have_posts() ) :
					the_post();
					$ist_type = get_post_type_object( get_post_type() );
					?>
					<li class="py-6">
						<a href="<?php the_permalink(); ?>" class="group block">
							<span class="text-xs font-semibold uppercase tracking-[0.18em] text-gold"><?php echo esc_html( $ist_type ? $ist_type->labels->singular_name : '' ); ?></span>
							<h2 class="mt-2 text-xl text-charcoal transition-colors group-hover:text-gold"><?php the_title(); ?></h2>
							<p class="mt-2 text-sm text-muted"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
						</a>
					</li>
				<?php endwhile; ?>
			</ol>
			<?php get_template_part( 'template-parts/pagination' ); ?>
		<?php else : ?>
			<p class="mt-12 text-muted"><?php esc_html_e( 'No results. Try different words, or explore our services and showroom.', 'interior-specifics' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
