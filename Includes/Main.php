<?php
/**
 * Main class.
 *
 * @package  Webpify
 * @version  1.0.0
 */

namespace Webpify;

use Webpify\Utils;
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

		// Perform other actions when plugin is loaded.
		do_action( 'webpify_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( 'Cheatin&#8217; huh?' ), '1.0.0' );
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

		Admin::hooks();
		Front::hooks();

		// Set up localisation.
		self::load_plugin_textdomain();
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
			$errors[] = 1;
		}

		if ( ! version_compare( $wp_version, PLUGIN_REQUIREMENTS['wp_version'], '>=' ) ) {
			$errors[] = 2;
		}

		if ( isset( PLUGIN_REQUIREMENTS['wc_version'] ) && ( ! defined( 'WC_VERSION' ) || ! version_compare( WC_VERSION, PLUGIN_REQUIREMENTS['wc_version'], '>=' ) ) ) {
			$errors[] = 3;
		}

		if ( empty( $errors ) ) {
			return true;
		}

		if ( Utils::is_request( 'admin' ) ) {

			add_action(
				'admin_notices',
				function () use ( $errors ) {

					$errors_notices = array(
						/* Translators: The minimum PHP version */
						1 => sprintf( esc_html__( 'WebPify requires a minimum PHP version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['php_version'] ),
						/* Translators: The minimum WP version */
						2 => sprintf( esc_html__( 'WebPify requires a minimum WordPress version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['wp_version'] ),
						/* Translators: The minimum WC version */
						3 => sprintf( esc_html__( 'WebPify requires a minimum WooCommerce version of %s.', 'webpify' ), PLUGIN_REQUIREMENTS['wc_version'] ),
					);

					?>
					<div class="notice notice-error">
						<?php
						foreach ( $errors as $error ) {
							echo '<p>' . esc_html( $errors_notices[ $error ] ) . '</p>';
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
		load_plugin_textdomain( 'webpify', false, Utils::plugin_path() . '/languages' );
	}
}
