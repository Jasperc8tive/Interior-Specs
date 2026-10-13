/**
 * @wordpress/scripts defaults (auto-discovers src/blocks/<name>/block.json) plus the
 * front-end bundle. Output: build/blocks/<name>/… and build/js/main.js.
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...( typeof defaultConfig.entry === 'function' ? defaultConfig.entry() : defaultConfig.entry ),
		'js/main': path.resolve( __dirname, 'src/js/main.js' ),
	},
};
