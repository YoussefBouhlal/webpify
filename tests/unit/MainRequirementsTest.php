<?php
/**
 * Tests for plugin requirement evaluation.
 *
 * @package Webpify
 */

use PHPUnit\Framework\TestCase;
use Webpify\Main;

final class MainRequirementsTest extends TestCase {

	public function test_supported_versions_have_no_errors(): void {
		$requirements = array(
			'php_version' => '7.4',
			'wp_version'  => '6.8',
		);

		$this->assertSame( array(), Main::get_requirement_errors( '7.4.33', '6.8.3', $requirements ) );
	}

	public function test_reports_php_and_wordpress_failures(): void {
		$requirements = array(
			'php_version' => '7.4',
			'wp_version'  => '6.8',
		);

		$this->assertSame( array( 1, 2 ), Main::get_requirement_errors( '7.3.33', '6.7.2', $requirements ) );
	}

	public function test_reports_missing_or_old_woocommerce(): void {
		$requirements = array(
			'php_version' => '7.4',
			'wp_version'  => '6.8',
			'wc_version'  => '9.0',
		);

		$this->assertSame( array( 3 ), Main::get_requirement_errors( '8.2', '6.8', $requirements ) );
		$this->assertSame( array( 3 ), Main::get_requirement_errors( '8.2', '6.8', $requirements, '8.9' ) );
		$this->assertSame( array(), Main::get_requirement_errors( '8.2', '6.8', $requirements, '9.0' ) );
	}
}
