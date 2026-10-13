/**
 * Lead forms — progressive enhancement of the server-rendered forms from inc/forms.php
 * (ports of ContactForm, BookingForm, LeadMagnet, NewsletterForm and the ClosingCTA card).
 *
 *   [data-lead-form data-form-type="contact"]      wrapper
 *     form[data-lead-form-el]                       real <form>; without JS it posts to admin-post.php
 *       [data-field="name"] … [data-field-error="name"]   per-field message slot (hidden)
 *       [data-lead-error]                           error box (hidden, role=alert) holding
 *         [data-lead-error-msg]                     a specific message (rate limit, expired page…)
 *         [data-lead-error-default]                 or the type's generic copy with fallbacks
 *         [data-lead-error-fallback]                the fallback links alone, after a rate-limit message
 *       button[type=submit][data-loading-label]
 *     [data-lead-success]                           success state (hidden until success)
 *     [data-lead-live]                              polite live region
 *
 * Flow (mirrors lib/forms.ts submitLead + each component's status machine):
 * idle → loading (button disabled, "Sending…") → success (form swapped for the success state and
 * reset) | error (422: inline field messages, first invalid field focused; 429: the server's
 * "too many submissions" message; otherwise the type's error copy with mailto / tel / WhatsApp
 * fallbacks). A page left open past the token's lifetime gets a 400 `token_expired` carrying a
 * fresh token: the form swaps it in and resubmits once, so the visitor never notices.
 */

// Fields that travel outside `data` (or not at all).
const RESERVED = [
	'action',
	'formType',
	'token',
	'anchor',
	'company',
	'_wp_http_referer',
];

const config = () => window.istConfig || {};

/**
 * Announce a message through the wrapper's polite live region.
 *
 * @param {HTMLElement} root    Wrapper.
 * @param {string}      message Text.
 */
function announce( root, message ) {
	const live = root.querySelector( '[data-lead-live]' );
	if ( ! live ) {
		return;
	}
	live.textContent = '';
	window.requestAnimationFrame( () => {
		live.textContent = message;
	} );
}

/**
 * Wire one form.
 *
 * @param {HTMLElement} root Element with [data-lead-form].
 */
function setup( root ) {
	const form = root.querySelector( 'form[data-lead-form-el]' );
	if ( ! form || form.dataset.leadReady ) {
		return;
	}
	form.dataset.leadReady = '1';

	const success = root.querySelector( '[data-lead-success]' );

	const type = root.dataset.formType || form.elements.formType?.value || '';
	const button = form.querySelector( 'button[type="submit"]' );
	const errorBox = form.querySelector( '[data-lead-error]' );
	const errorMsg = errorBox?.querySelector( '[data-lead-error-msg]' );
	const errorDefault = errorBox?.querySelector( '[data-lead-error-default]' );
	const errorFallback = errorBox?.querySelector(
		'[data-lead-error-fallback]'
	);
	const idleLabel = button ? button.textContent : '';
	const i18n = config().i18n || {};
	const loadingLabel =
		button?.dataset.loadingLabel ||
		( type === 'newsletter' ? i18n.subscribing : i18n.sending ) ||
		idleLabel;
	let busy = false;

	const setLoading = ( on ) => {
		busy = on;
		form.toggleAttribute( 'aria-busy', on );
		if ( button ) {
			button.disabled = on;
			button.textContent = on ? loadingLabel : idleLabel;
		}
		if ( on ) {
			announce( root, loadingLabel );
		}
	};

	const clearField = ( control ) => {
		const name = control?.name;
		if ( ! name || control.getAttribute( 'aria-invalid' ) !== 'true' ) {
			return;
		}
		const slot = form.querySelector(
			`[data-field-error="${ CSS.escape( name ) }"]`
		);
		control.removeAttribute( 'aria-invalid' );
		if ( slot ) {
			slot.hidden = true;
			slot.textContent = '';
			const ids = ( control.getAttribute( 'aria-describedby' ) || '' )
				.split( /\s+/ )
				.filter( ( id ) => id && id !== slot.id );
			if ( ids.length ) {
				control.setAttribute( 'aria-describedby', ids.join( ' ' ) );
			} else {
				control.removeAttribute( 'aria-describedby' );
			}
		}
	};

	const clearErrors = () => {
		if ( errorBox ) {
			errorBox.hidden = true;
		}
		if ( errorMsg ) {
			errorMsg.hidden = true;
			errorMsg.textContent = '';
		}
		if ( errorFallback ) {
			errorFallback.hidden = true;
		}
		if ( errorDefault ) {
			errorDefault.hidden = false;
		}
		form.querySelectorAll( '[aria-invalid="true"]' ).forEach( clearField );
	};

	/**
	 * Show server messages next to their fields. Returns false when none could be placed.
	 *
	 * @param {Object<string, string>} fields Field name → message.
	 */
	const showFieldErrors = ( fields ) => {
		let first = null;
		// Walk the form in DOM order so the first invalid field is the one focused.
		form.querySelectorAll( '[data-field-error]' ).forEach( ( slot ) => {
			const name = slot.dataset.fieldError;
			const message = fields[ name ];
			const control = form.elements[ name ];
			if (
				! message ||
				! control ||
				typeof control.setAttribute !== 'function'
			) {
				return;
			}
			slot.textContent = message;
			slot.hidden = false;
			control.setAttribute( 'aria-invalid', 'true' );
			const ids = ( control.getAttribute( 'aria-describedby' ) || '' )
				.split( /\s+/ )
				.filter( Boolean );
			if ( ! ids.includes( slot.id ) ) {
				ids.push( slot.id );
			}
			control.setAttribute( 'aria-describedby', ids.join( ' ' ) );
			first = first || control;
		} );
		if ( first ) {
			first.focus();
			return true;
		}
		return false;
	};

	/**
	 * Reveal the error box: the given message in its own slot, else the generic copy.
	 *
	 * @param {string}  [message]      Specific message from the server.
	 * @param {boolean} [withFallback] Follow the message with the fallback contact links.
	 */
	const showError = ( message = '', withFallback = false ) => {
		if ( ! errorBox ) {
			return;
		}
		if ( message && errorMsg ) {
			errorMsg.textContent = message;
			errorMsg.hidden = false;
			if ( errorDefault ) {
				errorDefault.hidden = true;
			}
			if ( errorFallback ) {
				errorFallback.hidden = ! withFallback;
			}
		}
		errorBox.hidden = false;
	};

	/**
	 * The success state as one sentence-separated string ("Thank you. We've received…").
	 */
	const successText = () => {
		const parts = [ ...success.children ]
			.map( ( el ) => el.textContent.replace( /\s+/g, ' ' ).trim() )
			.filter( Boolean );
		if ( ! parts.length ) {
			return success.textContent.replace( /\s+/g, ' ' ).trim();
		}
		return parts.reduce(
			( text, part ) =>
				text
					? `${ text }${
							/[.!?…]$/.test( text ) ? '' : '.'
					  } ${ part }`
					: part,
			''
		);
	};

	const showSuccess = () => {
		form.reset();
		form.hidden = true;
		if ( success ) {
			success.hidden = false;
			success.focus( { preventScroll: true } );
			// A short success box can land above the fold once the taller form collapses.
			window.requestAnimationFrame( () => {
				if ( success.getBoundingClientRect().top < 0 ) {
					success.scrollIntoView( { block: 'start' } );
				}
			} );
			announce( root, successText() );
		}
		window.istTrack?.( 'generate_lead', { form_type: type } );
	};

	// Typing into a flagged field clears its message.
	const onEdit = ( event ) => clearField( event.target );
	form.addEventListener( 'input', onEdit, { passive: true } );
	form.addEventListener( 'change', onEdit, { passive: true } );

	form.addEventListener( 'submit', async ( event ) => {
		const endpoint = config().leadEndpoint;
		if ( ! endpoint || ! window.fetch ) {
			return; // Fall back to the classic admin-post.php submission.
		}
		event.preventDefault();
		if ( busy ) {
			return;
		}
		clearErrors();
		setLoading( true );

		const formData = new FormData( form );
		const data = {};
		formData.forEach( ( value, key ) => {
			if ( ! RESERVED.includes( key ) && typeof value === 'string' ) {
				data[ key ] = value;
			}
		} );

		const send = async () => {
			try {
				const res = await window.fetch( endpoint, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						Accept: 'application/json',
					},
					credentials: 'same-origin',
					body: JSON.stringify( {
						formType: type,
						data,
						company: String( formData.get( 'company' ) || '' ),
						token: String( form.elements.token?.value || '' ),
					} ),
				} );
				return {
					response: res,
					body: await res.json().catch( () => null ),
				};
			} catch ( error ) {
				return { response: null, body: null };
			}
		};

		let { response, body } = await send();
		// Stale page: take the fresh token the server handed back and try once more.
		if (
			response?.status === 400 &&
			body?.code === 'token_expired' &&
			body.token &&
			form.elements.token
		) {
			form.elements.token.value = body.token;
			( { response, body } = await send() );
		}

		setLoading( false );

		if ( response?.ok && body?.ok ) {
			showSuccess();
			return;
		}
		if (
			response?.status === 422 &&
			body?.fields &&
			showFieldErrors( body.fields )
		) {
			announce( root, body.error || '' );
			return;
		}
		if (
			( response?.status === 429 || body?.code === 'token_expired' ) &&
			body?.error
		) {
			showError( body.error, response?.status === 429 );
			return;
		}
		showError();
	} );
}

export default function init() {
	document.querySelectorAll( '[data-lead-form]' ).forEach( setup );
}
