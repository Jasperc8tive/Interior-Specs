/**
 * Testimonials carousel (port of components/TestimonialCarousel.tsx).
 *
 *   [data-testimonials data-autoplay="7000"]   root (autoplay ms, 0 = off)
 *     [data-testimonials-stage aria-live]       slides wrapper
 *       [data-testimonial-slide]                every slide is server-rendered; init hides all but one
 *     [data-testimonial-prev] [data-testimonial-next] [data-testimonial-dot="n"]
 *
 * Mirrors Framer Motion's AnimatePresence mode="wait": the outgoing quote fades out and up
 * (opacity, y −16, 500ms), then the incoming one fades in from y 16 (keyframes in
 * home-stories.css). Adds what the original lacked: ArrowLeft / ArrowRight keys, touch swipe,
 * aria-current dots, a polite live region and an optional autoplay that pauses on hover/focus
 * and has a stop / start button [data-testimonial-autoplay].
 * Under prefers-reduced-motion slides swap instantly and autoplay is skipped.
 */
const reducedMotion = () =>
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Run `done` once the element's CSS animation ends (with a timeout fallback).
 *
 * @param {HTMLElement} el   Animated element.
 * @param {Function}    done Callback.
 */
function afterAnimation( el, done ) {
	let finished = false;
	const finish = () => {
		if ( finished ) {
			return;
		}
		finished = true;
		el.removeEventListener( 'animationend', onEnd );
		window.clearTimeout( timer );
		done();
	};
	const onEnd = ( event ) => {
		if ( event.target === el ) {
			finish();
		}
	};
	const timer = window.setTimeout( finish, 700 );
	el.addEventListener( 'animationend', onEnd );
}

/**
 * Wire one carousel.
 *
 * @param {HTMLElement} root Element with [data-testimonials].
 */
function setup( root ) {
	const slides = [ ...root.querySelectorAll( '[data-testimonial-slide]' ) ];
	const count = slides.length;
	if ( count < 2 ) {
		return;
	}

	const stage = root.querySelector( '[data-testimonials-stage]' );
	const dots = [ ...root.querySelectorAll( '[data-testimonial-dot]' ) ];
	const prev = root.querySelector( '[data-testimonial-prev]' );
	const next = root.querySelector( '[data-testimonial-next]' );

	let index = 0;
	let animating = false;
	let queued = null;

	const paintDots = () => {
		dots.forEach( ( dot, i ) => {
			const active = i === index;
			dot.classList.toggle( 'bg-gold', active );
			dot.classList.toggle( 'bg-ivory/30', ! active );
			if ( active ) {
				dot.setAttribute( 'aria-current', 'true' );
			} else {
				dot.removeAttribute( 'aria-current' );
			}
		} );
	};

	const show = ( slide ) => {
		slides.forEach( ( s ) => {
			s.hidden = s !== slide;
			s.classList.remove( 'is-leaving', 'is-entering' );
		} );
	};

	const go = ( target ) => {
		const to = ( ( target % count ) + count ) % count;
		if ( animating ) {
			queued = to;
			return;
		}
		if ( to === index ) {
			return;
		}

		const outgoing = slides[ index ];
		const incoming = slides[ to ];
		index = to;

		window.requestAnimationFrame( () => {
			paintDots();

			if ( reducedMotion() ) {
				show( incoming );
				return;
			}

			animating = true;
			outgoing.classList.add( 'is-leaving' );
			afterAnimation( outgoing, () => {
				window.requestAnimationFrame( () => {
					show( incoming );
					incoming.classList.add( 'is-entering' );
					afterAnimation( incoming, () => {
						incoming.classList.remove( 'is-entering' );
						animating = false;
						if ( null !== queued ) {
							const nextTarget = queued;
							queued = null;
							go( nextTarget );
						}
					} );
				} );
			} );
		} );
	};

	// Relative steps count from the slide already on its way in, so quick repeated presses add up.
	const step = ( delta ) =>
		go( ( null !== queued ? queued : index ) + delta );

	// Take over from the pre-boot CSS (which shows only the first slide).
	slides.forEach( ( slide, i ) => {
		slide.hidden = i !== index;
	} );
	root.classList.add( 'is-ready' );

	prev?.addEventListener( 'click', () => step( -1 ) );
	next?.addEventListener( 'click', () => step( 1 ) );
	dots.forEach( ( dot, i ) =>
		dot.addEventListener( 'click', () => go( i ) )
	);

	// Arrow keys while focus is anywhere inside the carousel.
	root.addEventListener( 'keydown', ( event ) => {
		if ( event.altKey || event.ctrlKey || event.metaKey ) {
			return;
		}
		if ( 'ArrowLeft' === event.key ) {
			event.preventDefault();
			step( -1 );
		} else if ( 'ArrowRight' === event.key ) {
			event.preventDefault();
			step( 1 );
		}
	} );

	// Horizontal swipe on touch screens.
	let startX = null;
	let startY = 0;
	stage.addEventListener(
		'touchstart',
		( event ) => {
			if ( 1 !== event.touches.length ) {
				startX = null;
				return;
			}
			startX = event.touches[ 0 ].clientX;
			startY = event.touches[ 0 ].clientY;
		},
		{ passive: true }
	);
	stage.addEventListener(
		'touchend',
		( event ) => {
			if ( null === startX ) {
				return;
			}
			const touch = event.changedTouches[ 0 ];
			const dx = touch.clientX - startX;
			const dy = touch.clientY - startY;
			startX = null;
			if (
				Math.abs( dx ) > 40 &&
				Math.abs( dx ) > Math.abs( dy ) * 1.2
			) {
				step( dx < 0 ? 1 : -1 );
			}
		},
		{ passive: true }
	);

	// Optional autoplay: paused while hovered, focused or the tab is hidden, and stopped for good
	// with the stop / start button (WCAG 2.2.2).
	const delay = parseInt( root.dataset.autoplay, 10 ) || 0;
	if ( delay > 0 && ! reducedMotion() ) {
		const toggle = root.querySelector( '[data-testimonial-autoplay]' );
		let timer = null;
		let hovered = false;
		let focused = false;
		let stopped = false;

		const stop = () => {
			window.clearInterval( timer );
			timer = null;
			stage.setAttribute( 'aria-live', 'polite' );
		};
		const start = () => {
			if ( timer || stopped || hovered || focused || document.hidden ) {
				return;
			}
			// Rotating content must not be announced on every change.
			stage.setAttribute( 'aria-live', 'off' );
			timer = window.setInterval( () => step( 1 ), delay );
		};

		if ( toggle ) {
			toggle.hidden = false;
			toggle.addEventListener( 'click', () => {
				stopped = ! stopped;
				toggle.setAttribute(
					'aria-label',
					stopped
						? toggle.dataset.labelPlay
						: toggle.dataset.labelPause
				);
				toggle
					.querySelector( '[data-icon-pause]' )
					.toggleAttribute( 'hidden', stopped );
				toggle
					.querySelector( '[data-icon-play]' )
					.toggleAttribute( 'hidden', ! stopped );
				// Focus is inside the carousel, so a restart waits for focusout / mouseleave.
				if ( stopped ) {
					stop();
				}
			} );
		}

		root.addEventListener( 'mouseenter', () => {
			hovered = true;
			stop();
		} );
		root.addEventListener( 'mouseleave', () => {
			hovered = false;
			start();
		} );
		root.addEventListener( 'focusin', () => {
			focused = true;
			stop();
		} );
		root.addEventListener( 'focusout', ( event ) => {
			if ( ! root.contains( event.relatedTarget ) ) {
				focused = false;
				start();
			}
		} );
		document.addEventListener( 'visibilitychange', () =>
			document.hidden ? stop() : start()
		);
		start();
	}
}

export default function init() {
	document.querySelectorAll( '[data-testimonials]' ).forEach( setup );
}
