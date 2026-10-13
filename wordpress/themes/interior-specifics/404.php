<?php
/**
 * 404 (port of app/not-found.tsx).
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="-mt-[88px] flex min-h-[70vh] items-center bg-charcoal pt-[88px]">
	<div class="<?php echo esc_attr( ist_container_class( 'py-20 text-center' ) ); ?>">
		<span class="font-serif text-6xl text-gold">404</span>
		<h1 class="mt-6 text-3xl text-ivory"><?php esc_html_e( 'This page could not be found', 'interior-specifics' ); ?></h1>
		<p class="mx-auto mt-4 max-w-md text-ivory/70"><?php esc_html_e( "The page you're looking for may have moved. Let's get you back to something beautiful.", 'interior-specifics' ); ?></p>
		<div class="mt-8 flex flex-wrap justify-center gap-4">
			<?php
			echo ist_button( __( 'Return Home', 'interior-specifics' ), home_url( '/' ), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			if ( post_type_exists( 'is_project' ) ) {
				echo ist_button( __( 'View Our Work', 'interior-specifics' ), (string) get_post_type_archive_link( 'is_project' ), 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
	</div>
</section>
<?php
get_footer();
