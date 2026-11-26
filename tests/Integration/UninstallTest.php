<?php
/**
 * What uninstall.php keeps and deletes.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests\Integration;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Installer;

/**
 * Uninstall.
 */
class UninstallTest extends \WP_UnitTestCase {

	use RunsUninstall;

	/**
	 * Activate, so the default settings exist.
	 */
	public function set_up() {
		parent::set_up();
		Installer::activate();
	}

	public function test_settings_are_kept_unless_the_user_asked_to_delete_them() {
		$this->uninstall();

		$this->assertIsArray( get_option( Api::OPTION ) );
	}

	public function test_settings_are_deleted_when_the_user_asked() {
		update_option( Api::OPTION, array_merge( Api::get_settings(), array( 'delete_data' => 1 ) ) );

		$this->uninstall();

		$this->assertFalse( get_option( Api::OPTION ) );
	}
}
