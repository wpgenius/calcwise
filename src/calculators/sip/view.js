/**
 * SIP calculator. Fields, results and labels are in definition.php; the
 * formula is the library's sip(); everything else is the shared runtime.
 */
window.calcwise.register( 'sip', ( element, settings, runtime ) =>
	runtime.mount( element, settings, {
		calculate: ( values, calc ) => calc.sip( values ),
		results: ( result ) => ( {
			invested: result.invested,
			returns: result.returns,
			total: result.total,
		} ),
		chart: ( result ) => ( {
			type: 'line',
			points: result.series,
			series: [ 'invested', 'total' ],
		} ),
	} )
);
