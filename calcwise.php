<?php
/**
 * Plugin Name:       CalcWise – Financial Calculators
 * Plugin URI:        https://github.com/wpgenius/calcwise
 * Description:       SIP, lump sum, SWP, goal and EMI calculators with live results and charts. Add them with a block or a shortcode.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            Team WPGenius
 * Author URI:        https://wpgenius.in
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       calcwise
 * Domain Path:       /languages
 *
 * @package WPGenius\CalcWise
 */

defined( 'ABSPATH' ) || exit;

// The release workflow keeps CALCWISE_VERSION in sync with the Version header.
define( 'CALCWISE_VERSION', '1.0.0' );
define( 'CALCWISE_FILE', __FILE__ );
define( 'CALCWISE_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CALCWISE_DIR_URL', plugin_dir_url( __FILE__ ) );

// Loads WPGenius\CalcWise\* classes from src/. No Composer needed at runtime.
require_once CALCWISE_DIR_PATH . 'src/autoload.php';

register_activation_hook( __FILE__, array( 'WPGenius\CalcWise\Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPGenius\CalcWise\Installer', 'deactivate' ) );
// Cleanup when the plugin is deleted lives in uninstall.php.

add_action( 'plugins_loaded', array( 'WPGenius\CalcWise\Plugin', 'boot' ) );
