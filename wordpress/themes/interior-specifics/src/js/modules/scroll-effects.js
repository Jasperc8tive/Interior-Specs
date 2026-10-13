/**
 * Scroll-linked transforms, written once per animation frame (no layout thrash).
 *
 * Scrub (hero exits — PageHero / StoryHero):
 *   <section data-scrub>
 *     <div data-scrub-media="scale:0.12;y:80">   scale 1→1.12, translateY 0→80px as the section scrolls away
 *     <div data-scrub-fade="end:0.6;y:-80">      opacity 1→0 by 60% progress, optional drift
 *         (add yend:0.55 to finish the drift at a different progress than the fade)
 *   progress = 0 when the section top is at the viewport top, 1 when its bottom reaches it.
 *
 * Parallax (FounderNote / About profile image):
 *   <img data-parallax="40">  translateY +40px → −40px while it crosses the viewport, eased (lerp)
 *
 * Disabled under prefers-reduced-motion.
 */

/**
 * Parse a "key:value;key:value" attribute into numbers.
 *
 * @param {string} spec Attribute value.
 * @return {Object<string, number>} Parsed values.
 */
const parse = ( spec = '' ) =>
	Object.fromEntries(
		spec
			.split( ';' )
			.map( ( pair ) => pair.split( ':' ).map( ( s ) => s.trim() ) )
			.filter( ( [ k, v ] ) => k && v !== undefined )
			.map( ( [ k, v ] ) => [ k, parseFloat( v ) ] )
	);

const clamp = ( v, min = 0, max = 1 ) => Math.min( max, Math.max( min, v ) );

export default function init() {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	const scrubs = [ ...document.querySelectorAll( '[data-scrub]' ) ].map(
		( section ) => ( {
			section,
			media: [ ...section.querySelectorAll( '[data-scrub-media]' ) ].map(
				( el ) => ( { el, cfg: parse( el.dataset.scrubMedia ) } )
			),
			fades: [ ...section.querySelectorAll( '[data-scrub-fade]' ) ].map(
				( el ) => ( { el, cfg: parse( el.dataset.scrubFade ) } )
			),
		} )
	);

	const parallax = [ ...document.querySelectorAll( '[data-parallax]' ) ].map(
		( el ) => ( {
			el,
			distance: parseFloat( el.dataset.parallax ) || 40,
			current: null,
		} )
	);

	if ( ! scrubs.length && ! parallax.length ) {
		return;
	}

	let ticking = false;
	const frame = () => {
		ticking = false;
		const vh = window.innerHeight;

		scrubs.forEach( ( { section, media, fades } ) => {
			const rect = section.getBoundingClientRect();
			if ( rect.bottom < 0 || rect.top > vh ) {
				return;
			}
			const p = clamp( -rect.top / rect.height );
			media.forEach( ( { el, cfg } ) => {
				el.style.transform = `translate3d(0, ${
					( cfg.y || 0 ) * p
				}px, 0) scale(${ 1 + ( cfg.scale || 0 ) * p })`;
			} );
			fades.forEach( ( { el, cfg } ) => {
				const end = cfg.end || 0.6;
				el.style.opacity = String( 1 - clamp( p / end ) );
				if ( cfg.y ) {
					// The drift may finish later than the fade (yend), as in StoryHero.
					const yEnd = cfg.yend || end;
					el.style.transform = `translate3d(0, ${
						cfg.y * clamp( p / yEnd )
					}px, 0)`;
				}
			} );
		} );

		let settling = false;
		parallax.forEach( ( item ) => {
			const rect = item.el.getBoundingClientRect();
			const p = clamp( ( vh - rect.top ) / ( vh + rect.height ) );
			const target = item.distance - 2 * item.distance * p;
			item.current =
				item.current === null
					? target
					: item.current + ( target - item.current ) * 0.12;
			if ( Math.abs( target - item.current ) > 0.1 ) {
				settling = true;
			}
			item.el.style.transform = `translate3d(0, ${ item.current.toFixed(
				2
			) }px, 0)`;
		} );

		// Keep easing parallax toward its target after scrolling stops.
		if ( settling ) {
			request();
		}
	};
	const request = () => {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( frame );
		}
	};

	window.addEventListener( 'scroll', request, { passive: true } );
	window.addEventListener( 'resize', request, { passive: true } );
	request();
}
