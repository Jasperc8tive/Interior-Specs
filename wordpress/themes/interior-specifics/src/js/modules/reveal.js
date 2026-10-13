/**
 * Scroll reveals — the vanilla equivalent of Framer Motion's whileInView (once).
 *
 *   [data-reveal]               rises/fades in (CSS in main.css)
 *   [data-reveal-stagger="0.09"] parent whose [data-reveal-item] children reveal in turn
 *   .ist-words                  masked word-by-word heading
 *   [data-reveal-amount="0.15"] optional: share of the element that must be visible to fire
 *
 * Default amounts follow the original's viewport settings: 0.2 for section reveals
 * (SectionReveal), 0.15 for staggered grids, 0.3 for everything else (viewportOnce in lib/anim.ts).
 *
 * Hidden states only exist under html.js-motion, so without JS / with reduced motion there is
 * nothing to undo. Each element is observed once, then released.
 */
const SELECTOR = '[data-reveal], [data-reveal-stagger], .ist-words';

export default function init() {
	const root = document.documentElement;
	const elements = document.querySelectorAll( SELECTOR );
	if ( ! elements.length ) {
		return;
	}

	// Number stagger children once, so CSS can delay each by --i × --stagger.
	document
		.querySelectorAll( '[data-reveal-stagger]' )
		.forEach( ( parent ) => {
			const gap = parseFloat( parent.dataset.revealStagger ) || 0.09;
			parent.style.setProperty( '--stagger', `${ gap }s` );
			parent
				.querySelectorAll( '[data-reveal-item]' )
				.forEach( ( item, i ) => {
					if ( ! item.style.getPropertyValue( '--i' ) ) {
						item.style.setProperty( '--i', String( i ) );
					}
				} );
		} );

	if (
		! root.classList.contains( 'js-motion' ) ||
		! ( 'IntersectionObserver' in window )
	) {
		elements.forEach( ( el ) => el.classList.add( 'is-in' ) );
		return;
	}

	// One observer per threshold, as Framer Motion's whileInView creates per `amount`.
	const observers = new Map();
	const observerFor = ( amount ) => {
		if ( ! observers.has( amount ) ) {
			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							entry.target.classList.add( 'is-in' );
							observer.unobserve( entry.target );
						}
					} );
				},
				{ threshold: amount }
			);
			observers.set( amount, observer );
		}
		return observers.get( amount );
	};

	elements.forEach( ( el ) => {
		let amount = parseFloat( el.dataset.revealAmount );
		if ( ! ( amount >= 0 && amount <= 1 ) ) {
			amount = defaultAmount( el );
		}
		// An element too tall to ever be that visible fires once it fills most of the viewport.
		const height = el.getBoundingClientRect().height;
		if ( height > 0 ) {
			amount = Math.min( amount, ( window.innerHeight * 0.9 ) / height );
		}
		observerFor( Math.round( amount * 1000 ) / 1000 ).observe( el );
	} );
}

/**
 * Viewport amount for an element without data-reveal-amount.
 *
 * @param {HTMLElement} el Reveal element.
 * @return {number} Threshold between 0 and 1.
 */
function defaultAmount( el ) {
	if ( el.hasAttribute( 'data-reveal-stagger' ) ) {
		return 0.15;
	}
	return 'section' === el.dataset.reveal ? 0.2 : 0.3;
}

/**
 * Reveal elements added after load (e.g. filtered grids, carousels).
 *
 * @param {Element|Document} scope Container whose reveal elements should show immediately.
 */
export function revealNow( scope ) {
	scope
		.querySelectorAll( SELECTOR )
		.forEach( ( el ) => el.classList.add( 'is-in' ) );
}
