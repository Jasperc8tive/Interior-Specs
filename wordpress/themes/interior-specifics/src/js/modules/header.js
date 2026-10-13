/**
 * Header: solid background once scrolled past 24px, mobile menu + accordions, Escape for dropdowns.
 * State is exposed as data-scrolled / data-open on the header for Tailwind data-* variants.
 */
export default function init() {
	const header = document.querySelector( '[data-site-header]' );
	if ( ! header ) {
		return;
	}
	const toggle = header.querySelector( '[data-menu-toggle]' );
	const panel = document.getElementById( 'mobile-menu' );

	const onScroll = () =>
		header.toggleAttribute( 'data-scrolled', window.scrollY > 24 );
	onScroll();
	window.addEventListener( 'scroll', onScroll, { passive: true } );

	const setOpen = ( open ) => {
		if ( ! toggle || ! panel ) {
			return;
		}
		panel.hidden = ! open;
		header.toggleAttribute( 'data-open', open );
		toggle.setAttribute( 'aria-expanded', String( open ) );
	};

	toggle?.addEventListener( 'click', () => setOpen( panel.hidden ) );

	// Desktop dropdowns open on hover / focus-within; Escape dismisses the open one (WCAG 1.4.13)
	// and returns focus to its top-level link. data-dismissed hides the panel until the pointer
	// or focus leaves that item.
	const dropdowns = [ ...header.querySelectorAll( '[data-nav-dropdown]' ) ];
	dropdowns.forEach( ( item ) => {
		item.addEventListener( 'focusout', ( event ) => {
			if (
				! item.contains( event.relatedTarget ) &&
				! item.matches( ':hover' )
			) {
				item.removeAttribute( 'data-dismissed' );
			}
		} );
		item.addEventListener( 'mouseleave', () => {
			if ( ! item.contains( item.ownerDocument.activeElement ) ) {
				item.removeAttribute( 'data-dismissed' );
			}
		} );
	} );
	const dismissDropdown = () => {
		const item = dropdowns.find(
			( el ) =>
				! el.hasAttribute( 'data-dismissed' ) &&
				( el.matches( ':hover' ) ||
					el.contains( el.ownerDocument.activeElement ) )
		);
		if ( ! item ) {
			return;
		}
		item.setAttribute( 'data-dismissed', '' );
		if ( item.contains( item.ownerDocument.activeElement ) ) {
			item.querySelector( 'a' )?.focus();
		}
	};

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Escape' ) {
			return;
		}
		if ( panel && ! panel.hidden ) {
			setOpen( false );
			toggle.focus();
		} else {
			dismissDropdown();
		}
	} );

	// Same-page anchor links (e.g. /about/#directors while on /about/) close the menu.
	panel?.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a' );
		if ( link && link.hash && link.pathname === window.location.pathname ) {
			setOpen( false );
		}
	} );

	// Closing at the desktop breakpoint avoids a stale open state after rotating a tablet.
	window
		.matchMedia( '(min-width: 1024px)' )
		.addEventListener( 'change', ( mq ) => mq.matches && setOpen( false ) );

	header.querySelectorAll( '[data-submenu-toggle]' ).forEach( ( button ) => {
		const sub = document.getElementById(
			button.getAttribute( 'aria-controls' )
		);
		button.addEventListener( 'click', () => {
			const open = sub.hidden;
			// One open section at a time, as in the original.
			header
				.querySelectorAll(
					'[data-submenu-toggle][aria-expanded="true"]'
				)
				.forEach( ( other ) => {
					if ( other !== button ) {
						other.setAttribute( 'aria-expanded', 'false' );
						other
							.querySelector( 'svg' )
							?.classList.remove( 'rotate-180' );
						document.getElementById(
							other.getAttribute( 'aria-controls' )
						).hidden = true;
					}
				} );
			sub.hidden = ! open;
			button.setAttribute( 'aria-expanded', String( open ) );
			button
				.querySelector( 'svg' )
				?.classList.toggle( 'rotate-180', open );
		} );
	} );
}
