/**
 * Homepage reading-progress bar (components/ScrollProgress.tsx): a gold hairline whose width
 * follows page scroll, eased toward its target so it feels springy rather than mechanical.
 */
export default function init() {
	const bar = document.querySelector( '[data-scroll-progress]' );
	if ( ! bar ) {
		return;
	}
	const reduced = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;
	let current = 0;
	let frame = 0;

	const target = () => {
		const max = document.documentElement.scrollHeight - window.innerHeight;
		return max > 0 ? Math.min( 1, Math.max( 0, window.scrollY / max ) ) : 0;
	};

	const step = () => {
		const goal = target();
		current = reduced ? goal : current + ( goal - current ) * 0.18;
		bar.style.transform = `scaleX(${ current.toFixed( 4 ) })`;
		frame =
			Math.abs( goal - current ) > 0.0005
				? window.requestAnimationFrame( step )
				: 0;
	};

	const request = () => {
		if ( ! frame ) {
			frame = window.requestAnimationFrame( step );
		}
	};
	window.addEventListener( 'scroll', request, { passive: true } );
	window.addEventListener( 'resize', request, { passive: true } );
	request();
}
