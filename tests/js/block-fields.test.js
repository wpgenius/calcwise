import { describe, expect, it } from 'vitest';
import { fieldsFor, setFieldValue } from '../../blocks/calculator/fields';

describe( 'setFieldValue', () => {
	it( 'stores numbers and drops emptied settings', () => {
		let fields = setFieldValue( {}, 'years', 'default', '15' );
		expect( fields ).toEqual( { years: { default: 15 } } );

		fields = setFieldValue( fields, 'years', 'max', '25' );
		expect( fields ).toEqual( { years: { default: 15, max: 25 } } );

		fields = setFieldValue( fields, 'years', 'default', '' );
		fields = setFieldValue( fields, 'years', 'max', 'abc' );
		expect( fields ).toEqual( {} );
	} );
} );

describe( 'fieldsFor', () => {
	it( 'keeps only the fields of the chosen calculator', () => {
		const fields = { years: { default: 15 }, principal: { default: 5 } };
		expect( fieldsFor( fields, { fields: { years: {} } } ) ).toEqual( {
			years: { default: 15 },
		} );
		expect( fieldsFor( fields, undefined ) ).toEqual( {} );
	} );
} );
