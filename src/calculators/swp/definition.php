<?php
/**
 * SWP calculator: a fixed monthly withdrawal from an investment that keeps
 * growing. The balance never goes below zero; when the money runs out, the
 * calculator says in which month.
 *
 * Field keys are the inputs of the library's swp() function.
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

return array(
	'order'       => 30,
	'title'       => __( 'SWP calculator', 'calcwise' ),
	'description' => __( 'What is left after fixed monthly withdrawals (systematic withdrawal plan).', 'calcwise' ),
	'fields'      => array(
		'investment'        => array(
			'label'   => __( 'Total investment', 'calcwise' ),
			'unit'    => 'money',
			'default' => 1000000,
			'min'     => 10000,
			'max'     => 50000000,
			'step'    => 10000,
		),
		'monthlyWithdrawal' => array(
			'label'   => __( 'Monthly withdrawal', 'calcwise' ),
			'unit'    => 'money',
			'default' => 10000,
			'min'     => 500,
			'max'     => 500000,
			'step'    => 500,
		),
		'annualRate'        => array(
			'label'   => __( 'Expected return rate (per year)', 'calcwise' ),
			'unit'    => 'percent',
			'default' => 10,
			'min'     => 1,
			'max'     => 30,
			'step'    => 0.1,
		),
		'years'             => array(
			'label'   => __( 'Time period', 'calcwise' ),
			'unit'    => 'years',
			'default' => 10,
			'min'     => 1,
			'max'     => 40,
			'step'    => 1,
		),
	),
	'results'     => array(
		'investment'     => array(
			'label' => __( 'Total investment', 'calcwise' ),
			'words' => true,
		),
		'totalWithdrawn' => array(
			'label' => __( 'Total withdrawn', 'calcwise' ),
			'words' => true,
		),
		'finalBalance'   => array(
			'label'     => __( 'Final value', 'calcwise' ),
			'words'     => true,
			'highlight' => true,
		),
	),
	'chart'       => array(
		'label'  => __( 'Remaining balance and total withdrawn by year', 'calcwise' ),
		'series' => array(
			'balance'   => __( 'Remaining balance', 'calcwise' ),
			'withdrawn' => __( 'Total withdrawn', 'calcwise' ),
		),
	),
	'messages'    => array(
		/* translators: {year} and {month} are numbers, e.g. "year 8, month 3". Keep the braces. */
		'depleted' => __( 'The money runs out in year {year}, month {month}. Lower the monthly withdrawal to make it last longer.', 'calcwise' ),
	),
);
