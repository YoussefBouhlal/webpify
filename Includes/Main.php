<?php
/**
 * Main class.
 *
 * @package  Webpify
 * @version  1.0.0
 */

namespace Webpify;

use Webpify\Admin\Main as Admin;
use Webpify\Front\Main as Front;

/**
 * Base Plugin class holding generic functionality
 */
final class Main {

	/**
	 * Constructor
	 */
	public static function bootstrap() {

		register_activation_hook( PLUGIN_FILE, array( Install::class, 'install' ) );

		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );

		add_action( 'init', array( __CLASS__, 'init' ) );

		// Perform other actions when plugin is loaded.
		do_action( 'webpify_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'webpify' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'webpify' ), '1.0.0' );
	}

	/**
	 * Include plugins files and hook into actions and filters.
	 *
	 * @since  1.0.0
	 */
	public static function load() {

		if ( ! self::check_plugin_requirements() ) {
			return;
		}

		if ( Utils::is_request( 'admin' ) ) {
			Admin::hooks();
		}

		if ( Utils::is_request( 'frontend' ) ) {
			Front::hooks();
		}

		// Set up localisation.
		self::load_plugin_textdomain();

		// Init action.
		do_action( 'webpify_loaded' );
	}

	/**
	 * Checks all plugin requirements. If run in admin context also adds a notice.
	 *
	 * @return boolean
	 */
	private static function check_plugin_requirements() {

		$errors = array();
		global $wp_version;

		if ( ! version_compare( PHP_VERSION, PLUGIN_REQUIREMENTS['php_version'], '>=' ) ) {
			/* Translators: The minimum PHP version */
			$errors[] = sprintf( esc_html__( 'WordPress Plugin Boilerplate requires a minimum PHP version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['php_version'] );
		}

		if ( ! version_compare( $wp_version, PLUGIN_REQUIREMENTS['wp_version'], '>=' ) ) {
			/* Translators: The minimum WP version */
			$errors[] = sprintf( esc_html__( 'WordPress Plugin Boilerplate requires a minimum WordPress version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['wp_version'] );
		}

		if ( isset( PLUGIN_REQUIREMENTS['wc_version'] ) && ( ! defined( 'WC_VERSION' ) || ! version_compare( WC_VERSION, PLUGIN_REQUIREMENTS['wc_version'], '>=' ) ) ) {
			/* Translators: The minimum WC version */
			$errors[] = sprintf( esc_html__( 'WordPress Plugin Boilerplate requires a minimum WooCommerce version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['wc_version'] );
		}

		if ( empty( $errors ) ) {
			return true;
		}

		if ( Utils::is_request( 'admin' ) ) {

			add_action(
				'admin_notices',
				function () use ( $errors ) {
					?>
					<div class="notice notice-error">
						<?php
						foreach ( $errors as $error ) {
							echo '<p>' . esc_html( $error ) . '</p>';
						}
						?>
					</div>
					<?php
				}
			);

			return;
		}

		return false;
	}

	/**
	 * Load Localisation files.
	 */
	private static function load_plugin_textdomain() {
		load_plugin_textdomain( 'webpify', false, plugin_basename( __DIR__ ) . '/languages' );
	}
}
