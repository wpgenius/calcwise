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
