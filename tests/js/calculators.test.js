import { beforeEach, describe, expect, it, vi } from 'vitest';
import * as calc from '../../src/vendor/financial-calculators/financial-calculators.esm.js';

/**
 * Load a calculator's view.js and return the spec it passes to mount().
 *
 * @param {string} type Calculator type (folder name).
 * @return {Promise<Object>} The spec.
 */
async function specFor( type ) {
	let spec = null;
	window.calcwise = {
		register: ( registered, start ) => {
			expect( registered ).toBe( type );
			start(
				document.createElement( 'div' ),
				{},
				{
					mount: ( element, settings, given ) => {
						spec = given;
					},
				}
			);
		},
	};
	vi.resetModules();
	await import( `../../src/calculators/${ type }/view.js` );
	return spec;
}

beforeEach( () => {
	delete window.calcwise;
} );

describe( 'SIP', () => {
	it( 'matches the reference values', async () => {
		const spec = await specFor( 'sip' );
		const result = spec.calculate(
			{ monthlyAmount: 25000, annualRate: 12, years: 10 },
			calc
		);
		const shown = spec.results( result );

		expect( Math.round( shown.total ) ).toBe( 5808477 );
		expect( shown.invested ).toBe( 3000000 );
		expect( Math.round( shown.returns ) ).toBe( 2808477 );
	} );

	it( 'charts invested and total value for every year', async () => {
		const spec = await specFor( 'sip' );
		const chart = spec.chart(
			calc.sip( { monthlyAmount: 1000, annualRate: 1, years: 5 } )
		);

		expect( chart.type ).toBe( 'line' );
		expect( chart.series ).toEqual( [ 'invested', 'total' ] );
		expect( chart.points ).toHaveLength( 6 );
		expect( chart.points[ 5 ].total ).toBeGreaterThan(
			chart.points[ 5 ].invested
		);
	} );
} );

describe( 'Lump sum', () => {
	it( 'matches the reference values (yearly compounding)', async () => {
		const spec = await specFor( 'lumpsum' );
		const shown = spec.results(
			spec.calculate( { amount: 25000, annualRate: 12, years: 10 }, calc )
		);

		expect( shown.invested ).toBe( 25000 );
		expect( Math.round( shown.total ) ).toBe( 77646 );
		expect( Math.round( shown.returns ) ).toBe( 52646 );
	} );

	it( 'charts every year, with distinct year labels for half years', async () => {
		const spec = await specFor( 'lumpsum' );
		const chart = spec.chart(
			spec.calculate( { amount: 1000, annualRate: 1, years: 10.5 }, calc )
		);
		const { yearLabel } = await import( '../../src/runtime/mount' );
		const labels = chart.points.map( ( point ) =>
			yearLabel( point, 2026 )
		);

		expect( chart.series ).toEqual( [ 'invested', 'total' ] );
		expect( new Set( labels ).size ).toBe( labels.length );
		expect( labels.at( -1 ) ).toBe( '2037' );
	} );
} );
