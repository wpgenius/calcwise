<?php
/**
 * Runs uninstall.php from integration tests.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

/**
 * The file declares functions, so it can be loaded only once per test run:
 * the first call loads it (which uninstalls), later calls call its function.
 */
trait RunsUninstall {

	/**
	 * Run the uninstall routine as WordPress would.
	 */
	private function uninstall() {
		if ( function_exists( 'calcwise_uninstall' ) ) {
			calcwise_uninstall();
			return;
		}
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'calcwise/calcwise.php' );
		}
		require dirname( __DIR__, 2 ) . '/uninstall.php';
	}
}
