<?php
/**
 * Journal listing body shared by home.php (the posts page) and archive.php (category, tag,
 * author and date archives). Port of the section in app/blog/page.tsx: on the first page the
 * lead post is the wide featured card; everything else sits in a three-column grid.
 *
 * Runs the main query loop. Args:
 * - featured  bool   Whether the first post on page 1 becomes the featured card (default true).
 * - empty     string Message when there are no posts.
 * - back_link bool   Show "← Back to the Journal" under the listing (archives).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'featured'  => true,
		'empty'     => __( 'No articles have been published here yet. Please check back soon.', 'interior-specifics' ),
		'back_link' => false,
	)
);

$ist_has_featured = $args['featured'] && ! is_paged() && have_posts();
$ist_posts_page   = (int) get_option( 'page_for_posts' );
$ist_journal_url  = $ist_posts_page ? get_permalink( $ist_posts_page ) : home_url( '/blog/' );
?>
<?php
// Load every card image (attachment and meta) in two queries rather than two per card.
update_post_thumbnail_cache();
?>
<section class="ist-journal bg-white py-20 md:py-28">
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( have_posts() ) : ?>
			<?php
			if ( $ist_has_featured ) {
				the_post();
				get_template_part(
					'template-parts/post-card',
					null,
					array(
						'post'    => get_post(),
						'variant' => 'featured',
					)
				);
			}
			?>

			<?php if ( have_posts() ) : ?>
				<div class="<?php echo esc_attr( ist_classes( 'grid gap-8 md:grid-cols-2 lg:grid-cols-3', $ist_has_featured ? 'mt-14' : '' ) ); ?>">
					<?php
					$ist_index = 0;
					while ( have_posts() ) :
						the_post();
						get_template_part(
							'template-parts/post-card',
							null,
							array(
								'post'    => get_post(),
								'index'   => $ist_index,
								'heading' => $ist_has_featured ? 'h3' : 'h2',
							)
						);
						++$ist_index;
					endwhile;
					?>
				</div>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/pagination' ); ?>
		<?php else : ?>
			<p class="mx-auto max-w-xl text-center text-lg text-muted"><?php echo esc_html( $args['empty'] ); ?></p>
		<?php endif; ?>

		<?php if ( $args['back_link'] ) : ?>
			<div class="mt-16 text-center">
				<a href="<?php echo esc_url( (string) $ist_journal_url ); ?>" class="text-sm font-semibold uppercase tracking-[0.15em] text-gold hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-charcoal"><?php esc_html_e( '← Back to the Journal', 'interior-specifics' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
