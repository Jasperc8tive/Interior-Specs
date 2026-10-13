/**
 * Showroom filter (port of components/ShowroomFilter.tsx), rendered by archive-is_project.php.
 *
 *   [data-showroom-filter data-base-url="…/showroom/"]
 *     form > button[data-filter="all|<type slug>"][aria-pressed]   (no JS: submits ?type=<slug>)
 *     [data-showroom-grid] > [data-project-type="<slug>"]          cards
 *     [data-showroom-empty]                                         "nothing here yet" note
 *     [data-showroom-status]                                        polite live region
 *
 * The server already applied ?type= on first paint; this re-applies it (cached pages), filters
 * client-side on click and keeps ?type=<slug> in the URL with history.replaceState. Matching is
 * case-insensitive so old links like ?type=Residential keep working.
 */
export default function init() {
	document.querySelectorAll( '[data-showroom-filter]' ).forEach( ( root ) => {
		const buttons = [ ...root.querySelectorAll( '[data-filter]' ) ];
		if ( ! buttons.length ) {
			return;
		}
		const cards = [
			...root.querySelectorAll(
				'[data-showroom-grid] [data-project-type]'
			),
		];
		const empty = root.querySelector( '[data-showroom-empty]' );
		const status = root.querySelector( '[data-showroom-status]' );
		const slugs = buttons.map( ( button ) => button.dataset.filter );

		const apply = ( slug, { announce = false, updateUrl = true } = {} ) => {
			const active = slugs.includes( slug ) ? slug : 'all';
			let shown = 0;

			buttons.forEach( ( button ) => {
				const on = button.dataset.filter === active;
				button.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				if ( on && announce && status ) {
					status.textContent = button.dataset.status || '';
				}
			} );
			cards.forEach( ( card ) => {
				const match =
					active === 'all' || card.dataset.projectType === active;
				card.hidden = ! match;
				shown += match ? 1 : 0;
			} );
			if ( empty ) {
				empty.hidden = shown > 0;
			}

			if ( updateUrl && window.history?.replaceState ) {
				const url = new URL(
					root.dataset.baseUrl || window.location.href,
					window.location.href
				);
				const params = new URLSearchParams( window.location.search );
				if ( active === 'all' ) {
					params.delete( 'type' );
				} else {
					params.set( 'type', active );
				}
				const query = params.toString();
				url.search = query ? `?${ query }` : '';
				url.hash = window.location.hash;
				if ( url.href !== window.location.href ) {
					window.history.replaceState(
						window.history.state,
						'',
						url.href
					);
				}
			}
		};

		buttons.forEach( ( button ) => {
			button.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				apply( button.dataset.filter, { announce: true } );
			} );
		} );

		// First paint: ?type= wins, else the server's choice (a /showroom/type/<slug>/ term page).
		const param = (
			new URLSearchParams( window.location.search ).get( 'type' ) || ''
		)
			.trim()
			.toLowerCase();
		const initial = param || root.dataset.initial || 'all';
		// Normalise ?type=Residential → ?type=residential; leave term-archive URLs untouched.
		apply( initial, { updateUrl: Boolean( param ) } );
	} );
}
