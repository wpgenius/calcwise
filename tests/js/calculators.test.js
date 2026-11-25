import { beforeEach, describe, expect, it, vi } from 'vitest';
import * as calc from '../../src/vendor/financial-calculators/financial-calculators.esm.js';

/**
 * Load a calculator's view.js and return the spec it passes to mount().
 *
 * @param {string} type       Calculator type (folder name).
 * @param {Object} [settings] Instance settings passed to the calculator.
 * @return {Promise<Object>} The spec.
 */
async function specFor( type, settings = {} ) {
	let spec = null;
	window.calcwise = {
		register: ( registered, start ) => {
			expect( registered ).toBe( type );
			start( document.createElement( 'div' ), settings, {
				mount: ( element, instanceSettings, given ) => {
					spec = given;
				},
			} );
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

describe( 'SWP', () => {
	const settings = {
		messages: { depleted: 'Runs out in year {year}, month {month}.' },
	};

	it( 'matches the original calculator when the money lasts', async () => {
		const spec = await specFor( 'swp', settings );
		const result = spec.calculate(
			{
				investment: 1000000,
				monthlyWithdrawal: 10000,
				annualRate: 10,
				years: 3,
			},
			calc
		);
		const shown = spec.results( result );

		expect( shown.investment ).toBe( 1000000 );
		expect( shown.totalWithdrawn ).toBe( 360000 );
		expect( shown.finalBalance ).toBeGreaterThan( 0 );
		expect( spec.message( result ) ).toBe( '' );
	} );

	it( 'stops at zero and says when the money runs out', async () => {
		const spec = await specFor( 'swp', settings );
		const result = spec.calculate(
			{
				investment: 100000,
				monthlyWithdrawal: 10000,
				annualRate: 1,
				years: 5,
			},
			calc
		);

		expect( result.finalBalance ).toBe( 0 );
		expect(
			Math.min( ...result.series.map( ( p ) => p.balance ) )
		).toBeGreaterThanOrEqual( 0 );
		expect( spec.message( result ) ).toMatch(
			/^Runs out in year 1, month \d+\.$/
		);
	} );

	it( 'draws plain lines for balance and withdrawals', async () => {
		const spec = await specFor( 'swp', settings );
		const chart = spec.chart(
			calc.swp( {
				investment: 1000000,
				monthlyWithdrawal: 10000,
				annualRate: 10,
				years: 10,
			} )
		);

		expect( chart.series ).toEqual( [ 'balance', 'withdrawn' ] );
		expect( chart.fill ).toBe( false );
	} );
} );

describe( 'depletedMessage', () => {
	it( 'turns a month number into year and month', async () => {
		await specFor( 'swp' );
		const { depletedMessage } =
			await import( '../../src/calculators/swp/view.js' );

		expect( depletedMessage( 1, 'y{year} m{month}' ) ).toBe( 'y1 m1' );
		expect( depletedMessage( 12, 'y{year} m{month}' ) ).toBe( 'y1 m12' );
		expect( depletedMessage( 13, 'y{year} m{month}' ) ).toBe( 'y2 m1' );
		expect( depletedMessage( null, 'x' ) ).toBe( '' );
	} );
} );

describe( 'Goal', () => {
	const settings = { messages: { covered: 'Covered.' } };

	it( 'matches the original calculator', async () => {
		const spec = await specFor( 'goal', settings );
		const result = spec.calculate(
			{
				presentCost: 1000000,
				currentSavings: 0,
				inflationRate: 6,
				annualRate: 9,
				years: 3,
			},
			calc
		);
		const shown = spec.results( result );

		// Ara: ₹10,00,000 at 6% inflation for 3 years costs ₹11,91,016.
		expect( Math.round( shown.futureCost ) ).toBe( 1191016 );
		expect( shown.monthlyInvestment ).toBeGreaterThan( 0 );
		expect( shown.targetYear ).toBe( new Date().getFullYear() + 3 );
		expect( spec.message( result ) ).toBe( '' );
	} );

	it( 'accepts no savings at all', async () => {
		const spec = await specFor( 'goal', settings );
		const result = spec.calculate(
			{
				presentCost: 500000,
				currentSavings: 0,
				inflationRate: 0,
				annualRate: 10,
				years: 5,
			},
			calc
		);

		expect( result.savingsFutureValue ).toBe( 0 );
		expect( result.monthlyInvestment ).toBeGreaterThan( 0 );
	} );

	it( 'never asks for a negative amount when savings cover the goal', async () => {
		const spec = await specFor( 'goal', settings );
		const result = spec.calculate(
			{
				presentCost: 100000,
				currentSavings: 500000,
				inflationRate: 6,
				annualRate: 10,
				years: 5,
			},
			calc
		);

		expect( spec.results( result ).monthlyInvestment ).toBe( 0 );
		expect( spec.message( result ) ).toBe( 'Covered.' );
	} );

	it( 'charts the goal cost against what you will have', async () => {
		const spec = await specFor( 'goal', settings );
		const chart = spec.chart(
			calc.goal( {
				presentCost: 1000000,
				currentSavings: 100000,
				inflationRate: 6,
				annualRate: 10,
				years: 5,
			} )
		);

		expect( chart.series ).toEqual( [ 'goalCost', 'total' ] );
		expect( chart.fill ).toBe( false );
		const last = chart.points.at( -1 );
		expect( Math.round( last.total ) ).toBe( Math.round( last.goalCost ) );
	} );
} );
