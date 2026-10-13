/**
 * Gallery lightbox (port of components/Gallery.tsx), rendered by template-parts/project-gallery.php.
 *
 *   [data-lightbox]                 gallery root
 *     [data-lightbox-open="i"]      tile links (full-size image href = the no-JS fallback);
 *                                   data-src / data-srcset / data-alt / data-caption
 *     [data-lightbox-dialog]        role="dialog" aria-modal, hidden until opened
 *
 * Opens on click / Enter / Space, fades in (0.25s), each image settles from 0.97 (0.3s).
 * Esc or a backdrop click closes; ArrowLeft / ArrowRight and swipes step with wrap-around.
 * Focus is trapped inside the dialog and returned to the tile that opened it; the page behind
 * stops scrolling (Lenis paused too). Motion collapses under prefers-reduced-motion via CSS.
 */
const FADE_MS = 250;

const reducedMotion = () =>
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

function setup( root ) {
	const dialog = root.querySelector( '[data-lightbox-dialog]' );
	const tiles = [ ...root.querySelectorAll( '[data-lightbox-open]' ) ];
	if ( ! dialog || ! tiles.length ) {
		return;
	}

	const image = dialog.querySelector( '[data-lightbox-image]' );
	const figure = image.parentElement;
	const caption = dialog.querySelector( '[data-lightbox-caption]' );
	const counter = dialog.querySelector( '[data-lightbox-counter]' );
	const closeButton = dialog.querySelector( '[data-lightbox-close]' );
	const count = tiles.length;

	// A fixed overlay must not live under a transformed ancestor (scroll reveals), so it moves to <body>.
	document.body.appendChild( dialog );

	let index = 0;
	let trigger = null;
	let previousOverflow = '';
	let hideTimer = 0;
	let touchX = null;

	const preload = ( i ) => {
		const tile = tiles[ ( i + count ) % count ];
		if ( tile.dataset.preloaded ) {
			return;
		}
		const img = new window.Image();
		if ( tile.dataset.srcset ) {
			img.sizes = '100vw';
			img.srcset = tile.dataset.srcset;
		}
		img.src = tile.dataset.src;
		tile.dataset.preloaded = '1';
	};

	const show = ( i ) => {
		index = ( i + count ) % count;
		const tile = tiles[ index ];

		image.removeAttribute( 'srcset' );
		if ( tile.dataset.srcset ) {
			image.srcset = tile.dataset.srcset;
		}
		image.src = tile.dataset.src;
		image.alt = tile.dataset.alt || '';

		const text = tile.dataset.caption || '';
		caption.textContent = text;
		caption.hidden = ! text;
		counter.textContent = `${ index + 1 } / ${ count }`;

		// Restart the settle animation for every image change.
		window.requestAnimationFrame( () => {
			figure.classList.remove( 'is-entering' );
			// eslint-disable-next-line no-unused-expressions
			figure.offsetWidth;
			figure.classList.add( 'is-entering' );
		} );

		if ( count > 1 ) {
			preload( index + 1 );
			preload( index - 1 );
		}
	};

	const focusables = () =>
		[
			...dialog.querySelectorAll(
				'button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])'
			),
		].filter( ( el ) => ! el.hidden && el.offsetParent !== null );

	const onKey = ( event ) => {
		if ( event.key === 'Escape' ) {
			event.preventDefault();
			close();
		} else if ( event.key === 'ArrowRight' ) {
			event.preventDefault();
			show( index + 1 );
		} else if ( event.key === 'ArrowLeft' ) {
			event.preventDefault();
			show( index - 1 );
		} else if ( event.key === 'Tab' ) {
			const items = focusables();
			if ( ! items.length ) {
				event.preventDefault();
				return;
			}
			const first = items[ 0 ];
			const last = items[ items.length - 1 ];
			if (
				event.shiftKey &&
				( dialog.ownerDocument.activeElement === first ||
					! dialog.contains( dialog.ownerDocument.activeElement ) )
			) {
				event.preventDefault();
				last.focus();
			} else if (
				! event.shiftKey &&
				( dialog.ownerDocument.activeElement === last ||
					! dialog.contains( dialog.ownerDocument.activeElement ) )
			) {
				event.preventDefault();
				first.focus();
			}
		}
	};

	// Keep focus inside while open (e.g. a click on the page chrome via assistive tech).
	const onFocusIn = ( event ) => {
		if ( ! dialog.contains( event.target ) ) {
			closeButton.focus();
		}
	};

	function open( i, from ) {
		window.clearTimeout( hideTimer );
		trigger = from || null;
		show( i );

		previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		window.istLenis?.stop();

		dialog.hidden = false;
		window.requestAnimationFrame( () => dialog.classList.add( 'is-open' ) );
		document.addEventListener( 'keydown', onKey );
		document.addEventListener( 'focusin', onFocusIn );
		closeButton.focus( { preventScroll: true } );
	}

	function close() {
		if ( dialog.hidden ) {
			return;
		}
		document.removeEventListener( 'keydown', onKey );
		document.removeEventListener( 'focusin', onFocusIn );
		dialog.classList.remove( 'is-open' );

		const finish = () => {
			dialog.hidden = true;
			figure.classList.remove( 'is-entering' );
		};
		if ( reducedMotion() ) {
			finish();
		} else {
			hideTimer = window.setTimeout( finish, FADE_MS );
		}

		document.body.style.overflow = previousOverflow;
		window.istLenis?.start();
		trigger?.focus( { preventScroll: true } );
		trigger = null;
	}

	tiles.forEach( ( tile ) => {
		// The tile is a link to the full image (no-JS fallback); with JS it behaves as a button.
		tile.setAttribute( 'role', 'button' );
		tile.addEventListener( 'click', ( event ) => {
			if (
				event.metaKey ||
				event.ctrlKey ||
				event.shiftKey ||
				event.button === 1
			) {
				return; // Let "open in new tab" through.
			}
			event.preventDefault();
			open( Number( tile.dataset.lightboxOpen ) || 0, tile );
		} );
		tile.addEventListener( 'keydown', ( event ) => {
			if ( event.key === ' ' ) {
				event.preventDefault();
				open( Number( tile.dataset.lightboxOpen ) || 0, tile );
			}
		} );
	} );

	closeButton.addEventListener( 'click', close );
	dialog
		.querySelector( '[data-lightbox-prev]' )
		?.addEventListener( 'click', () => show( index - 1 ) );
	dialog
		.querySelector( '[data-lightbox-next]' )
		?.addEventListener( 'click', () => show( index + 1 ) );

	// Backdrop click closes; the image and arrows do not (as in the original).
	dialog.addEventListener( 'click', ( event ) => {
		if (
			! event.target.closest( '[data-lightbox-stop]' ) &&
			! event.target.closest( '[data-lightbox-close]' )
		) {
			close();
		}
	} );

	// Swipe left / right on touch screens.
	dialog.addEventListener(
		'touchstart',
		( event ) => {
			touchX =
				event.touches.length === 1 ? event.touches[ 0 ].clientX : null;
		},
		{ passive: true }
	);
	dialog.addEventListener(
		'touchend',
		( event ) => {
			if ( touchX === null ) {
				return;
			}
			const dx = event.changedTouches[ 0 ].clientX - touchX;
			touchX = null;
			if ( Math.abs( dx ) > 50 ) {
				show( index + ( dx < 0 ? 1 : -1 ) );
			}
		},
		{ passive: true }
	);
}

export default function init() {
	document.querySelectorAll( '[data-lightbox]' ).forEach( setup );
}
