/**
 * Count-up numbers — the vanilla equivalent of components/home/Counter.tsx.
 *
 *   <span data-count="200">200+</span>   counts 0 → 200 once ~60% visible, keeping "+" (and any
 *                                        prefix such as "₦") exactly as rendered
 *   <span data-count-fade>Lagos</span>   non-numeric values fade + rise in instead (CSS in home-hero.css)
 *
 * The server renders the final value, so without JS, for crawlers and under reduced motion the
 * real number is always shown. Only when motion is allowed (html.js-motion) is the figure reset
 * to zero before it is seen, then tweened over 1.6s on the site's lux curve, cubic-bezier(.22,1,.36,1).
 */
const DURATION = 1600;
const PATTERN = /^(\D*)(\d[\d,]*)(.*)$/s;

/**
 * Cubic-bezier easing (x1, y1, x2, y2), solved for x with Newton-Raphson and bisection fallback.
 *
 * @param {number} x1
 * @param {number} y1
 * @param {number} x2
 * @param {number} y2
 * @return {(t: number) => number} Easing function.
 */
function bezier( x1, y1, x2, y2 ) {
	const a = ( p1, p2 ) => 1 - 3 * p2 + 3 * p1;
	const b = ( p1, p2 ) => 3 * p2 - 6 * p1;
	const c = ( p1 ) => 3 * p1;
	const at = ( t, p1, p2 ) =>
		( ( a( p1, p2 ) * t + b( p1, p2 ) ) * t + c( p1 ) ) * t;
	const slope = ( t, p1, p2 ) =>
		3 * a( p1, p2 ) * t * t + 2 * b( p1, p2 ) * t + c( p1 );

	const solve = ( x ) => {
		let t = x;
		for ( let i = 0; i < 8; i++ ) {
			const d = slope( t, x1, x2 );
			if ( Math.abs( d ) < 1e-6 ) {
				break;
			}
			const err = at( t, x1, x2 ) - x;
			if ( Math.abs( err ) < 1e-6 ) {
				return t;
			}
			t -= err / d;
		}
		let lo = 0;
		let hi = 1;
		t = x;
		for ( let i = 0; i < 30; i++ ) {
			const v = at( t, x1, x2 );
			if ( Math.abs( v - x ) < 1e-6 ) {
				break;
			}
			if ( v < x ) {
				lo = t;
			} else {
				hi = t;
			}
			t = ( lo + hi ) / 2;
		}
		return t;
	};

	return ( x ) => {
		if ( x <= 0 ) {
			return 0;
		}
		if ( x >= 1 ) {
			return 1;
		}
		return at( solve( x ), y1, y2 );
	};
}

const ease = bezier( 0.22, 1, 0.36, 1 );

export default function init() {
	const numbers = [ ...document.querySelectorAll( '[data-count]' ) ];
	const fades = [ ...document.querySelectorAll( '[data-count-fade]' ) ];
	if ( ! numbers.length && ! fades.length ) {
		return;
	}

	const motion =
		document.documentElement.classList.contains( 'js-motion' ) &&
		'IntersectionObserver' in window;
	if ( ! motion ) {
		fades.forEach( ( el ) => el.classList.add( 'is-in' ) );
		return;
	}

	const lang = document.documentElement.lang || undefined;
	const format = ( n ) => {
		try {
			return Math.round( n ).toLocaleString( lang );
		} catch ( error ) {
			return String( Math.round( n ) );
		}
	};

	// Split each figure into static prefix / animated number / static suffix, then show zero.
	const counters = new Map();
	numbers.forEach( ( el ) => {
		const text = el.textContent.trim();
		const match = text.match( PATTERN );
		const target = match ? Number( match[ 2 ].replace( /,/g, '' ) ) : NaN;
		if ( ! match || ! Number.isFinite( target ) ) {
			return;
		}
		const digits = document.createElement( 'span' );
		digits.textContent = format( 0 );
		el.textContent = '';
		el.append( match[ 1 ], digits, match[ 3 ] );
		counters.set( el, { digits, target, final: match[ 2 ] } );
	} );

	const run = ( el ) => {
		const counter = counters.get( el );
		if ( ! counter ) {
			return;
		}
		const { digits, target, final } = counter;
		let start = null;
		let last = '';
		const step = ( now ) => {
			if ( start === null ) {
				start = now;
			}
			const progress = Math.min( 1, ( now - start ) / DURATION );
			// Land on the exact authored figure (e.g. keeps "1,200" as written).
			const next =
				progress < 1 ? format( target * ease( progress ) ) : final;
			if ( next !== last ) {
				digits.textContent = next;
				last = next;
			}
			if ( progress < 1 ) {
				window.requestAnimationFrame( step );
			}
		};
		window.requestAnimationFrame( step );
	};

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) {
					return;
				}
				observer.unobserve( entry.target );
				if ( entry.target.hasAttribute( 'data-count-fade' ) ) {
					entry.target.classList.add( 'is-in' );
				} else {
					run( entry.target );
				}
			} );
		},
		{ threshold: 0.6 }
	);

	counters.forEach( ( _, el ) => observer.observe( el ) );
	fades.forEach( ( el ) => observer.observe( el ) );
}
