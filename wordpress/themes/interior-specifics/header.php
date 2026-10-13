<?php
/**
 * Site header (port of components/Header.tsx): transparent over heroes, charcoal once scrolled,
 * hover/focus dropdowns on desktop (Escape dismisses), accordion menu on mobile. Behaviour: src/js/modules/header.js.
 *
 * @package InteriorSpecifics
 */

defined( 'ABSPATH' ) || exit;

$ist_menu      = ist_menu_tree( 'primary' );
$ist_cta_label = ist_setting( 'header_cta_label' ) ? ist_setting( 'header_cta_label' ) : __( 'Book Consultation', 'interior-specifics' );
?><!doctype html>
<html <?php language_attributes(); ?> class="h-full antialiased">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-full flex flex-col bg-white text-charcoal' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'interior-specifics' ); ?></a>

<header class="site-header group sticky z-40 transition-colors duration-300 bg-transparent data-scrolled:bg-charcoal/95 data-scrolled:backdrop-blur data-scrolled:supports-[backdrop-filter]:bg-charcoal/80 data-open:bg-charcoal/95 data-open:backdrop-blur data-open:supports-[backdrop-filter]:bg-charcoal/80" data-site-header>
	<div class="<?php echo esc_attr( ist_container_class( 'flex items-center justify-between py-4' ) ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: site name. */ __( '%s — home', 'interior-specifics' ), get_bloginfo( 'name' ) ) ); ?>">
			<?php echo ist_logo( 'h-11 w-11 shrink-0 md:h-12 md:w-12' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<span class="flex flex-col leading-none">
				<span class="font-serif text-lg tracking-wide text-ivory md:text-xl">Interior <span class="text-gold">Specifics</span></span>
				<span class="mt-1 text-[10px] uppercase tracking-[0.3em] text-ivory/60"><?php esc_html_e( 'Lagos · Nigeria', 'interior-specifics' ); ?></span>
			</span>
		</a>

		<nav class="hidden items-center gap-8 lg:flex" aria-label="<?php esc_attr_e( 'Primary', 'interior-specifics' ); ?>">
			<?php foreach ( $ist_menu as $ist_item ) : ?>
				<div class="group/item relative"<?php echo $ist_item['children'] ? ' data-nav-dropdown' : ''; ?>>
					<a href="<?php echo esc_url( $ist_item['url'] ); ?>" class="flex items-center gap-1 py-2 text-sm font-medium uppercase tracking-[0.12em] text-ivory/90 transition-colors hover:text-gold focus-visible:text-gold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold"<?php echo ! empty( $ist_item['current'] ) ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $ist_item['label'] ); ?>
						<?php
						if ( $ist_item['children'] ) {
							echo ist_icon( 'chevron-down', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</a>
					<?php if ( $ist_item['children'] ) : ?>
						<div class="invisible absolute left-1/2 top-full z-50 w-64 -translate-x-1/2 pt-2 opacity-0 transition-all duration-200 group-hover/item:visible group-hover/item:opacity-100 group-focus-within/item:visible group-focus-within/item:opacity-100 group-data-dismissed/item:invisible group-data-dismissed/item:opacity-0">
							<div class="max-h-[70vh] overflow-auto border-t-2 border-gold bg-charcoal py-2 shadow-2xl" data-lenis-prevent>
								<?php foreach ( $ist_item['children'] as $ist_child ) : ?>
									<a href="<?php echo esc_url( $ist_child['url'] ); ?>" class="block px-5 py-2.5 text-sm text-ivory/80 transition-colors hover:bg-charcoal-light hover:text-gold focus-visible:bg-charcoal-light focus-visible:text-gold focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-gold"><?php echo esc_html( $ist_child['label'] ); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( ist_booking_url() ); ?>" class="ml-2 inline-flex min-h-11 items-center bg-terracotta px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-charcoal"><?php echo esc_html( $ist_cta_label ); ?></a>
		</nav>

		<button type="button" class="flex h-11 w-11 items-center justify-center text-ivory lg:hidden" aria-controls="mobile-menu" aria-expanded="false" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'interior-specifics' ); ?></span>
			<span class="space-y-1.5" aria-hidden="true">
				<span class="block h-0.5 w-6 bg-current transition-transform group-data-open:translate-y-2 group-data-open:rotate-45"></span>
				<span class="block h-0.5 w-6 bg-current transition-opacity group-data-open:opacity-0"></span>
				<span class="block h-0.5 w-6 bg-current transition-transform group-data-open:-translate-y-2 group-data-open:-rotate-45"></span>
			</span>
		</button>
	</div>

	<div id="mobile-menu" class="max-h-[calc(100vh-72px)] overflow-auto border-t border-ivory/10 bg-charcoal px-6 pb-8 pt-2 lg:hidden" data-lenis-prevent hidden>
		<nav aria-label="<?php esc_attr_e( 'Mobile', 'interior-specifics' ); ?>">
			<?php foreach ( $ist_menu as $ist_index => $ist_item ) : ?>
				<div class="border-b border-ivory/10">
					<div class="flex items-center justify-between">
						<a href="<?php echo esc_url( $ist_item['url'] ); ?>" class="block py-4 text-sm font-medium uppercase tracking-[0.12em] text-ivory"><?php echo esc_html( $ist_item['label'] ); ?></a>
						<?php if ( $ist_item['children'] ) : ?>
							<button type="button" class="flex h-11 w-11 items-center justify-center text-gold" aria-expanded="false" aria-controls="mobile-sub-<?php echo (int) $ist_index; ?>" data-submenu-toggle>
								<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: menu item. */ __( 'Expand %s', 'interior-specifics' ), $ist_item['label'] ) ); ?></span>
								<?php echo ist_icon( 'chevron-down', 'h-4 w-4 transition-transform' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						<?php endif; ?>
					</div>
					<?php if ( $ist_item['children'] ) : ?>
						<div id="mobile-sub-<?php echo (int) $ist_index; ?>" class="pb-2" hidden>
							<?php foreach ( $ist_item['children'] as $ist_child ) : ?>
								<a href="<?php echo esc_url( $ist_child['url'] ); ?>" class="block py-2.5 pl-4 text-sm text-ivory/70"><?php echo esc_html( $ist_child['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( ist_booking_url() ); ?>" class="mt-6 inline-flex w-full min-h-12 items-center justify-center bg-terracotta px-5 py-3 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal"><?php esc_html_e( 'Book Your Private Consultation', 'interior-specifics' ); ?></a>
		</nav>
	</div>
</header>

<main id="main" class="flex-1" tabindex="-1">
