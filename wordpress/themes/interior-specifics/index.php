<?php
/**
 * Fallback template for anything without a more specific one (e.g. date archives).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

ist_page_hero(
	array(
		'eyebrow'   => is_archive() ? __( 'Archive', 'interior-specifics' ) : '',
		'title'     => is_archive() ? wp_strip_all_tags( get_the_archive_title() ) : get_bloginfo( 'name' ),
		'image_id'  => ist_page_hero_image_id(),
		'image_url' => '',
	)
);
?>
<section class="bg-white py-24 md:py-32">
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( have_posts() ) : ?>
			<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				update_post_thumbnail_cache();
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php get_template_part( 'template-parts/pagination' ); ?>
		<?php else : ?>
			<p class="text-center text-muted"><?php esc_html_e( 'Nothing here yet.', 'interior-specifics' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
