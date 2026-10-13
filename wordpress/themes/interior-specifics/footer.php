<?php
/**
 * Site footer (port of components/Footer.tsx) + floating WhatsApp button.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_explore = ist_menu_tree( 'footer' );
$ist_legal   = ist_menu_tree( 'legal' );
$ist_socials = function_exists( 'isp_socials' ) ? isp_socials() : array();
// First two letters of each network, as Footer.tsx renders name.slice( 0, 2 ).
$ist_social_abbr = array(
	'instagram' => 'In',
	'linkedin'  => 'Li',
	'pinterest' => 'Pi',
	'youtube'   => 'Yo',
);
?>
</main>

<footer class="bg-charcoal text-ivory/80">
	<div class="<?php echo esc_attr( ist_container_class( 'py-16' ) ); ?>">
		<div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
			<div class="lg:col-span-1">
				<div class="flex items-center gap-3">
					<?php echo ist_logo( 'h-12 w-12 shrink-0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
					<span class="font-serif text-2xl text-ivory">Interior <span class="text-gold">Specifics</span></span>
				</div>
				<p class="mt-4 max-w-xs font-accent text-lg italic text-ivory/70"><?php echo esc_html( ist_setting( 'tagline' ) ); ?></p>
			</div>

			<div>
				<h2 class="mb-4 font-sans text-sm font-semibold uppercase tracking-[0.2em] text-gold"><?php esc_html_e( 'Explore', 'interior-specifics' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<?php foreach ( $ist_explore as $ist_item ) : ?>
						<li><a href="<?php echo esc_url( $ist_item['url'] ); ?>" class="transition-colors hover:text-gold"><?php echo esc_html( $ist_item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h2 class="mb-4 font-sans text-sm font-semibold uppercase tracking-[0.2em] text-gold"><?php esc_html_e( 'Contact', 'interior-specifics' ); ?></h2>
				<ul class="space-y-2.5 text-sm">
					<li><?php echo esc_html( ist_address() ); ?></li>
					<li><a href="<?php echo esc_url( ist_phone_href() ); ?>" class="hover:text-gold" data-track="phone"><?php echo esc_html( ist_setting( 'phone_display' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( 'mailto:' . ist_setting( 'email' ) ); ?>" class="hover:text-gold" data-track="email"><?php echo esc_html( ist_setting( 'email' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( ist_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-gold" data-track="whatsapp"><?php esc_html_e( 'WhatsApp Us', 'interior-specifics' ); ?></a></li>
					<li class="text-ivory/60"><?php echo esc_html( ist_setting( 'hours' ) ); ?></li>
				</ul>
			</div>

			<div>
				<h2 class="mb-4 font-sans text-sm font-semibold uppercase tracking-[0.2em] text-gold"><?php esc_html_e( 'Newsletter', 'interior-specifics' ); ?></h2>
				<p class="mb-4 text-sm text-ivory/70"><?php esc_html_e( 'Design insights for the discerning, straight to your inbox.', 'interior-specifics' ); ?></p>
				<?php
				if ( function_exists( 'ist_lead_form' ) ) {
					echo ist_lead_form( 'newsletter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
				}
				?>
				<?php if ( $ist_socials ) : ?>
					<div class="mt-6 flex gap-4">
						<?php foreach ( $ist_socials as $ist_network => $ist_url ) : ?>
							<a href="<?php echo esc_url( $ist_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $ist_network ) ); ?>" class="text-xs uppercase tracking-[0.15em] text-ivory/60 transition-colors hover:text-gold"><?php echo esc_html( $ist_social_abbr[ $ist_network ] ?? substr( $ist_network, 0, 2 ) ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-ivory/10 pt-6 text-xs text-ivory/50 md:flex-row">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( ist_setting( 'legal_name' ) ); ?> <?php esc_html_e( 'All rights reserved.', 'interior-specifics' ); ?></p>
			<?php if ( $ist_legal ) : ?>
				<nav class="flex gap-6" aria-label="<?php esc_attr_e( 'Legal', 'interior-specifics' ); ?>">
					<?php foreach ( $ist_legal as $ist_item ) : ?>
						<a href="<?php echo esc_url( $ist_item['url'] ); ?>" class="hover:text-gold"><?php echo esc_html( $ist_item['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>

	<?php // Inside the footer landmark so the floating button belongs to a region (fixed, so placement is unchanged). ?>
	<a href="<?php echo esc_url( ist_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'interior-specifics' ); ?>" class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 transition-transform duration-300 hover:scale-105 md:bottom-7 md:right-7" data-track="whatsapp">
		<?php echo ist_icon( 'whatsapp', 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
</footer>

<?php wp_footer(); ?>
</body>
</html>
