<?php
/**
 * Numbered pagination for archives.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_links = paginate_links(
	array(
		'type'      => 'array',
		'prev_text' => __( 'Previous', 'interior-specifics' ),
		'next_text' => __( 'Next', 'interior-specifics' ),
	)
);
if ( ! $ist_links ) {
	return;
}
?>
<nav class="mt-16 flex flex-wrap justify-center gap-2 text-sm" aria-label="<?php esc_attr_e( 'Pagination', 'interior-specifics' ); ?>">
	<?php foreach ( $ist_links as $ist_link ) : ?>
		<span class="[&_a]:inline-flex [&_a]:min-h-11 [&_a]:min-w-11 [&_a]:items-center [&_a]:justify-center [&_a]:border [&_a]:border-line [&_a]:px-4 [&_a]:text-charcoal [&_a:hover]:border-gold [&_a:hover]:text-gold [&_.current]:inline-flex [&_.current]:min-h-11 [&_.current]:min-w-11 [&_.current]:items-center [&_.current]:justify-center [&_.current]:bg-charcoal [&_.current]:px-4 [&_.current]:text-ivory">
			<?php echo wp_kses_post( $ist_link ); ?>
		</span>
	<?php endforeach; ?>
</nav>
