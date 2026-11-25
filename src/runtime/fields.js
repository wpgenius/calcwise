/**
 * Input helpers with no DOM: reading, clamping and snapping field values.
 */

/**
 * Field limits as sent by the server: { min, max, step, default }.
 *
 * @typedef {Object} FieldLimits
 * @property {number} min     Lowest allowed value.
 * @property {number} max     Highest allowed value.
 * @property {number} step    Step of the slider and the number box.
 * @property {number} default Value used when the box is empty or invalid.
 */

/**
 * Parse what the visitor typed. Accepts "25,000", " 12.5 " and numbers.
 *
 * @param {string|number} raw Typed value.
 * @return {number|null} The number, or null when empty or not a number.
 */
export function parseNumber( raw ) {
	if ( typeof raw === 'number' ) {
		return Number.isFinite( raw ) ? raw : null;
	}
	const text = String( raw ?? '' )
		.replace( /[,\s]/g, '' )
		.trim();
	if ( text === '' ) {
		return null;
	}
	const number = Number( text );
	return Number.isFinite( number ) ? number : null;
}

/**
 * Keep a value between a field's min and max.
 *
 * @param {number}      value  Value.
 * @param {FieldLimits} limits Field limits.
 * @return {number} Clamped value.
 */
export function clamp( value, limits ) {
	return Math.min( Math.max( value, limits.min ), limits.max );
}

/**
 * The value to use for a typed entry: the default when empty or invalid,
 * otherwise clamped to the limits.
 *
 * @param {string|number} raw    Typed value.
 * @param {FieldLimits}   limits Field limits.
 * @return {number} Usable value.
 */
export function resolveValue( raw, limits ) {
	const number = parseNumber( raw );
	return number === null ? limits.default : clamp( number, limits );
}

/**
 * Whether a typed entry can be used as it is (a number within the limits).
 * While the visitor is still typing, out-of-range entries are left alone and
 * only fixed when they leave the box.
 *
 * @param {string|number} raw    Typed value.
 * @param {FieldLimits}   limits Field limits.
 * @return {boolean} True when usable now.
 */
export function isUsable( raw, limits ) {
	const number = parseNumber( raw );
	return number !== null && number >= limits.min && number <= limits.max;
}

/**
 * Read one field's limits from the settings, falling back to the input's own
 * attributes when the settings miss it.
 *
 * @param {Object}           settings Instance settings.
 * @param {string}           key      Field key.
 * @param {HTMLInputElement} input    The number box.
 * @return {FieldLimits} Limits.
 */
export function limitsFor( settings, key, input ) {
	const fromSettings = settings?.fields?.[ key ] || {};
	const read = ( name, fallback ) => {
		const value = parseNumber(
			fromSettings[ name ] ?? input?.getAttribute( name )
		);
		return value === null ? fallback : value;
	};
	const min = read( 'min', 0 );
	const max = Math.max( read( 'max', min + 1 ), min );
	const step = read( 'step', 1 ) > 0 ? read( 'step', 1 ) : 1;
	const fallback = parseNumber( input?.dataset?.default );
	const value = parseNumber( fromSettings.default );
	return {
		min,
		max,
		step,
		default: clamp( value ?? fallback ?? min, { min, max } ),
	};
}
