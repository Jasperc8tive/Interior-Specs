<?php
/**
 * Compact inner-page hero (port of components/PageHero.tsx). Slides beneath the transparent
 * sticky header; the image drifts and scales on scroll while the title wipes up word by word.
 *
 * Args: eyebrow, title, accent_from, accent_to, intro, image_id, image_url, image_alt, tag (h1).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args        = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow'     => '',
		'title'       => '',
		'accent_from' => -1,
		'accent_to'   => -1,
		'intro'       => '',
		'image_id'    => 0,
		'image_url'   => '',
		'image_alt'   => '',
		'tag'         => 'h1',
		'wrapper'     => '',
	)
);
$ist_wrapper = $args['wrapper'] ? $args['wrapper'] : 'class="grain relative -mt-[88px] flex min-h-[68vh] items-center overflow-hidden bg-onyx"';
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by get_block_wrapper_attributes() or the literal above. ?> data-scrub>
	<div class="absolute inset-0" data-scrub-media="scale:0.12;y:80">
		<?php
		echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			(int) $args['image_id'],
			(string) $args['image_url'],
			(string) $args['image_alt'],
			'ist-hero',
			array(
				'class'   => 'absolute inset-0 h-full w-full object-cover',
				'sizes'   => '100vw',
				'loading' => 'eager',
			)
		);
		?>
		<div class="hero-overlay absolute inset-0"></div>
		<div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_50%_0%,transparent_55%,rgba(18,17,16,0.5))]"></div>
	</div>

	<div class="<?php echo esc_attr( ist_container_class( 'relative z-10 pt-[88px] text-center' ) ); ?>">
		<div class="mx-auto max-w-3xl" data-scrub-fade="end:0.6">
			<?php if ( $args['eyebrow'] ) : ?>
				<span class="eyebrow eyebrow--center justify-center text-gold-soft" data-settle style="--settle-delay:.1s"><?php echo esc_html( $args['eyebrow'] ); ?></span>
			<?php endif; ?>
			<?php
			echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				array(
					'text'        => $args['title'],
					'tag'         => $args['tag'],
					'class'       => 'display mt-6 text-4xl text-ivory sm:text-5xl lg:text-[3.5rem]',
					'accent_from' => (int) $args['accent_from'],
					'accent_to'   => (int) $args['accent_to'],
					'delay'       => 0.2,
				)
			);
			?>
			<div class="gold-rule mx-auto mt-7" data-settle style="--settle-delay:.5s"></div>
			<?php if ( $args['intro'] ) : ?>
				<p class="mx-auto mt-7 max-w-2xl text-lg text-ivory/85" data-settle style="--settle-delay:.6s"><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
