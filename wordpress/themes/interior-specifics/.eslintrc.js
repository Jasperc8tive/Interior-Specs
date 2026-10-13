/**
 * ESLint (legacy config) for the theme's block and front-end scripts. `root` stops ESLint from
 * picking up the Next.js app's flat config in the repository root; the lint:js script also sets
 * ESLINT_USE_FLAT_CONFIG=false for the same reason.
 */
module.exports = {
	root: true,
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	env: {
		browser: true,
	},
};
