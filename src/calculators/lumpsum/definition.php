<?php
/**
 * Lump-sum calculator: what a one-time investment grows to.
 *
 * Field keys are the inputs of the library's lumpsum() function. Returns
 * compound once a year.
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

return array(
	'title'       => __( 'Lump-sum calculator', 'calcwise' ),
	'description' => __( 'What a one-time investment grows to.', 'calcwise' ),
	'fields'      => array(
		'amount'     => array(
			'label'   => __( 'Investment amount', 'calcwise' ),
			'unit'    => 'money',
			'default' => 100000,
			'min'     => 1000,
			'max'     => 50000000,
			'step'    => 1000,
		),
		'annualRate' => array(
			'label'   => __( 'Expected return rate (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 12,
			'min'     => 1,
			'max'     => 30,
			'step'    => 0.1,
		),
		'years'      => array(
			'label'   => __( 'Time period', 'calcwise' ),
			'unit'    => 'years',
			'default' => 10,
			'min'     => 1,
			'max'     => 40,
			'step'    => 1,
		),
	),
	'results'     => array(
		'invested' => array(
			'label' => __( 'Amount invested', 'calcwise' ),
			'words' => true,
		),
		'returns'  => array(
			'label' => __( 'Estimated returns', 'calcwise' ),
			'words' => true,
		),
		'total'    => array(
			'label'     => __( 'Total value', 'calcwise' ),
			'words'     => true,
			'highlight' => true,
		),
	),
	'chart'       => array(
		'label'  => __( 'Amount invested and total value by year', 'calcwise' ),
		'series' => array(
			'invested' => __( 'Amount invested', 'calcwise' ),
			'total'    => __( 'Total value', 'calcwise' ),
		),
	),
);
