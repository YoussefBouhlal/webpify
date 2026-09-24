<?php
/**
 * Tests for plugin hook registration.
 *
 * @package Webpify
 */

use Webpify\Admin\Media;
use Webpify\Admin\RewriteRules;
use Webpify\Admin\Settings;

final class PluginBootstrapTest extends WP_UnitTestCase {

	public function test_registers_primary_admin_hooks(): void {
		$this->assertSame( 10, has_filter( 'wp_handle_upload_prefilter', array( Media::class, 'image_optimization' ) ) );
		$this->assertSame( 10, has_filter( 'rest_pre_dispatch', array( RewriteRules::class, 'add_rewrite_rules' ) ) );
		$this->assertSame( 10, has_action( 'rest_api_init', array( Settings::class, 'register_settings' ) ) );
		$this->assertSame( 10, has_action( Media::CRON_BULK_HOOK, array( Media::class, 'bulk_optimization_excute' ) ) );
	}
}
