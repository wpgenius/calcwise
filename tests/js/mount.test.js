import { beforeEach, describe, expect, it, vi } from 'vitest';
import * as calc from '../../src/vendor/financial-calculators/financial-calculators.esm.js';
import { createFormat, mount, yearLabel } from '../../src/runtime/mount';

/**
 * Markup like Renderer::render() produces for a two-field calculator.
 *
 * @param {string} id Instance ID.
 * @return {HTMLElement} Calculator element.
 */
function markup( id ) {
	const settings = {
		fields: {
			monthlyAmount: {
				min: 500,
				max: 1000000,
				step: 500,
				default: 25000,
			},
			annualRate: { min: 1, max: 30, step: 0.5, default: 12 },
			years: { min: 1, max: 40, step: 1, default: 10 },
		},
		format: {
			system: 'indian',
			symbol: '₹',
			position: 'before',
			words: true,
		},
		series: { invested: 'Amount invested', total: 'Total value' },
		i18n: { year: 'Year', error: 'Please check the values entered.' },
	};
	const field = ( key, value, min, max, step, money ) => `
		<div class="calcwise-field${ money ? ' calcwise-field--money' : '' }" data-field="${ key }">
			<input class="calcwise-field__number" type="number" min="${ min }" max="${ max }" step="${ step }" value="${ value }" data-default="${ value }">
			<input class="calcwise-field__range" type="range" min="${ min }" max="${ max }" step="${ step }" value="${ value }">
			<div class="calcwise-field__limits"><span data-limit="min"></span><span data-limit="max"></span></div>
		</div>`;
	const element = document.createElement( 'div' );
	element.className = 'calcwise-calculator';
	element.id = id;
	element.dataset.calcwiseSettings = JSON.stringify( settings );
	element.innerHTML = `
		${ field( 'monthlyAmount', 25000, 500, 1000000, 500, true ) }
		${ field( 'annualRate', 12, 1, 30, 0.5, false ) }
		${ field( 'years', 10, 1, 40, 1, false ) }
		<dl class="calcwise-results">
			<div class="calcwise-result" data-result="invested" data-format="money"><dt class="calcwise-result__label">Invested</dt><dd class="calcwise-result__value"></dd></div>
			<div class="calcwise-result" data-result="total" data-format="money" data-highlight><dt class="calcwise-result__label">Total value</dt><dd class="calcwise-result__value"></dd><dd class="calcwise-result__words"></dd></div>
		</dl>
		<p class="calcwise-message" hidden></p>
		<figure class="calcwise-chart"><canvas></canvas><details class="calcwise-table"><table><thead></thead><tbody></tbody></table></details></figure>
		<p data-calcwise-status></p>`;
	document.body.appendChild( element );
	return element;
}

const charts = [];
class FakeChart {
	constructor( canvas, config ) {
		this.canvas = canvas;
		this.config = config;
		this.data = config.data;
		this.updates = 0;
		charts.push( this );
	}
	update() {
		this.updates++;
	}
	destroy() {}
}

const spec = {
	calculate: ( values, lib ) => lib.sip( values ),
	results: ( result ) => ( {
		invested: result.invested,
		total: result.total,
	} ),
	chart: ( result ) => ( {
		type: 'line',
		points: result.series,
		series: [ 'invested', 'total' ],
	} ),
};

const start = ( element ) =>
	mount(
		element,
		JSON.parse( element.dataset.calcwiseSettings ),
		{ calc, Chart: FakeChart },
		spec
	);

const text = ( element, key ) =>
	element.querySelector( `[data-result="${ key }"] .calcwise-result__value` )
		.textContent;

beforeEach( () => {
	document.body.innerHTML = '';
	charts.length = 0;
	vi.useFakeTimers();
} );

describe( 'mount', () => {
	it( 'calculates on load, with words, chart and table', () => {
		const element = markup( 'a' );
		start( element );

		expect( text( element, 'total' ) ).toBe( '₹58,08,477' );
		expect( text( element, 'invested' ) ).toBe( '₹30,00,000' );
		expect(
			element.querySelector( '.calcwise-result__words' ).textContent
		).toBe(
			'Fifty Eight Lakh Eight Thousand Four Hundred and Seventy Seven'
		);
		expect( charts ).toHaveLength( 1 );
		expect( charts[ 0 ].data.datasets.map( ( d ) => d.label ) ).toEqual( [
			'Amount invested',
			'Total value',
		] );
		expect( element.querySelectorAll( 'tbody tr' ) ).toHaveLength( 11 );
		expect(
			element.querySelector( '[data-limit="max"]' ).textContent
		).toBe( '₹10,00,000' );
	} );

	it( 'announces the highlighted result after a pause', () => {
		const element = markup( 'a' );
		start( element );
		vi.advanceTimersByTime( 700 );

		expect(
			element.querySelector( '[data-calcwise-status]' ).textContent
		).toBe( 'Total value: ₹58,08,477.' );
	} );

	it( 'keeps the number box and the slider in sync', () => {
		const element = markup( 'a' );
		start( element );
		const number = element.querySelector(
			'[data-field="years"] .calcwise-field__number'
		);
		const range = element.querySelector(
			'[data-field="years"] .calcwise-field__range'
		);

		range.value = '20';
		range.dispatchEvent( new Event( 'input' ) );
		expect( number.value ).toBe( '20' );

		number.value = '15';
		number.dispatchEvent( new Event( 'input' ) );
		expect( range.value ).toBe( '15' );
	} );

	it( 'clamps on change and falls back to the default when emptied', () => {
		const element = markup( 'a' );
		const instance = start( element );
		const number = element.querySelector(
			'[data-field="annualRate"] .calcwise-field__number'
		);

		number.value = '99';
		number.dispatchEvent( new Event( 'change' ) );
		expect( number.value ).toBe( '30' );

		number.value = '';
		number.dispatchEvent( new Event( 'change' ) );
		expect( number.value ).toBe( '12' );
		expect( instance.values().annualRate ).toBe( 12 );
	} );

	it( 'uses a rate of exactly 1%', () => {
		const element = markup( 'a' );
		const instance = start( element );
		const number = element.querySelector(
			'[data-field="annualRate"] .calcwise-field__number'
		);

		number.value = '1';
		number.dispatchEvent( new Event( 'change' ) );
		instance.update();

		expect( text( element, 'total' ) ).not.toBe( '₹58,08,477' );
		expect( instance.values().annualRate ).toBe( 1 );
	} );

	it( 'keeps two calculators on one page independent', () => {
		const first = markup( 'a' );
		const second = markup( 'b' );
		start( first );
		const other = start( second );

		const years = second.querySelector(
			'[data-field="years"] .calcwise-field__number'
		);
		years.value = '20';
		years.dispatchEvent( new Event( 'change' ) );
		other.update();

		expect( text( first, 'total' ) ).toBe( '₹58,08,477' );
		expect( text( second, 'total' ) ).not.toBe( '₹58,08,477' );
		expect( charts ).toHaveLength( 2 );
	} );

	it( 'shows a message instead of failing when the calculation throws', () => {
		const element = markup( 'a' );
		mount(
			element,
			{},
			{ calc, Chart: FakeChart },
			{
				calculate: () => {
					throw new RangeError( 'bad' );
				},
			}
		);

		const message = element.querySelector( '.calcwise-message' );
		expect( message.hidden ).toBe( false );
		expect( message.textContent ).toBe(
			'Please check the values entered.'
		);
	} );
} );

describe( 'formatting', () => {
	it( 'follows the instance format', () => {
		const international = createFormat( calc, {
			format: { system: 'international', symbol: '$' },
		} );
		expect( international.money( 1234567 ) ).toBe( '$1,234,567' );
		expect( international.compact( 1500000 ) ).toBe( '1.5M' );

		const after = createFormat( calc, {
			format: { symbol: '€', position: 'after' },
		} );
		expect( after.money( 1500 ) ).toBe( '1,500 €' );
	} );

	it( 'rounds chart axis labels up to the next unit (library 1.1.1)', () => {
		expect( createFormat( calc, {} ).compact( 999999 ) ).toBe( '10L' );
		expect( createFormat( calc, {} ).compact( 9999999 ) ).toBe( '1Cr' );
		expect(
			createFormat( calc, {
				format: { system: 'international' },
			} ).compact( 999999 )
		).toBe( '1M' );
	} );

	it( 'uses translated number words and short units', () => {
		const format = createFormat( calc, {
			i18n: {
				words: {
					ones: [ '', 'Ek', ...Array( 18 ).fill( 'x' ) ],
					indian: { lakh: 'Laakh' },
				},
				units: { L: ' lakh', Cr: ' cr' },
			},
		} );
		expect( format.words( 100000 ) ).toBe( 'Ek Laakh' );
		expect( format.compact( 250000 ) ).toBe( '2.5 lakh' );
		expect( format.compact( 12000000 ) ).toBe( '1.2 cr' );
		expect( format.compact( 5000 ) ).toBe( '5K' );
	} );

	it( 'falls back to English words when the translations are malformed', () => {
		const format = createFormat( calc, {
			i18n: { words: { ones: [ 'too', 'short' ] } },
		} );
		expect( format.words( 100000 ) ).toBe( 'One Lakh' );
	} );

	it( 'labels points with the calendar year they fall in', () => {
		expect( yearLabel( { month: 0 }, 2026 ) ).toBe( '2026' );
		expect( yearLabel( { month: 12 }, 2026 ) ).toBe( '2027' );
		expect( yearLabel( { month: 126 }, 2026 ) ).toBe( '2037' );
	} );
} );

describe( 'separate table', () => {
	it( 'uses spec.table and its column labels when given', () => {
		const element = markup( 'a' );
		const settings = JSON.parse( element.dataset.calcwiseSettings );
		settings.table = { balance: 'Balance left' };
		mount(
			element,
			settings,
			{ calc, Chart: FakeChart },
			{
				...spec,
				table: () => ( {
					points: [
						{ month: 0, balance: 900 },
						{ month: 12, balance: 0 },
					],
					series: [ 'balance' ],
				} ),
			}
		);

		const headers = [ ...element.querySelectorAll( 'thead th' ) ].map(
			( th ) => th.textContent
		);
		expect( headers ).toEqual( [ 'Year', 'Balance left' ] );
		expect( element.querySelectorAll( 'tbody tr' ) ).toHaveLength( 2 );
	} );

	it( 'passes half-doughnut options to the chart', () => {
		const element = markup( 'a' );
		mount(
			element,
			JSON.parse( element.dataset.calcwiseSettings ),
			{ calc, Chart: FakeChart },
			{
				...spec,
				chart: () => ( {
					type: 'doughnut',
					series: [ 'invested', 'total' ],
					values: [ 1, 2 ],
					circumference: 180,
					rotation: -90,
				} ),
			}
		);

		const chart = charts.at( -1 );
		expect( chart.config.type ).toBe( 'doughnut' );
		expect( chart.config.options.circumference ).toBe( 180 );
		expect( chart.config.options.rotation ).toBe( -90 );
		expect( chart.data.datasets[ 0 ].data ).toEqual( [ 1, 2 ] );
	} );
} );
