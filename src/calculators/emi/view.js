/**
 * EMI calculator, written from scratch. Fields, results and labels are in
 * definition.php; the formula is the library's emi(); everything else is the
 * shared runtime. The chart is a half doughnut of principal and interest; the
 * table is the yearly repayment schedule.
 */
import './style.css';

/**
 * Schedule rows for the table, one per loan year, placed in the calendar year
 * they start in.
 *
 * @param {Array<Object>} schedule The library's yearly schedule.
 * @return {Array<Object>} Rows with `month` for the year label.
 */
export function scheduleRows( schedule ) {
	return ( schedule || [] ).map( ( row ) => ( {
		...row,
		month: ( row.year - 1 ) * 12,
	} ) );
}

window.calcwise.register( 'emi', ( element, settings, runtime ) =>
	runtime.mount( element, settings, {
		calculate: ( values, calc ) => calc.emi( values ),
		results: ( result ) => ( {
			emi: result.emi,
			principal: result.principal,
			totalInterest: result.totalInterest,
			totalPayment: result.totalPayment,
		} ),
		chart: ( result ) => ( {
			type: 'doughnut',
			series: [ 'principal', 'totalInterest' ],
			values: [ result.principal, result.totalInterest ],
			circumference: 180,
			rotation: -90,
		} ),
		table: ( result ) => ( {
			points: scheduleRows( result.schedule ),
			series: [ 'principalPaid', 'interestPaid', 'balance' ],
		} ),
	} )
);
