/**
 * SWP calculator. Fields, results, labels and the "money runs out" message are
 * in definition.php; the formula is the library's swp(); everything else is
 * the shared runtime.
 */

/**
 * The warning shown when the balance reaches zero before the end.
 *
 * @param {number|null} depletedMonth Month (1-based) the money ran out, or null.
 * @param {string}      template      Text with {year} and {month}.
 * @return {string} The message, or '' when the money lasts.
 */
export function depletedMessage( depletedMonth, template ) {
	if ( ! depletedMonth || ! template ) {
		return '';
	}
	const year = Math.ceil( depletedMonth / 12 );
	const month = ( ( depletedMonth - 1 ) % 12 ) + 1;
	return template
		.replace( '{year}', String( year ) )
		.replace( '{month}', String( month ) );
}

window.calcwise.register( 'swp', ( element, settings, runtime ) =>
	runtime.mount( element, settings, {
		calculate: ( values, calc ) => calc.swp( values ),
		results: ( result ) => ( {
			investment: result.investment,
			totalWithdrawn: result.totalWithdrawn,
			finalBalance: result.finalBalance,
		} ),
		message: ( result ) =>
			depletedMessage(
				result.depletedMonth,
				settings?.messages?.depleted || ''
			),
		chart: ( result ) => ( {
			type: 'line',
			points: result.series,
			series: [ 'balance', 'withdrawn' ],
			fill: false,
		} ),
	} )
);
