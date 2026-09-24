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
	}

	/**
	 * Checks all plugin requirements. If run in admin context also adds a notice.
	 *
	 * @return boolean
	 */
	private static function check_plugin_requirements() {

		global $wp_version;
		$woocommerce_version = defined( 'WC_VERSION' ) ? constant( 'WC_VERSION' ) : null;
		$errors              = self::get_requirement_errors( PHP_VERSION, $wp_version, PLUGIN_REQUIREMENTS, $woocommerce_version );

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
	 * Return requirement error codes for the supplied runtime versions.
	 *
	 * @param string      $php_version         PHP version.
	 * @param string      $wp_version          WordPress version.
	 * @param array       $requirements        Required versions.
	 * @param string|null $woocommerce_version WooCommerce version, when loaded.
	 * @return int[]
	 */
	public static function get_requirement_errors( $php_version, $wp_version, $requirements, $woocommerce_version = null ) {
		$errors = array();

		if ( ! version_compare( $php_version, $requirements['php_version'], '>=' ) ) {
			$errors[] = 1;
		}

		if ( ! version_compare( $wp_version, $requirements['wp_version'], '>=' ) ) {
			$errors[] = 2;
		}

		if ( isset( $requirements['wc_version'] ) && ( null === $woocommerce_version || ! version_compare( $woocommerce_version, $requirements['wc_version'], '>=' ) ) ) {
			$errors[] = 3;
		}

		return $errors;
	}
}
