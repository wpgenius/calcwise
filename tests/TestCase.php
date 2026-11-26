<?php
/**
 * Base test case: sets up and tears down Brain Monkey for each test.
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test case with WordPress functions mockable.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Start Brain Monkey and stub the translation and escaping functions.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\stubTranslationFunctions();
		Monkey\Functions\stubEscapeFunctions();
		Monkey\Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
			}
		);
		\WPGenius\CalcWise\Calculator\Registry::reset();
	}

	/**
	 * Stop Brain Monkey.
	 */
	protected function tearDown(): void {
		\WPGenius\CalcWise\Calculator\Registry::reset();
		Monkey\tearDown();
		parent::tearDown();
	}
}
