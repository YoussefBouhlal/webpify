<?php
/**
 * Handle admin settings.
 *
 * @class       Admin
 * @version     1.0.0
 * @package     Webpify/Admin/
 */

namespace Webpify\Admin;

use Webpify\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings
 */
final class Settings {

	/**
	 * Hook in methods.
	 */
	public static function hooks() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles_scripts' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_options_pages' ) );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_styles_scripts( $hook_suffix ) {

		if ( 'settings_page_webpify' === $hook_suffix ) {
			$asset = include Utils::build_path( 'settings.asset.php' );
			wp_enqueue_style( 'webpify_settings', Utils::build_url( 'settings.css' ), array(), $asset['version'] );
			wp_enqueue_script( 'webpify_settings', Utils::build_url( 'settings.js' ), $asset['dependencies'], $asset['version'], array( 'in_footer' => true ) );
			wp_set_script_translations( 'webpify_settings', 'webpify', Utils::plugin_path() . '/languages' );

			wp_enqueue_style( 'wp-components' );
		}
	}

	/**
	 * Add options page.
	 */
	public static function add_options_pages() {
		add_options_page(
			__( 'Webpify', 'webpify' ),
			__( 'Webpify', 'webpify' ),
			'manage_options',
			'webpify',
			array( __CLASS__, 'render_webpify' )
		);
	}

	/**
	 * Render webpify option page.
	 */
	public static function render_webpify() {
		printf(
			'<div class="wrap" id="webpify-settings">%s</div>',
			esc_html__( 'Loading…', 'webpify' )
		);
	}
}
