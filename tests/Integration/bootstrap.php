<?php
/**
 * Integration test bootstrap: starts the WordPress test suite and loads the plugin in it.
 *
 * Runs inside the wp-env container, which sets WP_TESTS_DIR.
 *
 * @package WPGenius\CalcWise
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

$calcwise_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $calcwise_tests_dir || ! file_exists( $calcwise_tests_dir . '/includes/functions.php' ) ) {
	echo "The WordPress test suite was not found. Run these tests with: npm run test:integration\n";
	exit( 1 );
}

require_once $calcwise_tests_dir . '/includes/functions.php';

// Load the plugin the way WordPress does, before the test suite starts.
tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/calcwise.php';
	}
);

require $calcwise_tests_dir . '/includes/bootstrap.php';
