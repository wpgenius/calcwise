<?php
/**
 * Unit test bootstrap: loads the plugin's classes without WordPress.
 * WordPress functions are mocked per test with Brain Monkey.
 *
 * @package WPGenius\CalcWise
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'CALCWISE_VERSION', '0.0.0-test' );
define( 'CALCWISE_FILE', dirname( __DIR__ ) . '/calcwise.php' );
define( 'CALCWISE_DIR_PATH', dirname( __DIR__ ) . '/' );
define( 'CALCWISE_DIR_URL', 'https://example.test/wp-content/plugins/calcwise/' );

require_once dirname( __DIR__ ) . '/src/autoload.php';
