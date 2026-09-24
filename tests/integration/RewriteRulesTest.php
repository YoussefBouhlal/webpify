<?php
/**
 * Tests for rewrite-rule REST handling.
 *
 * @package Webpify
 */

use Webpify\Admin\RewriteRules;
use Webpify\Admin\Settings;

final class RewriteRulesTest extends WP_UnitTestCase {

	/**
	 * @var bool
	 */
	private $was_apache;

	/**
	 * @var bool
	 */
	private $was_nginx;

	public function set_up(): void {
		parent::set_up();
		$this->was_apache = $GLOBALS['is_apache'];
		$this->was_nginx  = $GLOBALS['is_nginx'];
	}

	public function tear_down(): void {
		$GLOBALS['is_apache'] = $this->was_apache;
		$GLOBALS['is_nginx']  = $this->was_nginx;
		wp_set_current_user( 0 );
		delete_option( Settings::OPTION_NAME );
		parent::tear_down();
	}

	public function test_unauthorized_request_cannot_trigger_rewrite_changes(): void {
		wp_set_current_user( 0 );
		$request = $this->create_settings_request( Settings::DISPLAY_REWRITE_RULES );
		$result  = new stdClass();

		$this->assertSame( $result, RewriteRules::add_rewrite_rules( $result, null, $request ) );
	}

	public function test_authorized_request_reports_unsupported_server(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		update_option(
			Settings::OPTION_NAME,
			array(
				'format'  => Settings::FORMAT_WEBP,
				'display' => Settings::DISPLAY_OFF,
			)
		);
		$GLOBALS['is_apache'] = false;
		$GLOBALS['is_nginx']  = false;

		$result = RewriteRules::add_rewrite_rules( null, null, $this->create_settings_request( Settings::DISPLAY_REWRITE_RULES ) );

		$this->assertWPError( $result );
		$this->assertSame( 'server_not_supported', $result->get_error_code() );
	}

	public function test_unrelated_route_is_ignored(): void {
		$request = new WP_REST_Request( 'POST', '/wp/v2/posts' );
		$result  = new stdClass();

		$this->assertSame( $result, RewriteRules::add_rewrite_rules( $result, null, $request ) );
	}

	private function create_settings_request( $display ) {
		$request = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body(
			wp_json_encode(
				array(
					Settings::OPTION_NAME => array(
						'format'  => Settings::FORMAT_WEBP,
						'display' => $display,
					),
				)
			)
		);

		return $request;
	}
}
