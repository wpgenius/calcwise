<?php
/**
 * SIP calculator: what a fixed monthly investment grows to.
 *
 * Field keys are the inputs of the library's sip() function.
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

return array(
	'order'       => 10,
	'title'       => __( 'SIP calculator', 'calcwise' ),
	'description' => __( 'What a fixed monthly investment (SIP) grows to.', 'calcwise' ),
	'fields'      => array(
		'monthlyAmount' => array(
			'label'   => __( 'Monthly investment', 'calcwise' ),
			'unit'    => 'money',
			'default' => 25000,
			'min'     => 500,
			'max'     => 1000000,
			'step'    => 500,
		),
		'annualRate'    => array(
			'label'   => __( 'Expected return rate (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 12,
			'min'     => 1,
			'max'     => 30,
			'step'    => 0.1,
		),
		'years'         => array(
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
