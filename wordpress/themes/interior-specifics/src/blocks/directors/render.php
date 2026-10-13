<?php
/**
 * Directors block — the About page "#directors" section (app/about/page.tsx): a centred
 * RevealHeading above 3:4 portrait cards that rise in turn; on hover the portrait zooms to 105%
 * over 1.1s and a charcoal gradient fades up.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_people = array_values(
	array_filter(
		is_array( $attributes['people'] ?? null ) ? $attributes['people'] : array(),
		static fn( $person ) => is_array( $person ) && ( '' !== trim( (string) ( $person['name'] ?? '' ) ) || ! empty( $person['imageId'] ) || '' !== trim( (string) ( $person['imageUrl'] ?? '' ) ) )
	)
);
$ist_title  = trim( (string) ( $attributes['title'] ?? '' ) );
$ist_anchor = (string) ( $attributes['anchor'] ?? '' );
?>
<section 
<?php
echo get_block_wrapper_attributes(
	array(
		'class' => 'bg-ivory py-24 md:py-32',
		'id'    => '' !== $ist_anchor ? $ist_anchor : 'directors',
	)
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped wrapper attributes. 
?>
>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<?php if ( '' !== $ist_title || '' !== (string) ( $attributes['eyebrow'] ?? '' ) ) : ?>
			<div data-reveal="section">
				<?php
				echo ist_reveal_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'eyebrow'     => (string) ( $attributes['eyebrow'] ?? '' ),
						'title'       => $ist_title,
						'intro'       => (string) ( $attributes['intro'] ?? '' ),
						'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
						'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
						// leading-[1.12]: Next's unlayered h1–h4 rule overrides the size utilities' line-height.
						'title_class' => 'max-w-3xl text-3xl leading-[1.12] md:text-4xl lg:text-[2.75rem]',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<?php if ( $ist_people ) : ?>
			<div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $ist_people as $ist_i => $ist_person ) : ?>
					<?php
					$ist_name  = (string) ( $ist_person['name'] ?? '' );
					$ist_alt   = trim( (string) ( $ist_person['imageAlt'] ?? '' ) );
					$ist_delay = round( $ist_i * 0.1, 2 );
					$ist_photo = ist_image(
						(int) ( $ist_person['imageId'] ?? 0 ),
						(string) ( $ist_person['imageUrl'] ?? '' ),
						'' !== $ist_alt ? $ist_alt : $ist_name,
						'ist-portrait',
						array(
							'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105',
							'sizes' => '(max-width: 768px) 100vw, 33vw',
						)
					);
					?>
					<div class="group bg-white" data-reveal="section"<?php echo $ist_delay ? ' style="--reveal-delay:' . esc_attr( (string) $ist_delay ) . 's"' : ''; ?>>
						<div class="relative aspect-[3/4] overflow-hidden">
							<?php echo $ist_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
							<div class="absolute inset-0 bg-gradient-to-t from-charcoal/45 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" aria-hidden="true"></div>
						</div>
						<div class="p-7">
							<?php if ( '' !== $ist_name ) : ?>
								<h3 class="text-lg leading-[1.12] text-charcoal"><?php echo esc_html( $ist_name ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== (string) ( $ist_person['role'] ?? '' ) ) : ?>
								<p class="mt-1 text-sm uppercase tracking-[0.12em] text-gold"><?php echo esc_html( (string) $ist_person['role'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
