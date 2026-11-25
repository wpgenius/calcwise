/**
 * CalcWise shared runtime.
 *
 * Loaded once per page (handle `calcwise-runtime`) before any calculator
 * script. It bundles Chart.js (so CalcWise never clashes with another plugin's
 * copy) and the financial-calculators library (formulas, number formatting,
 * amounts in words: src/vendor/financial-calculators), and keeps a registry of
 * calculator types.
 *
 * A calculator script (handle `calcwise-<type>`) registers itself:
 *
 *     window.calcwise.register( 'sip', ( element, settings, runtime ) => {
 *         const result = runtime.calc.sip( { monthlyAmount: 25000, annualRate: 12, years: 10 } );
 *         runtime.calc.formatCurrency( result.total ); // "₹58,08,477"
 *     } );
 *
 * Every element `.calcwise-calculator[data-calcwise-type="sip"]` on the page is
 * then started once, with the settings from its own `data-calcwise-settings`
 * attribute (JSON). Nothing is shared between instances, so several
 * calculators work on one page.
 *
 * Content added later (popups, tabs, page builders) is started with
 * `window.calcwise.init( container )`.
 *
 * `window.calcwise` is the only global CalcWise defines.
 */
import {
	ArcElement,
	CategoryScale,
	Chart,
	DoughnutController,
	Filler,
	Legend,
	LinearScale,
	LineController,
	LineElement,
	PointElement,
	Tooltip,
} from 'chart.js';
import * as calc from '../vendor/financial-calculators/financial-calculators.esm.js';

Chart.register(
	ArcElement,
	CategoryScale,
	DoughnutController,
	Filler,
	Legend,
	LinearScale,
	LineController,
	LineElement,
	PointElement,
	Tooltip
);

const SELECTOR = '.calcwise-calculator[data-calcwise-type]';
const types = new Map();

/**
 * Read an instance's settings from its data attribute.
 *
 * @param {HTMLElement} element Calculator element.
 * @return {Object} Settings, or an empty object if missing or invalid.
 */
function readSettings( element ) {
	try {
		const settings = JSON.parse( element.dataset.calcwiseSettings || '{}' );
		return settings && typeof settings === 'object' ? settings : {};
	} catch {
		return {};
	}
}

/**
 * Start every calculator in a container that isn't started yet.
 *
 * @param {Document|Element} [root=document] Where to look.
 * @return {void}
 */
function init( root = document ) {
	root.querySelectorAll( SELECTOR ).forEach( ( element ) => {
		if ( element.dataset.calcwiseReady ) {
			return;
		}
		const start = types.get( element.dataset.calcwiseType );
		if ( ! start ) {
			return; // Its script hasn't loaded yet; register() starts it.
		}
		element.dataset.calcwiseReady = '1';
		start( element, readSettings( element ), runtime );
	} );
}

/**
 * Register a calculator type and start its instances already on the page.
 *
 * @param {string}                                                            type  Calculator type, e.g. 'sip'.
 * @param {(element: HTMLElement, settings: Object, runtime: Object) => void} start Called once per instance.
 * @return {void}
 */
function register( type, start ) {
	types.set( type, start );
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => init() );
	} else {
		init();
	}
}

const runtime = Object.freeze( { Chart, calc, init, register } );

window.calcwise = runtime;
