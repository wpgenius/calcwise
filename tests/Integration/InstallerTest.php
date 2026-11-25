<?php
/**
 * Activation and deactivation in a real WordPress.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Installer;

/**
 * Installer.
 */
class InstallerTest extends \WP_UnitTestCase {

	public function test_activation_saves_default_settings() {
		delete_option( Api::OPTION );

		Installer::activate();

		$this->assertSame( 6, Api::get_setting( 'items_per_page' ) );
	}


	public function test_activation_keeps_existing_settings() {
		update_option( Api::OPTION, array( 'items_per_page' => 12 ) );

		Installer::activate();

		$this->assertSame( 12, Api::get_setting( 'items_per_page' ) );
	}
}
