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

	const OPTION_NAME           = 'webpify_settings';
	const FORMAT_WEBP           = '1';
	const FORMAT_AVIF           = '2';
	const DISPLAY_OFF           = '1';
	const DISPLAY_REWRITE_RULES = '2';

	/**
	 * Hook in methods.
	 */
	public static function hooks() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles_scripts' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_options_pages' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'plugin_action_links_webpify/webpify.php', array( __CLASS__, 'add_settings_link' ) );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_styles_scripts( $hook_suffix ) {

		if ( 'settings_page_webpify' === $hook_suffix ) {
			$asset = include Utils::build_path( 'settings.asset.php' );
			wp_enqueue_style( 'wp-components' );
			wp_enqueue_style( 'webpify_settings', Utils::build_url( 'settings.css' ), array(), $asset['version'] );
			wp_enqueue_script( 'webpify_settings', Utils::build_url( 'settings.js' ), $asset['dependencies'], $asset['version'], array( 'in_footer' => true ) );
			wp_set_script_translations( 'webpify_settings', 'webpify', Utils::plugin_path() . '/languages' );
			wp_localize_script(
				'webpify_settings',
				'WEBPIFY_SETTINGS',
				array(
					'optionName'          => self::OPTION_NAME,
					'ajaxUrl'             => Utils::ajax_url(),
					'nonce'               => wp_create_nonce( 'webpify_settings_bulk' ),
					'isPhpCompatibleAvif' => Utils::is_php_compatible_avif(),
				),
			);
		}
	}

	/**
	 * Add options page.
	 */
	public static function add_options_pages() {
		add_options_page(
			__( 'WebPify', 'webpify' ),
			__( 'WebPify', 'webpify' ),
			'manage_options',
			'webpify',
			array( __CLASS__, 'render_webpify' )
		);
	}

	/**
	 * Render webpify option page.
	 */
	public static function render_webpify() {
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WebPify Settings', 'webpify' ); ?></h1>
				<div id="JS-webpify-settings" class="webpify-settings"></div>
			</div>
		<?php
	}

	/**
	 * Register webpify settings page.
	 */
	public static function register_settings() {
		$default      = array(
			'format'  => self::FORMAT_WEBP,
			'display' => self::DISPLAY_OFF,
		);
		$show_in_rest = array(
			'schema' => array(
				'type'       => 'object',
				'properties' => array(
					'format'  => array(
						'type' => 'string',
					),
					'display' => array(
						'type' => 'string',
					),
				),
			),
		);

		register_setting(
			'options',
			'webpify_settings',
			array(
				'type'              => 'object',
				'default'           => $default,
				'show_in_rest'      => $show_in_rest,
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			),
		);
	}

	/**
	 * Sanitize webpify settings.
	 * 
	 * @return array
	 */
	public static function sanitize_settings( $value ) {
		return array(
			'format'  => sanitize_text_field( wp_unslash( $value['format'] ?? '' ) ),
			'display' => sanitize_text_field( wp_unslash( $value['display'] ?? '' ) ),
		);
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * @param array $links Array of plugin action links.
	 */
	public static function add_settings_link( $links ) {

		$settings_link = '<a href="' . admin_url( 'options-general.php?page=webpify' ) . '">' . esc_html__( 'Settings', 'webpify' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}
