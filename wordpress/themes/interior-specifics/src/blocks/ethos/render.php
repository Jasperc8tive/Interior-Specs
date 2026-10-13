<?php
/**
 * Ethos block (port of components/home/Ethos.tsx).
 *
 * Editorial two-column ethos: a confident statement on the left (sticky from lg, 8rem from the
 * top), the value pillars as a hairline-ruled, numbered ledger on the right that staggers in.
 *
 * @package InteriorSpecifics
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$ist_body    = (string) ( $attributes['body'] ?? '' );
$ist_accent  = (string) ( $attributes['accentLine'] ?? '' );
$ist_pillars = array();
foreach ( (array) ( $attributes['pillars'] ?? array() ) as $ist_pillar ) {
	if ( ! is_array( $ist_pillar ) ) {
		continue;
	}
	$ist_pillar_title = (string) ( $ist_pillar['title'] ?? '' );
	$ist_pillar_body  = (string) ( $ist_pillar['body'] ?? '' );
	if ( '' !== $ist_pillar_title || '' !== $ist_pillar_body ) {
		$ist_pillars[] = array(
			'title' => $ist_pillar_title,
			'body'  => $ist_pillar_body,
		);
	}
}

$ist_wrapper = get_block_wrapper_attributes( array( 'class' => 'bg-ivory py-24 md:py-32' ) );
?>
<section <?php echo $ist_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes(). ?>>
	<div class="<?php echo esc_attr( ist_container_class() ); ?>">
		<div class="grid gap-14 lg:grid-cols-[1.05fr_0.95fr] lg:gap-20">
			<div class="lg:sticky lg:top-32 lg:self-start">
				<?php
				echo ist_eyebrow( (string) ( $attributes['eyebrow'] ?? '' ), 'text-gold' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
				echo ist_animated_heading( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						'text'        => (string) ( $attributes['title'] ?? '' ),
						'tag'         => 'h2',
						'class'       => 'mt-7 text-3xl leading-[1.12] text-charcoal sm:text-4xl lg:text-[2.9rem]',
						'accent_from' => (int) ( $attributes['accentFrom'] ?? -1 ),
						'accent_to'   => (int) ( $attributes['accentTo'] ?? -1 ),
					)
				);
				?>
				<?php if ( '' !== $ist_body ) : ?>
					<p class="mt-7 max-w-md text-lg text-muted" data-reveal style="--reveal-y:18px;--reveal-delay:.15s"><?php echo esc_html( $ist_body ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $ist_accent ) : ?>
					<p class="accent mt-8 max-w-md text-2xl leading-snug text-charcoal/80" data-reveal style="--reveal-y:18px;--reveal-delay:.25s"><?php echo esc_html( $ist_accent ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $ist_pillars ) : ?>
				<ol class="flex flex-col" role="list" data-reveal-stagger="0.12" data-reveal-amount="0.3">
					<?php foreach ( $ist_pillars as $ist_i => $ist_pillar ) : ?>
						<li class="group grid grid-cols-[auto_1fr] gap-6 border-t border-line py-8 last:border-b" data-reveal-item>
							<span class="font-serif text-2xl text-gold tabular-nums" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $ist_i + 1 ) ); ?></span>
							<div>
								<?php if ( '' !== $ist_pillar['title'] ) : ?>
									<h3 class="text-xl leading-[1.12] text-charcoal transition-colors duration-300 group-hover:text-gold"><?php echo esc_html( $ist_pillar['title'] ); ?></h3>
								<?php endif; ?>
								<?php if ( '' !== $ist_pillar['body'] ) : ?>
									<p class="mt-3 text-muted"><?php echo esc_html( $ist_pillar['body'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
	</div>
</section>
