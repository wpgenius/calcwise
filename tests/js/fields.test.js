import { describe, expect, it } from 'vitest';
import {
	clamp,
	isUsable,
	limitsFor,
	parseNumber,
	resolveValue,
} from '../../src/runtime/fields';

const limits = { min: 500, max: 100000, step: 500, default: 25000 };

describe( 'parseNumber', () => {
	it( 'reads plain, grouped and padded numbers', () => {
		expect( parseNumber( '25000' ) ).toBe( 25000 );
		expect( parseNumber( '25,00,000' ) ).toBe( 2500000 );
		expect( parseNumber( ' 12.5 ' ) ).toBe( 12.5 );
		expect( parseNumber( 7 ) ).toBe( 7 );
	} );

	it( 'returns null for empty or non-numbers', () => {
		expect( parseNumber( '' ) ).toBeNull();
		expect( parseNumber( 'abc' ) ).toBeNull();
		expect( parseNumber( undefined ) ).toBeNull();
		expect( parseNumber( Infinity ) ).toBeNull();
	} );
} );

describe( 'resolveValue', () => {
	it( 'clamps to the limits', () => {
		expect( resolveValue( '50', limits ) ).toBe( 500 );
		expect( resolveValue( '999999', limits ) ).toBe( 100000 );
		expect( clamp( 3000, limits ) ).toBe( 3000 );
	} );

	it( 'uses the default when empty or invalid', () => {
		expect( resolveValue( '', limits ) ).toBe( 25000 );
		expect( resolveValue( 'x', limits ) ).toBe( 25000 );
	} );

	it( 'accepts exactly the minimum (the old 1% bug)', () => {
		expect(
			resolveValue( '1', { min: 1, max: 30, step: 0.5, default: 12 } )
		).toBe( 1 );
		expect(
			isUsable( '1', { min: 1, max: 30, step: 0.5, default: 12 } )
		).toBe( true );
	} );
} );

describe( 'isUsable', () => {
	it( 'is false while the entry is out of range or empty', () => {
		expect( isUsable( '5', limits ) ).toBe( false );
		expect( isUsable( '', limits ) ).toBe( false );
		expect( isUsable( '5000', limits ) ).toBe( true );
	} );
} );

describe( 'limitsFor', () => {
	it( 'prefers the settings and falls back to the input attributes', () => {
		const input = document.createElement( 'input' );
		input.setAttribute( 'min', '1' );
		input.setAttribute( 'max', '40' );
		input.setAttribute( 'step', '1' );
		input.dataset.default = '10';

		expect(
			limitsFor( { fields: { years: { max: 30 } } }, 'years', input )
		).toEqual( {
			min: 1,
			max: 30,
			step: 1,
			default: 10,
		} );
	} );

	it( 'clamps a default outside the limits', () => {
		expect(
			limitsFor(
				{ fields: { x: { min: 0, max: 10, default: 50 } } },
				'x',
				null
			).default
		).toBe( 10 );
	} );
} );
