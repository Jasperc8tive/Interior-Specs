/**
 * Google Analytics 4 + conversion events — port of components/Analytics.tsx.
 *
 * - Loads gtag.js only when Settings → Interior Specifics → GA4 ID is set (istConfig.ga4).
 * - window.istTrack( name, params ) is always defined. Without GA it is a no-op (it still
 *   dispatches an `ist:track` DOM event for other integrations). With GA, events go through
 *   gtag(), whose dataLayer queue holds them until gtag.js has loaded.
 * - Contact clicks: [data-track="phone|email|whatsapp"] links, plus any tel:, mailto: and
 *   wa.me link, fire click_phone / click_email / click_whatsapp.
 * - Calendly: a scheduled booking (postMessage from https://calendly.com) fires
 *   calendly_event_scheduled.
 *
 * The forms module calls window.istTrack?.( 'generate_lead', { form_type } ).
 */

const GA_ID = /^G-[A-Z0-9]{4,20}$/;

/**
 * Event name for a clicked link, or '' when it is not a tracked contact link.
 *
 * @param {HTMLAnchorElement} link Link element.
 * @return {string} Event name.
 */
function contactEvent( link ) {
	const kind = ( link.dataset.track || '' )
		.toLowerCase()
		.replace( /[^a-z0-9_]/g, '' );
	if ( kind ) {
		return `click_${ kind }`;
	}
	const href = ( link.getAttribute( 'href' ) || '' ).trim().toLowerCase();
	if ( href.startsWith( 'tel:' ) ) {
		return 'click_phone';
	}
	if ( href.startsWith( 'mailto:' ) ) {
		return 'click_email';
	}
	if (
		/^https?:\/\/(?:api\.)?(?:wa\.me|whatsapp\.com)\b/.test( href ) ||
		href.includes( 'wa.me/' )
	) {
		return 'click_whatsapp';
	}
	return '';
}

/**
 * Where on the page a link sits, for reporting (header / footer / section id / main).
 *
 * @param {Element} link Link element.
 * @return {string} Location label.
 */
function linkLocation( link ) {
	if ( link.closest( 'header' ) ) {
		return 'header';
	}
	if ( link.closest( 'footer' ) ) {
		return 'footer';
	}
	if ( link.classList.contains( 'fixed' ) ) {
		return 'floating_button';
	}
	const section = link.closest( 'section[id], [id]' );
	return section ? section.id : 'main';
}

function loadGtag( id ) {
	window.dataLayer = window.dataLayer || [];
	if ( typeof window.gtag !== 'function' ) {
		window.gtag = function gtag() {
			// gtag.js requires the Arguments object itself, not an array.
			// eslint-disable-next-line prefer-rest-params
			window.dataLayer.push( arguments );
		};
	}
	window.gtag( 'js', new Date() );
	window.gtag( 'config', id, { anonymize_ip: true } );

	if ( ! document.querySelector( 'script[data-ist-gtag]' ) ) {
		const script = document.createElement( 'script' );
		script.async = true;
		script.src = `https://www.googletagmanager.com/gtag/js?id=${ encodeURIComponent(
			id
		) }`;
		script.dataset.istGtag = '';
		document.head.appendChild( script );
	}
}

export default function init() {
	const config = window.istConfig || {};
	const id = String( config.ga4 || '' )
		.trim()
		.toUpperCase();
	const enabled = GA_ID.test( id );

	if ( enabled ) {
		loadGtag( id );
	}

	window.istTrack = function istTrack( name, params = {} ) {
		if ( ! name || typeof name !== 'string' ) {
			return;
		}
		try {
			document.dispatchEvent(
				new CustomEvent( 'ist:track', { detail: { name, params } } )
			);
		} catch ( e ) {
			// CustomEvent unsupported: nothing else listens anyway.
		}
		if ( enabled && typeof window.gtag === 'function' ) {
			window.gtag( 'event', name, params );
		}
	};

	// Contact-link clicks (delegated; also covers keyboard activation, which fires click).
	document.addEventListener(
		'click',
		( event ) => {
			const link =
				event.target instanceof Element
					? event.target.closest( 'a[href]' )
					: null;
			if ( ! link ) {
				return;
			}
			const name = contactEvent( link );
			if ( ! name ) {
				return;
			}
			window.istTrack( name, {
				link_url: link.href,
				link_text: (
					link.getAttribute( 'aria-label' ) ||
					link.textContent ||
					''
				)
					.replace( /\s+/g, ' ' )
					.trim()
					.slice( 0, 100 ),
				link_location: linkLocation( link ),
			} );
		},
		{ passive: true, capture: true }
	);

	// Calendly inline/popup widget: track a completed booking once per event URI.
	const seen = new Set();
	window.addEventListener(
		'message',
		( event ) => {
			if (
				event.origin !== 'https://calendly.com' ||
				! event.data ||
				typeof event.data !== 'object'
			) {
				return;
			}
			if ( event.data.event !== 'calendly.event_scheduled' ) {
				return;
			}
			const uri = event.data.payload?.event?.uri || '';
			if ( uri && seen.has( uri ) ) {
				return;
			}
			seen.add( uri );
			window.istTrack( 'calendly_event_scheduled', {
				method: 'calendly',
			} );
		},
		{ passive: true }
	);
}
