/**
 * Site-wide smooth scrolling (Lenis), as in components/SmoothScroll.tsx. Lenis drives native
 * window scroll, so position: sticky and scroll listeners keep working. Off under reduced motion.
 */
import Lenis from 'lenis';

export default function init() {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	const lenis = new Lenis( {
		duration: 1.1,
		smoothWheel: true,
		easing: ( t ) => Math.min( 1, 1.001 - Math.pow( 2, -10 * t ) ),
	} );
	window.istLenis = lenis;

	const raf = ( time ) => {
		lenis.raf( time );
		window.requestAnimationFrame( raf );
	};
	window.requestAnimationFrame( raf );

	// In-page anchors glide too. Lenis already honours html's scroll-padding-top (the sticky-header
	// offset, main.css) and the target's scroll-margin-top, so no extra offset: targets land exactly
	// where a native jump would.
	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a[href*="#"]' );
		if (
			! link ||
			link.pathname !== window.location.pathname ||
			! link.hash ||
			link.hash === '#'
		) {
			return;
		}
		const target = document.getElementById(
			decodeURIComponent( link.hash.slice( 1 ) )
		);
		if ( target ) {
			event.preventDefault();
			lenis.scrollTo( target );
			history.pushState( null, '', link.hash );
			focusTarget( target );
		}
	} );
}

/**
 * Move focus to an in-page target, as the browser's own fragment navigation would (skip link,
 * #directors…), so the next Tab continues from there. Non-focusable targets get a temporary
 * tabindex, without a ring around the whole section.
 *
 * @param {HTMLElement} target Anchor target.
 */
function focusTarget( target ) {
	if (
		! target.matches(
			'a[href], button, input, select, textarea, [tabindex]'
		)
	) {
		target.setAttribute( 'tabindex', '-1' );
		target.style.outline = 'none';
		target.addEventListener(
			'blur',
			() => {
				target.removeAttribute( 'tabindex' );
				target.style.removeProperty( 'outline' );
			},
			{ once: true }
		);
	}
	target.focus( { preventScroll: true } );
}
