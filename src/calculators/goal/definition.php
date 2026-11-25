<?php
/**
 * Time-based goal calculator: what a goal will cost after inflation, and the
 * monthly investment needed on top of the savings already put aside.
 *
 * Field keys are the inputs of the library's goal() function.
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Goal calculator', 'calcwise' ),
	'description' => __( 'The monthly investment needed to reach a goal by a chosen year.', 'calcwise' ),
	'fields'      => array(
		'presentCost'    => array(
			'label'   => __( 'Cost of the goal today', 'calcwise' ),
			'unit'    => 'money',
			'default' => 1000000,
			'min'     => 10000,
			'max'     => 100000000,
			'step'    => 10000,
		),
		'currentSavings' => array(
			'label'   => __( 'Savings already put aside', 'calcwise' ),
			'unit'    => 'money',
			'default' => 100000,
			'min'     => 0,
			'max'     => 100000000,
			'step'    => 10000,
		),
		'inflationRate'  => array(
			'label'   => __( 'Inflation (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 6,
			'min'     => 0,
			'max'     => 15,
			'step'    => 0.1,
		),
		'annualRate'     => array(
			'label'   => __( 'Expected return rate (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 10,
			'min'     => 1,
			'max'     => 30,
			'step'    => 0.1,
		),
		'years'          => array(
			'label'   => __( 'Years to reach the goal', 'calcwise' ),
			'unit'    => 'years',
			'default' => 5,
			'min'     => 1,
			'max'     => 40,
			'step'    => 1,
		),
	),
	'results'     => array(
		'targetYear'         => array(
			'label'  => __( 'Goal year', 'calcwise' ),
			'format' => 'text',
		),
		'futureCost'         => array(
			'label' => __( 'Cost of the goal in that year', 'calcwise' ),
			'words' => true,
		),
		'savingsFutureValue' => array(
			'label' => __( 'Your savings will grow to', 'calcwise' ),
		),
		'monthlyInvestment'  => array(
			'label'     => __( 'Invest every month', 'calcwise' ),
			'words'     => true,
			'highlight' => true,
		),
	),
	'chart'       => array(
		'label'  => __( 'Cost of the goal and what you will have, by year', 'calcwise' ),
		'series' => array(
			'goalCost' => __( 'Cost of the goal', 'calcwise' ),
			'total'    => __( 'Savings and investments', 'calcwise' ),
		),
	),
	'messages'    => array(
		'covered' => __( 'Your savings already cover this goal. No monthly investment is needed.', 'calcwise' ),
	),
);
