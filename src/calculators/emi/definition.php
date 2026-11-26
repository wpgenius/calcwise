<?php
/**
 * EMI calculator: the monthly instalment of a loan, with interest reducing
 * every month, and a yearly repayment schedule.
 *
 * Written from scratch. Field keys are the inputs of the library's emi()
 * function.
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

return array(
	'order'       => 50,
	'title'       => __( 'EMI calculator', 'calcwise' ),
	'description' => __( 'The monthly instalment (EMI) of a loan, and how much of it is interest.', 'calcwise' ),
	'fields'      => array(
		'principal'  => array(
			'label'   => __( 'Loan amount', 'calcwise' ),
			'unit'    => 'money',
			'default' => 1000000,
			'min'     => 10000,
			'max'     => 100000000,
			'step'    => 10000,
		),
		'annualRate' => array(
			'label'   => __( 'Interest rate (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 9,
			'min'     => 1,
			'max'     => 30,
			'step'    => 0.05,
		),
		'years'      => array(
			'label'   => __( 'Loan tenure', 'calcwise' ),
			'unit'    => 'years',
			'default' => 20,
			'min'     => 1,
			'max'     => 30,
			'step'    => 1,
		),
	),
	'results'     => array(
		'emi'           => array(
			'label'     => __( 'Monthly EMI', 'calcwise' ),
			'words'     => true,
			'highlight' => true,
		),
		'principal'     => array(
			'label' => __( 'Principal amount', 'calcwise' ),
		),
		'totalInterest' => array(
			'label' => __( 'Total interest', 'calcwise' ),
			'words' => true,
		),
		'totalPayment'  => array(
			'label' => __( 'Total amount payable', 'calcwise' ),
			'words' => true,
		),
	),
	'chart'       => array(
		'label'  => __( 'Principal and interest in the total amount payable', 'calcwise' ),
		'series' => array(
			'principal'     => __( 'Principal', 'calcwise' ),
			'totalInterest' => __( 'Interest', 'calcwise' ),
		),
	),
	'table'       => array(
		'label'   => __( 'Yearly repayment schedule', 'calcwise' ),
		'columns' => array(
			'principalPaid' => __( 'Principal paid', 'calcwise' ),
			'interestPaid'  => __( 'Interest paid', 'calcwise' ),
			'balance'       => __( 'Balance', 'calcwise' ),
		),
	),
);
