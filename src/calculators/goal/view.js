/**
 * Time-based goal calculator. Fields, results, labels and messages are in
 * definition.php; the formula is the library's goal(); everything else is the
 * shared runtime.
 */
window.calcwise.register( 'goal', ( element, settings, runtime ) =>
	runtime.mount( element, settings, {
		calculate: ( values, calc ) => calc.goal( values ),
		results: ( result ) => ( {
			targetYear:
				new Date().getFullYear() + Math.ceil( result.months / 12 ),
			futureCost: result.futureCost,
			savingsFutureValue: result.savingsFutureValue,
			monthlyInvestment: result.monthlyInvestment,
		} ),
		message: ( result ) =>
			result.shortfall <= 0 ? settings?.messages?.covered || '' : '',
		chart: ( result ) => ( {
			type: 'line',
			points: result.series,
			series: [ 'goalCost', 'total' ],
			fill: false,
		} ),
	} )
);
