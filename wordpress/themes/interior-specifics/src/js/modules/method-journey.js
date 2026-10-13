/**
 * Method Journey scrollytelling (port of components/home/MethodJourney.tsx, PinnedJourney).
 *
 * On md+ screens with motion allowed the section is pinned (.is-pinned): it is N × 100vh tall
 * with a sticky 100vh stage, and scroll progress through it selects the active act:
 *
 *   v   = clamp( -sectionTop / ( sectionHeight - innerHeight ), 0, 1 )
 *   idx = min( N - 1, floor( v × N ) )
 *
 * Only the discrete index is written (.is-active on scenes and rail items, when it changes);
 * the cross-fades are CSS transitions (src/css/parts/home-method.css). Rail items are buttons
 * that glide to their act. Everywhere else the server-rendered stacked layout stays as is.
 */
const DESKTOP = '(min-width: 768px)';
const REDUCED = '(prefers-reduced-motion: reduce)';

/**
 * Subscribe to a media query change (with the Safari < 14 fallback).
 *
 * @param {MediaQueryList} mql      Media query list.
 * @param {Function}       callback Listener.
 */
function onMediaChange( mql, callback ) {
	if ( mql.addEventListener ) {
		mql.addEventListener( 'change', callback );
	} else if ( mql.addListener ) {
		mql.addListener( callback );
	}
}

/**
 * Smoothly scroll the window to an absolute position (via Lenis when it is running).
 *
 * @param {number} top Target scroll position.
 */
function glideTo( top ) {
	if ( window.istLenis && typeof window.istLenis.scrollTo === 'function' ) {
		window.istLenis.scrollTo( top );
	} else {
		window.scrollTo( { top, behavior: 'smooth' } );
	}
}

/**
 * Wire one journey section.
 *
 * @param {HTMLElement}    section Section element.
 * @param {MediaQueryList} desktop Desktop media query.
 * @param {MediaQueryList} reduced Reduced-motion media query.
 */
function setup( section, desktop, reduced ) {
	const scenes = Array.from(
		section.querySelectorAll( '[data-method-scene]' )
	);
	const count = scenes.length;
	if ( ! count ) {
		return;
	}
	const railItems = Array.from(
		section.querySelectorAll( '[data-method-rail]' )
	);

	let pinned = false;
	let active = scenes.findIndex( ( scene ) =>
		scene.classList.contains( 'is-active' )
	);
	let frame = 0;

	const setActive = ( index ) => {
		if ( index === active ) {
			return;
		}
		active = index;
		scenes.forEach( ( scene, i ) =>
			scene.classList.toggle( 'is-active', i === index )
		);
		railItems.forEach( ( item, i ) => {
			item.classList.toggle( 'is-active', i === index );
			const button = item.querySelector( 'button' );
			if ( button ) {
				if ( i === index ) {
					button.setAttribute( 'aria-current', 'step' );
				} else {
					button.removeAttribute( 'aria-current' );
				}
			}
		} );
	};

	// Runs inside requestAnimationFrame: one layout read, then class writes only on change.
	const update = () => {
		frame = 0;
		const shouldPin = desktop.matches && ! reduced.matches;
		if ( shouldPin !== pinned ) {
			pinned = shouldPin;
			section.classList.toggle( 'is-pinned', pinned );
		}
		section.classList.add( 'is-ready' );
		if ( ! pinned ) {
			return;
		}
		const rect = section.getBoundingClientRect();
		const span = rect.height - window.innerHeight;
		const progress =
			span > 0 ? Math.min( 1, Math.max( 0, -rect.top / span ) ) : 0;
		setActive( Math.min( count - 1, Math.floor( progress * count ) ) );
	};

	const schedule = () => {
		if ( ! frame ) {
			frame = window.requestAnimationFrame( update );
		}
	};

	railItems.forEach( ( item, i ) => {
		const button = item.querySelector( 'button' );
		if ( ! button ) {
			return;
		}
		button.addEventListener( 'click', () => {
			if ( ! pinned ) {
				return;
			}
			const rect = section.getBoundingClientRect();
			const span = rect.height - window.innerHeight;
			// The middle of the act's slice of the scroll range.
			glideTo(
				window.scrollY + rect.top + ( ( i + 0.5 ) * span ) / count
			);
		} );
	} );

	// Scrolling only matters while pinned; resize and media changes re-evaluate the mode.
	window.addEventListener( 'scroll', () => pinned && schedule(), {
		passive: true,
	} );
	window.addEventListener( 'resize', schedule, { passive: true } );
	onMediaChange( desktop, schedule );
	onMediaChange( reduced, schedule );
	schedule();
}

export default function init() {
	const sections = document.querySelectorAll( '[data-method-journey]' );
	if ( ! sections.length || ! window.matchMedia ) {
		return;
	}
	const desktop = window.matchMedia( DESKTOP );
	const reduced = window.matchMedia( REDUCED );
	sections.forEach( ( section ) => setup( section, desktop, reduced ) );
}
