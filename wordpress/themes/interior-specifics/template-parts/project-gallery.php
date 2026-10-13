<?php
/**
 * Project image gallery with a full-screen lightbox (port of components/Gallery.tsx).
 *
 * The first tile spans two columns; every tile opens the lightbox at that image. Behaviour
 * (focus trap, Esc, arrow keys, swipe, scroll lock, focus return) lives in
 * src/js/modules/lightbox.js; without JS each tile links to the full-size image instead.
 *
 * Args:
 *   images array<int, array{id?: int, url?: string}>  Attachment IDs (preferred) or URLs.
 *   title  string                                     Project title (alt text and dialog label).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'images' => array(),
		'title'  => '',
	)
);

// Normalise to a list of renderable images.
$ist_items = array();
foreach ( (array) $args['images'] as $ist_image ) {
	$ist_id  = (int) ( $ist_image['id'] ?? 0 );
	$ist_url = (string) ( $ist_image['url'] ?? '' );

	if ( $ist_id && wp_attachment_is_image( $ist_id ) ) {
		$ist_full = wp_get_attachment_image_src( $ist_id, 'ist-hero' );
		if ( ! $ist_full ) {
			continue;
		}
		$ist_items[] = array(
			'id'      => $ist_id,
			'url'     => '',
			'src'     => (string) $ist_full[0],
			'srcset'  => (string) wp_get_attachment_image_srcset( $ist_id, 'ist-hero' ),
			'caption' => (string) wp_get_attachment_caption( $ist_id ),
		);
	} elseif ( '' !== $ist_url ) {
		$ist_items[] = array(
			'id'      => 0,
			'url'     => $ist_url,
			'src'     => $ist_url,
			'srcset'  => '',
			'caption' => '',
		);
	}
}

$ist_count = count( $ist_items );
if ( ! $ist_count ) {
	return;
}

$ist_title = (string) $args['title'];
/* translators: %s: project title. */
$ist_label = sprintf( __( '%s gallery', 'interior-specifics' ), $ist_title );
?>
<div data-lightbox>
	<div class="grid gap-6 md:grid-cols-3">
		<?php
		foreach ( $ist_items as $ist_i => $ist_item ) :
			/* translators: 1: project title, 2: image number. */
			$ist_alt = sprintf( __( '%1$s interior detail %2$d', 'interior-specifics' ), $ist_title, $ist_i + 1 );
			?>
			<a
				href="<?php echo esc_url( $ist_item['src'] ); ?>"
				class="<?php echo esc_attr( ist_classes( 'group relative block cursor-zoom-in overflow-hidden bg-ivory focus:outline-none focus-visible:ring-2 focus-visible:ring-charcoal focus-visible:ring-offset-2', 0 === $ist_i ? 'aspect-[16/10] md:col-span-2' : 'aspect-[4/3]' ) ); ?>"
				aria-label="<?php echo esc_attr( sprintf( /* translators: 1: image number, 2: image count. */ __( 'Open image %1$d of %2$d', 'interior-specifics' ), $ist_i + 1, $ist_count ) ); ?>"
				data-lightbox-open="<?php echo (int) $ist_i; ?>"
				data-src="<?php echo esc_url( $ist_item['src'] ); ?>"
				<?php if ( $ist_item['srcset'] ) : ?>
					data-srcset="<?php echo esc_attr( $ist_item['srcset'] ); ?>"
				<?php endif; ?>
				data-alt="<?php echo esc_attr( $ist_alt ); ?>"
				data-caption="<?php echo esc_attr( $ist_item['caption'] ); ?>"
			>
				<?php
				echo ist_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					$ist_item['id'],
					$ist_item['url'],
					$ist_alt,
					'large',
					array(
						'class' => 'absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105 group-focus-visible:scale-105',
						'sizes' => 0 === $ist_i ? '(max-width: 768px) 100vw, 66vw' : '(max-width: 768px) 100vw, 33vw',
					)
				);
				?>
				<span class="absolute inset-0 bg-charcoal/0 transition-colors duration-300 group-hover:bg-charcoal/15 group-focus-visible:bg-charcoal/15"></span>
			</a>
		<?php endforeach; ?>
	</div>

	<div
		class="ist-lightbox fixed inset-0 z-[60] flex items-center justify-center bg-charcoal/95 p-4"
		role="dialog"
		aria-modal="true"
		aria-label="<?php echo esc_attr( $ist_label ); ?>"
		data-lightbox-dialog
		hidden
	>
		<button type="button" class="absolute right-5 top-5 z-10 flex h-11 w-11 items-center justify-center text-2xl text-ivory/80 transition-colors hover:text-gold focus:outline-none focus-visible:text-gold focus-visible:ring-2 focus-visible:ring-gold" aria-label="<?php esc_attr_e( 'Close gallery', 'interior-specifics' ); ?>" data-lightbox-close>
			<span aria-hidden="true">&#x2715;</span>
		</button>

		<button type="button" class="absolute left-3 z-10 flex h-12 w-12 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold focus:outline-none focus-visible:border-gold focus-visible:text-gold focus-visible:ring-2 focus-visible:ring-gold md:left-8" aria-label="<?php esc_attr_e( 'Previous image', 'interior-specifics' ); ?>" data-lightbox-prev data-lightbox-stop>
			<span aria-hidden="true">&#x2039;</span>
		</button>

		<figure class="ist-lightbox__figure relative m-0 h-[78vh] w-full max-w-5xl" data-lightbox-stop>
			<img class="absolute inset-0 h-full w-full object-contain" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="" sizes="100vw" decoding="async" data-lightbox-image>
			<figcaption class="absolute inset-x-0 -bottom-9 text-center text-xs text-ivory/60" data-lightbox-caption hidden></figcaption>
		</figure>

		<button type="button" class="absolute right-3 z-10 flex h-12 w-12 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold focus:outline-none focus-visible:border-gold focus-visible:text-gold focus-visible:ring-2 focus-visible:ring-gold md:right-8" aria-label="<?php esc_attr_e( 'Next image', 'interior-specifics' ); ?>" data-lightbox-next data-lightbox-stop>
			<span aria-hidden="true">&#x203A;</span>
		</button>

		<span class="absolute bottom-6 text-xs uppercase tracking-[0.2em] text-ivory/60" aria-live="polite" data-lightbox-counter>1 / <?php echo (int) $ist_count; ?></span>
	</div>
</div>
