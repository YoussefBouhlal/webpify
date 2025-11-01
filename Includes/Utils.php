<?php
/**
 * Utility methods
 *
 * @class       Utils
 * @version     1.0.0
 * @package     Webpify
 */

namespace Webpify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utils class
 */
final class Utils {

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public static function is_request( $type ) {

		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' ) && DOING_AJAX;
			case 'cron':
				return defined( 'DOING_CRON' ) && DOING_CRON;
			case 'frontend':
				return ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) && ( ! defined( 'DOING_CRON' ) || ! DOING_CRON );
		}
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return untrailingslashit( plugins_url( '/', PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( plugin_dir_path( PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public static function template_path() {
		// Allow 3rd party plugin filter template path from their plugin.
		return apply_filters( 'webpify_template_path', 'webpify/' );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public static function ajax_url() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Get the path to the build directory.
	 *
	 * @param  string $filename Filename.
	 */
	public static function build_path( $filename ) {
		return self::plugin_path() . '/build/' . $filename;
	}

	/**
	 * Get the url to the build directory.
	 *
	 * @param  string $filename Filename.
	 */
	public static function build_url( $filename ) {
		return self::plugin_url() . '/build/' . $filename;
	}

	/**
	 * Is PHP compatible with AVIF?
	 */
	public static function is_php_compatible_avif() {
		return is_php_version_compatible( '8.1.0' ) && function_exists( 'imageavif' );
	}
}
