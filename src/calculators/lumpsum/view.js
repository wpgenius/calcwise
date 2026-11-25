/**
 * Lump-sum calculator. Fields, results and labels are in definition.php; the
 * formula is the library's lumpsum() with yearly compounding; everything else
 * is the shared runtime.
 */
window.calcwise.register( 'lumpsum', ( element, settings, runtime ) =>
	runtime.mount( element, settings, {
		calculate: ( values, calc ) =>
			calc.lumpsum( { ...values, compoundingPerYear: 1 } ),
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
