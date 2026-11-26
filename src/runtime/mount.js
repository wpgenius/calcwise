/**
 * Shared behaviour of every calculator: inputs, results, chart, table and the
 * screen-reader summary. A calculator script only says how to calculate and
 * what to show:
 *
 *     window.calcwise.register( 'sip', ( element, settings, runtime ) =>
 *         runtime.mount( element, settings, {
 *             calculate: ( values, calc ) => calc.sip( values ),
 *             results: ( result ) => ( { invested: result.invested, total: result.total } ),
 *             chart: ( result ) => ( {
 *                 type: 'line',
 *                 points: result.series,
 *                 series: [ 'invested', 'total' ],
 *                 // fill: false draws plain lines instead of stacked areas.
 *             } ),
 *             // Optional: a note under the results, e.g. a warning.
 *             message: ( result, format ) => '',
 *             // Optional: table rows when they differ from the chart (e.g. a
 *             // repayment schedule): { points, series }. Defaults to the chart.
 *             table: ( result ) => ( { points: [], series: [] } ),
 *         } )
 *     );
 *
 * Everything is scoped to `element`, so instances never touch each other.
 */
import { isUsable, limitsFor, resolveValue } from './fields';

const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

/**
 * Number formatting for one instance.
 *
 * @param {Object} calc     The financial-calculators library.
 * @param {Object} settings Instance settings.
 * @return {Object} { money, number, compact, words, enabledWords }
 */
export function createFormat( calc, settings ) {
	const format = settings?.format || {};
	const i18n = settings?.i18n || {};
	// Translated words for amountInWords(), when the site isn't in English.
	const words =
		i18n.words && typeof i18n.words === 'object' ? i18n.words : undefined;
	const units =
		i18n.units && typeof i18n.units === 'object' ? i18n.units : {};
	const system =
		format.system === 'international' ? 'international' : 'indian';
	const currency = {
		symbol: typeof format.symbol === 'string' ? format.symbol : '₹',
		position: format.position === 'after' ? 'after' : 'before',
		system,
	};
	return {
		money: ( value ) =>
			calc.formatCurrency( Math.round( value ), currency ),
		number: ( value ) =>
			calc.formatNumber( value, { system, decimals: 0 } ),
		// The library ends short numbers with an English unit ("2.5L").
		compact: ( value ) =>
			calc
				.formatCompact( value, { system } )
				.replace( /[A-Za-z]+$/, ( unit ) =>
					typeof units[ unit ] === 'string' ? units[ unit ] : unit
				),
		words: ( value ) => {
			const amount = Math.round( Math.max( 0, value ) );
			try {
				return calc.amountInWords( amount, { system, words } );
			} catch {
				// Badly shaped translations: fall back to English.
				return calc.amountInWords( amount, { system } );
			}
		},
		enabledWords: format.words !== false,
	};
}

/**
 * Read a CSS custom property from the calculator (for chart colours).
 *
 * @param {HTMLElement} element  Calculator element.
 * @param {string}      name     Property name, e.g. '--calcwise-accent'.
 * @param {string}      fallback Used when the property is empty.
 * @return {string} Colour.
 */
function cssVar( element, name, fallback ) {
	const value = window
		.getComputedStyle( element )
		.getPropertyValue( name )
		.trim();
	return value || fallback;
}

/**
 * Label of a series point on the x axis: the calendar year it falls in.
 *
 * @param {Object} point     Series point with `month`.
 * @param {number} startYear This year.
 * @return {string} Label.
 */
export function yearLabel( point, startYear ) {
	return String( startYear + Math.ceil( ( point.month || 0 ) / 12 ) );
}

/**
 * Start one calculator instance.
 *
 * @param {HTMLElement} element  The `.calcwise-calculator` element.
 * @param {Object}      settings Settings from its data attribute.
 * @param {Object}      runtime  window.calcwise (Chart, calc).
 * @param {Object}      spec     How to calculate and what to show.
 * @return {Object} { update, values, destroy } for tests and page builders.
 */
export function mount( element, settings, runtime, spec ) {
	const { calc, Chart } = runtime;
	const format = createFormat( calc, settings );
	const i18n = settings?.i18n || {};
	const startYear = new Date().getFullYear();

	const fields = Array.from(
		element.querySelectorAll( '.calcwise-field' )
	).map( ( wrapper ) => {
		const key = wrapper.dataset.field;
		const number = wrapper.querySelector( '.calcwise-field__number' );
		const range = wrapper.querySelector( '.calcwise-field__range' );
		return {
			key,
			wrapper,
			number,
			range,
			limits: limitsFor( settings, key, number ),
		};
	} );

	const message = element.querySelector( '.calcwise-message' );
	const status = element.querySelector( '[data-calcwise-status]' );
	const canvas = element.querySelector( '.calcwise-chart canvas' );
	const table = element.querySelector( '.calcwise-table table' );
	let chart = null;
	let frame = 0;
	let statusTimer = 0;

	// Money limits under the sliders, in the instance's number format.
	fields.forEach( ( field ) => {
		if ( ! field.wrapper.classList.contains( 'calcwise-field--money' ) ) {
			return;
		}
		field.wrapper.querySelectorAll( '[data-limit]' ).forEach( ( span ) => {
			span.textContent = format.money(
				field.limits[ span.dataset.limit ]
			);
		} );
	} );

	const values = () =>
		Object.fromEntries(
			fields.map( ( field ) => [
				field.key,
				resolveValue( field.number.value, field.limits ),
			] )
		);

	const showMessage = ( text ) => {
		if ( ! message ) {
			return;
		}
		message.textContent = text || '';
		message.hidden = ! text;
	};

	const renderResults = ( shown ) => {
		let summary = '';
		Object.entries( shown || {} ).forEach( ( [ key, value ] ) => {
			const row = element.querySelector(
				`.calcwise-result[data-result="${ key }"]`
			);
			if ( ! row ) {
				return;
			}
			const kind = row.dataset.format || 'money';
			let text = String( value );
			if ( kind === 'money' ) {
				text = format.money( value );
			} else if ( kind === 'number' ) {
				text = format.number( value );
			}
			row.querySelector( '.calcwise-result__value' ).textContent = text;
			const words = row.querySelector( '.calcwise-result__words' );
			if ( words ) {
				words.textContent =
					kind === 'money' && format.enabledWords
						? format.words( value )
						: '';
			}
			if ( row.hasAttribute( 'data-highlight' ) ) {
				const label = row.querySelector(
					'.calcwise-result__label'
				).textContent;
				summary += `${ label }: ${ text }. `;
			}
		} );
		if ( status ) {
			window.clearTimeout( statusTimer );
			statusTimer = window.setTimeout( () => {
				status.textContent = summary.trim();
			}, 600 );
		}
	};

	const datasetsFor = ( config ) => {
		const accent = cssVar( element, '--calcwise-accent', '#2271b1' );
		const second = cssVar( element, '--calcwise-accent-2', '#b3501d' );
		const colours = [ accent, second, '#8c8f94', '#00a32a' ];
		// Each area fills down to the series before it (the first to zero).
		const fillFor = ( index ) => ( index === 0 ? 'origin' : '-1' );
		// A see-through version of a hex colour for the filled area.
		const translucent = ( colour ) =>
			/^#[0-9a-f]{6}$/i.test( colour ) ? `${ colour }33` : colour;
		return ( config.series || [] ).map( ( key, index ) => ( {
			label: settings?.series?.[ key ] || key,
			data: ( config.points || [] ).map( ( point ) => point[ key ] ),
			borderColor: colours[ index % colours.length ],
			backgroundColor: translucent( colours[ index % colours.length ] ),
			fill:
				config.type === 'line' && config.fill !== false
					? fillFor( index )
					: false,
			tension: 0.3,
			pointRadius: 0,
		} ) );
	};

	const renderChart = ( config ) => {
		if ( ! canvas || ! Chart || ! config ) {
			return;
		}
		const animation = window.matchMedia?.( REDUCED_MOTION ).matches
			? false
			: { duration: 600 };
		let data;
		if ( config.type === 'doughnut' ) {
			const accent = cssVar( element, '--calcwise-accent', '#2271b1' );
			const second = cssVar( element, '--calcwise-accent-2', '#b3501d' );
			data = {
				labels: config.series.map(
					( key ) => settings?.series?.[ key ] || key
				),
				datasets: [
					{
						data: config.values,
						backgroundColor: [ accent, second, '#8c8f94' ],
						borderWidth: 0,
					},
				],
			};
		} else {
			data = {
				labels: ( config.points || [] ).map( ( point ) =>
					yearLabel( point, startYear )
				),
				datasets: datasetsFor( config ),
			};
		}

		if ( chart && chart.config.type === config.type ) {
			chart.data = data;
			chart.update();
			return;
		}
		chart?.destroy();

		const money = ( context ) => {
			const value = context.parsed?.y ?? context.parsed;
			return `${ context.dataset.label || context.label }: ${ format.money( value ) }`;
		};
		chart = new Chart( canvas, {
			type: config.type,
			data,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				animation,
				interaction: { mode: 'index', intersect: false },
				plugins: {
					legend: { position: 'bottom' },
					tooltip: { callbacks: { label: money } },
				},
				...( config.type === 'doughnut'
					? {
							circumference: config.circumference || 360,
							rotation: config.rotation || 0,
						}
					: {
							scales: {
								x: {
									title: {
										display: true,
										text: i18n.year || 'Year',
									},
								},
								y: {
									beginAtZero: true,
									ticks: {
										callback: ( value ) =>
											format.compact( value ),
									},
								},
							},
						} ),
			},
		} );
	};

	const columnLabel = ( key ) =>
		settings?.table?.[ key ] || settings?.series?.[ key ] || key;

	const renderTable = ( config ) => {
		if ( ! table || ! config || config.type === 'doughnut' ) {
			return;
		}
		const keys = config.series || [];
		const head = document.createElement( 'tr' );
		[ i18n.year || 'Year', ...keys.map( columnLabel ) ].forEach(
			( text ) => {
				const th = document.createElement( 'th' );
				th.scope = 'col';
				th.textContent = text;
				head.appendChild( th );
			}
		);
		table.tHead.replaceChildren( head );
		table.tBodies[ 0 ].replaceChildren(
			...( config.points || [] ).map( ( point ) => {
				const row = document.createElement( 'tr' );
				const th = document.createElement( 'th' );
				th.scope = 'row';
				th.textContent = yearLabel( point, startYear );
				row.appendChild( th );
				keys.forEach( ( key ) => {
					const td = document.createElement( 'td' );
					td.textContent = format.money( point[ key ] );
					row.appendChild( td );
				} );
				return row;
			} )
		);
	};

	const update = () => {
		frame = 0;
		let result;
		try {
			result = spec.calculate( values(), calc );
		} catch {
			showMessage( i18n.error || 'Please check the values entered.' );
			return;
		}
		showMessage( spec.message ? spec.message( result, format ) : '' );
		renderResults( spec.results ? spec.results( result, format ) : {} );
		const config = spec.chart ? spec.chart( result ) : null;
		renderChart( config );
		renderTable( spec.table ? spec.table( result ) : config );
	};

	const schedule = () => {
		if ( ! frame ) {
			frame = window.requestAnimationFrame( update );
		}
	};

	fields.forEach( ( field ) => {
		const { number, range, limits } = field;
		if ( ! number ) {
			return;
		}
		number.addEventListener( 'input', () => {
			if ( isUsable( number.value, limits ) ) {
				if ( range ) {
					range.value = number.value;
				}
				schedule();
			}
		} );
		number.addEventListener( 'change', () => {
			const value = resolveValue( number.value, limits );
			number.value = String( value );
			if ( range ) {
				range.value = String( value );
			}
			schedule();
		} );
		range?.addEventListener( 'input', () => {
			number.value = range.value;
			schedule();
		} );
	} );

	update();

	return {
		update,
		values,
		destroy: () => {
			window.cancelAnimationFrame( frame );
			window.clearTimeout( statusTimer );
			chart?.destroy();
		},
	};
}
