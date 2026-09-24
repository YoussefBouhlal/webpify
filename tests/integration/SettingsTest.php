<?php
/**
 * Tests for WebPify settings.
 *
 * @package Webpify
 */

use Webpify\Admin\Settings;
use Webpify\Utils;

final class SettingsTest extends WP_UnitTestCase {

	public function tear_down(): void {
		unregister_setting( 'options', Settings::OPTION_NAME );
		delete_option( Settings::OPTION_NAME );
		parent::tear_down();
	}

	public function test_registers_rest_visible_setting_with_defaults(): void {
		Settings::register_settings();
		$registered = get_registered_settings();

		$this->assertArrayHasKey( Settings::OPTION_NAME, $registered );
		$this->assertSame(
			array(
				'format'  => Settings::FORMAT_WEBP,
				'display' => Settings::DISPLAY_OFF,
			),
			$registered[ Settings::OPTION_NAME ]['default']
		);
		$this->assertIsArray( $registered[ Settings::OPTION_NAME ]['show_in_rest'] );
	}

	public function test_sanitizes_valid_values(): void {
		$expected_format = Utils::is_php_compatible_avif() ? Settings::FORMAT_AVIF : Settings::FORMAT_WEBP;

		$this->assertSame(
			array(
				'format'  => $expected_format,
				'display' => Settings::DISPLAY_REWRITE_RULES,
			),
			Settings::sanitize_settings(
				array(
					'format'  => Settings::FORMAT_AVIF,
					'display' => Settings::DISPLAY_REWRITE_RULES,
				)
			)
		);
	}

	public function test_invalid_or_non_array_values_use_safe_defaults(): void {
		$defaults = array(
			'format'  => Settings::FORMAT_WEBP,
			'display' => Settings::DISPLAY_OFF,
		);

		$this->assertSame( $defaults, Settings::sanitize_settings( 'invalid' ) );
		$this->assertSame(
			$defaults,
			Settings::sanitize_settings(
				array(
					'format'  => 'arbitrary-format',
					'display' => 'arbitrary-display',
				)
			)
		);
	}
}
