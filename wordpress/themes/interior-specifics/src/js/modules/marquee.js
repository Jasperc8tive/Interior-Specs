/**
 * Credential Marquee pause / play (WCAG 2.2.2). The loop itself is CSS (home-hero.css): it
 * pauses while hovered and stops for good while the block carries data-paused. The button stays
 * hidden without JavaScript and under reduced motion, where the band is static anyway.
 */
export default function init() {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}
	document.querySelectorAll( '[data-marquee]' ).forEach( ( root ) => {
		const button = root.querySelector( '[data-marquee-toggle]' );
		if ( ! button ) {
			return;
		}
		button.hidden = false;
		button.addEventListener( 'click', () => {
			const paused = ! root.hasAttribute( 'data-paused' );
			root.toggleAttribute( 'data-paused', paused );
			button.setAttribute(
				'aria-label',
				paused ? button.dataset.labelPlay : button.dataset.labelPause
			);
		} );
	} );
}
