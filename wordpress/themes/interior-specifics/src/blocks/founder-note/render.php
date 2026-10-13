<?php
/**
 * Founder Note block (port of components/home/FounderNote.tsx).
 *
 * A parallaxed portrait (data-parallax inside an overflow-hidden frame, scaled 110% so the drift
 * never reveals an edge) beside the eyebrow, word-reveal heading, biography, signed pull quote
 * and a dark button. Copy fades up in sequence (.12s / .2s / .28s) like the original.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_quote = trim( (string) $attributes['quote'] );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'bg-white py-24 md:py-32' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="grid items-center gap-14 lg:grid-cols-[0.85fr_1fr] lg:gap-20">
			<div class="relative aspect-[4/5] overflow-hidden" data-reveal="fade">
				<div class="absolute inset-0">
					<div class="relative h-full w-full" data-parallax="40">
						<?php
						echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
							(int) $attributes['imageId'],
							(string) $attributes['imageUrl'],
							(string) $attributes['imageAlt'],
							'ist-portrait',
							array(
								'class' => 'absolute inset-0 h-full w-full scale-110 object-cover',
								'sizes' => '(max-width: 1024px) 100vw, 40vw',
							)
						);
						?>
					</div>
				</div>
				<?php if ( '' !== (string) $attributes['caption'] ) : ?>
					<span class="absolute bottom-5 left-5 z-10 text-xs uppercase tracking-[0.25em] text-white/80"><?php echo esc_html( (string) $attributes['caption'] ); ?></span>
				<?php endif; ?>
			</div>

			<div>
				<?php
				echo ist_eyebrow( (string) $attributes['eyebrow'], 'text-gold', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'text'        => (string) $attributes['title'],
						'class'       => 'mt-6 text-3xl leading-[1.12] text-charcoal sm:text-4xl lg:text-[2.6rem]',
						'accent_from' => (int) $attributes['accentFrom'],
						'accent_to'   => (int) $attributes['accentTo'],
					)
				);
				?>

				<?php if ( '' !== (string) $attributes['body'] ) : ?>
					<p class="mt-7 max-w-xl text-lg text-muted" data-reveal style="--reveal-y:18px;--reveal-delay:.12s"><?php echo esc_html( (string) $attributes['body'] ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $ist_quote ) : ?>
					<blockquote class="accent mt-8 border-l-2 border-gold pl-7 text-2xl leading-snug text-charcoal md:text-[1.7rem]" data-reveal style="--reveal-y:18px;--reveal-delay:.2s">
						<?php
						/* translators: %s: the founder's quotation. */
						echo esc_html( sprintf( _x( '“%s”', 'curly quotation marks around a quote', 'interior-specifics' ), $ist_quote ) );
						?>
					</blockquote>
				<?php endif; ?>

				<?php if ( '' !== (string) $attributes['buttonLabel'] ) : ?>
					<div class="mt-9" data-reveal style="--reveal-y:18px;--reveal-delay:.28s">
						<?php echo ist_button( (string) $attributes['buttonLabel'], (string) $attributes['buttonUrl'], 'dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
