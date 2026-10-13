<?php
/**
 * Styles, scripts, fonts and the motion-capability flag.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: the file's mtime in every environment, so each rebuild or deploy busts browser
 * and CDN caches without a theme Version bump (safe with long-lived Cache-Control headers).
 *
 * @param string $relative Path relative to the theme root.
 */
function ist_asset_version( string $relative ): string {
	$path = IST_DIR . '/' . $relative;
	return file_exists( $path ) ? (string) filemtime( $path ) : IST_VERSION;
}

add_action( 'wp_head', 'ist_head_flags', 0 );
/**
 * Runs before first paint: marks JS support and, unless reduced motion is requested, enables
 * the hidden start states of scroll reveals. If the theme script has not booted within 3s
 * (blocked, errored), motion is switched off again so content can never stay hidden.
 */
function ist_head_flags(): void {
	?>
	<script>
	(function(d,w){var h=d.documentElement;h.classList.add('js');if(!(w.matchMedia&&w.matchMedia('(prefers-reduced-motion: reduce)').matches)){h.classList.add('js-motion');w.setTimeout(function(){if(!w.__istReady){h.classList.remove('js-motion');}},3000);}})(document,window);
	</script>
	<?php
}

add_action( 'wp_head', 'ist_preload_fonts', 1 );
/**
 * Preload the fonts used above the fold: the two variable fonts and the Cormorant italic of the
 * hero headline accents.
 */
function ist_preload_fonts(): void {
	foreach ( array( 'playfair-display-latin-wght-normal.woff2', 'inter-latin-wght-normal.woff2', 'cormorant-latin-400-italic.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( IST_URI . '/assets/fonts/' . $font ) );
	}
}

add_action( 'enqueue_block_assets', 'ist_enqueue_styles' );
/**
 * The compiled stylesheet, on the front end and inside the editor canvas so block previews match.
 *
 * Core also fires enqueue_block_assets from admin_enqueue_scripts for the outer editor page,
 * where the unlayered heading and paragraph rules would restyle the admin UI. The canvas iframe
 * collects its assets separately (get_block_editor_settings(), before admin_enqueue_scripts),
 * so skipping that pass keeps the theme inside the canvas.
 */
function ist_enqueue_styles(): void {
	if ( is_admin() && doing_action( 'admin_enqueue_scripts' ) ) {
		return;
	}
	wp_enqueue_style( 'interior-specifics', IST_URI . '/build/css/main.css', array(), ist_asset_version( 'build/css/main.css' ) );
}

add_action( 'wp_enqueue_scripts', 'ist_enqueue_scripts' );
/**
 * Front-end behaviour bundle (header, smooth scroll, reveals, forms, carousels, lightbox...).
 */
function ist_enqueue_scripts(): void {
	$asset_file = IST_DIR . '/build/js/main.asset.php';
	$asset      = file_exists( $asset_file ) ? require $asset_file : array(
		'dependencies' => array(),
		'version'      => IST_VERSION,
	);

	wp_enqueue_script(
		'interior-specifics',
		IST_URI . '/build/js/main.js',
		$asset['dependencies'],
		$asset['version'],
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'interior-specifics',
		'istConfig',
		array(
			'leadEndpoint' => esc_url_raw( rest_url( 'is/v1/lead' ) ),
			'ga4'          => ist_setting( 'ga4_id' ),
			'i18n'         => array(
				'sending'     => __( 'Sending…', 'interior-specifics' ),
				'subscribing' => __( 'Subscribing…', 'interior-specifics' ),
				'close'       => __( 'Close', 'interior-specifics' ),
				'previous'    => __( 'Previous', 'interior-specifics' ),
				'next'        => __( 'Next', 'interior-specifics' ),
			),
		)
	);

	// Comments are closed site-wide; never load the reply script.
	wp_dequeue_script( 'comment-reply' );
}

// Emoji detection script and styles are dead weight on this site.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
