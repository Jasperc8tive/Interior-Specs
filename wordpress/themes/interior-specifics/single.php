<?php
/**
 * Single Journal article (port of app/blog/[slug]/page.tsx): a 60vh image hero carrying the
 * title and meta, the excerpt as a Cormorant lede, the block content in a max-w-3xl column
 * (styled by .ist-article in src/css/parts/journal.css), a back link and the closing CTA.
 *
 * Article JSON-LD is printed in wp_head by the SEO module, not here.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ist_posts_page  = (int) get_option( 'page_for_posts' );
$ist_journal_url = $ist_posts_page ? get_permalink( $ist_posts_page ) : home_url( '/blog/' );

while ( have_posts() ) :
	the_post();

	$ist_id       = get_the_ID();
	$ist_cats     = 'post' === get_post_type() ? get_the_category() : array();
	$ist_meta     = implode(
		' · ',
		array_filter(
			array(
				$ist_cats ? $ist_cats[0]->name : '',
				get_the_date( 'j F Y' ),
				function_exists( 'isp_read_time' ) && 'post' === get_post_type() ? isp_read_time( $ist_id ) : '',
			)
		)
	);
	$ist_lede     = has_excerpt() && ! post_password_required() ? wp_strip_all_tags( get_the_excerpt() ) : '';
	$ist_hero_img = ist_post_image(
		$ist_id,
		'ist-hero',
		array(
			'class'   => 'absolute inset-0 h-full w-full object-cover',
			'sizes'   => '100vw',
			'loading' => 'eager',
		)
	);
	?>
	<section class="ist-article-hero relative -mt-[88px] flex min-h-[60vh] items-end bg-onyx">
		<?php echo $ist_hero_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<div class="hero-overlay absolute inset-0"></div>
		<div class="<?php echo esc_attr( ist_container_class( 'relative z-10 pb-14 pt-[88px]' ) ); ?>">
			<div class="max-w-3xl">
				<?php if ( $ist_meta ) : ?>
					<span class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft"><?php echo esc_html( $ist_meta ); ?></span>
				<?php endif; ?>
				<h1 class="mt-4 text-3xl leading-[1.12] text-ivory sm:text-4xl lg:text-5xl"><?php the_title(); ?></h1>
			</div>
		</div>
	</section>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-white py-16 md:py-24' ); ?>>
		<div class="<?php echo esc_attr( ist_container_class() ); ?>">
			<div class="mx-auto max-w-3xl">
				<?php if ( $ist_lede ) : ?>
					<p class="font-accent text-xl italic text-graysoft"><?php echo esc_html( $ist_lede ); ?></p>
				<?php endif; ?>

				<div class="<?php echo esc_attr( ist_classes( 'ist-article space-y-6', $ist_lede ? 'mt-10' : '' ) ); ?>">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before'      => '<nav class="ist-article__pages" aria-label="' . esc_attr__( 'Article pages', 'interior-specifics' ) . '"><span>' . esc_html__( 'Pages:', 'interior-specifics' ) . '</span>',
							'after'       => '</nav>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						)
					);
					?>
				</div>

				<div class="mt-14 border-t border-charcoal/10 pt-8">
					<a href="<?php echo esc_url( (string) $ist_journal_url ); ?>" class="text-sm font-semibold uppercase tracking-[0.15em] text-gold hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-charcoal"><?php esc_html_e( '← Back to the Journal', 'interior-specifics' ); ?></a>
				</div>
			</div>
		</div>
	</article>
	<?php
endwhile;

ist_cta_section();

get_footer();
