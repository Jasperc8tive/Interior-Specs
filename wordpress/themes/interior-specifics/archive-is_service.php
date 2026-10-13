<?php
/**
 * Services archive, /services/ (port of app/services/page.tsx): hero, a three-column grid of
 * every service in menu order (the Core plugin sets posts_per_page -1), then the closing CTA.
 *
 * The hero image is chosen under Settings → Interior Specifics → Page heroes; developers can
 * change the copy with the `ist_services_archive_hero` filter.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

ist_page_hero(
	/**
	 * Filters the services archive hero.
	 *
	 * @param array<string, mixed> $args ist_page_hero() args.
	 */
	apply_filters(
		'ist_services_archive_hero',
		array(
			'eyebrow'     => __( 'Our Services', 'interior-specifics' ),
			'title'       => __( 'End-to-end interior design solutions', 'interior-specifics' ),
			'accent_from' => 0,
			'accent_to'   => 1,
			'intro'       => __( 'From concept to completion, we bring your vision to life — every discipline handled in-house.', 'interior-specifics' ),
			'image_id'    => ist_page_hero_image_id( 'services' ),
			'image_url'   => '',
			'image_alt'   => __( 'Beautifully finished luxury interior', 'interior-specifics' ),
		)
	)
);
?>
<section class="bg-ivory py-24 md:py-32" aria-label="<?php esc_attr_e( 'All services', 'interior-specifics' ); ?>">
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( have_posts() ) : ?>
			<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				update_post_thumbnail_cache(); // Every card image in two queries, not two per card.
				$ist_index = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/service-card',
						null,
						array(
							'post'    => get_post(),
							'index'   => $ist_index,
							'heading' => 'h2',
						)
					);
					++$ist_index;
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="text-center text-muted"><?php esc_html_e( 'Our services will be listed here shortly.', 'interior-specifics' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
ist_cta_section();

get_footer();
