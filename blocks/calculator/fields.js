/**
 * Helpers for the block's per-field settings, kept free of React so they can
 * be tested on their own.
 */

/**
 * Store one field setting, dropping it when emptied so the calculator's own
 * value applies again.
 *
 * @param {Object} fields Current `fields` attribute: { key: { prop: number } }.
 * @param {string} key    Field key.
 * @param {string} prop   'default', 'min', 'max' or 'step'.
 * @param {string} raw    What was typed.
 * @return {Object} New `fields` attribute.
 */
export function setFieldValue( fields, key, prop, raw ) {
	const next = { ...( fields || {} ) };
	const field = { ...( next[ key ] || {} ) };
	const number = raw === '' || raw === null ? NaN : Number( raw );

	if ( Number.isFinite( number ) ) {
		field[ prop ] = number;
	} else {
		delete field[ prop ];
	}

	if ( Object.keys( field ).length ) {
		next[ key ] = field;
	} else {
		delete next[ key ];
	}
	return next;
}

/**
 * Keep only the settings that belong to a calculator's fields (after the
 * calculator type changes).
 *
 * @param {Object} fields     Current `fields` attribute.
 * @param {Object} definition Calculator definition with `fields`.
 * @return {Object} Filtered `fields` attribute.
 */
export function fieldsFor( fields, definition ) {
	const keys = Object.keys( definition?.fields || {} );
	return Object.fromEntries(
		Object.entries( fields || {} ).filter( ( [ key ] ) =>
			keys.includes( key )
		)
	);
}
