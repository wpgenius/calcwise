/**
 * Webpack config: the `@wordpress/scripts` defaults plus CalcWise's own entries.
 *
 * - blocks/<name>/block.json    → build/<name>/          (blocks, found by wp-scripts)
 * - src/runtime/index.js        → build/runtime/index.js (shared runtime: Chart.js, registry)
 * - src/calculators/<type>/view.js → build/calculators/<type>/view.js (+ view.css)
 *
 * Each entry gets an index.asset.php / view.asset.php with its dependencies
 * and a content hash, which src/Assets.php reads to register the handles.
 */
const { existsSync, readdirSync } = require( 'fs' );
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * One entry per calculator folder that has a view.js.
 *
 * @return {Object<string, string>} Entry name → source file.
 */
function calculatorEntries() {
	const dir = path.resolve( __dirname, 'src/calculators' );
	if ( ! existsSync( dir ) ) {
		return {};
	}
	return Object.fromEntries(
		readdirSync( dir, { withFileTypes: true } )
			.filter( ( entry ) => entry.isDirectory() )
			.filter( ( entry ) =>
				existsSync( path.join( dir, entry.name, 'view.js' ) )
			)
			.map( ( entry ) => [
				`calculators/${ entry.name }/view`,
				`./src/calculators/${ entry.name }/view.js`,
			] )
	);
}

module.exports = {
	...defaultConfig,
	entry: () => {
		const blocks =
			typeof defaultConfig.entry === 'function'
				? defaultConfig.entry()
				: defaultConfig.entry || {};
		return {
			...blocks,
			'runtime/index': './src/runtime/index.js',
			...calculatorEntries(),
		};
	},
};
