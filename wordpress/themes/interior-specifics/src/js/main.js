/**
 * Front-end entry. Every file in ./modules exports a default `init()` that finds its own
 * elements (by data-* hooks) and does nothing when they are absent — so modules can be added
 * without touching this file.
 */
const modules = require.context( './modules', false, /\.js$/ );

function boot() {
	modules.keys().forEach( ( key ) => {
		try {
			modules( key ).default?.();
		} catch ( error ) {
			// One broken module must never take the rest of the page down.
			// eslint-disable-next-line no-console
			console.error( `[interior-specifics] ${ key }`, error );
		}
	} );
	// Tells the head failsafe that reveal styles are safe to keep (see inc/assets.php).
	window.__istReady = true;
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
