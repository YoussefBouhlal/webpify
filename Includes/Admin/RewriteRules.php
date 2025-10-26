<?php
/**
 * Handle rewrite rules.
 *
 * @class       Admin
 * @version     1.0.0
 * @package     Webpify/Admin/
 */

namespace Webpify\Admin;

use Webpify\Helpers\Apache;
use Webpify\Helpers\Nginx;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite rules
 */
final class RewriteRules {

	/**
	 * Hook in methods.
	 */
	public static function hooks() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'add_rewrite_rules' ), 10, 3 );
	}

	/**
	 * Handle the rewrite rules when saving the settings.
	 *
	 * @param array  $result Result.
	 * @param array  $server Server.
	 * @param object $request Request.
	 */
	public static function add_rewrite_rules( $result, $server, $request ) {
		if ( $request->get_route() === '/wp/v2/settings' && $request->get_method() === 'POST' ) {

			$params           = $request->get_json_params();
			$webpify_settings = $params['webpify_settings'] ?? '';

			if ( ! empty( $webpify_settings ) ) {

				$old_value     = get_option( Settings::OPTION_NAME );
				$display       = $webpify_settings['display'] ?? '';
				$old_display   = $old_value['display'] ?? '';
				$is_rewrite    = '3' === $display;
				$was_rewrite   = '3' === $old_display;
				$add_or_remove = false;

				if ( $is_rewrite && ! $was_rewrite ) {
					$add_or_remove = 'add';
				} elseif ( ! $is_rewrite && $was_rewrite ) {
					$add_or_remove = 'remove';
				}

				if ( $add_or_remove ) {
					global $is_apache, $is_nginx;

					$rules = null;
					if ( $is_apache ) {
						$rules = new Apache();
					} elseif ( $is_nginx ) {
						$rules = new Nginx();
					}

					if ( ! $rules ) {
						return new \WP_Error(
							'server_not_supported',
							__( 'Your server is not supported.', 'webpify' ),
							array( 'status' => 400 ),
						);
					}

					if ( 'add' === $add_or_remove ) {
						$result_file = $rules->add();
					} else {
						$result_file = $rules->remove();
					}

					if ( is_wp_error( $result_file ) ) {
						return new \WP_Error(
							$result_file->get_error_code(),
							$result_file->get_error_message(),
							array( 'status' => 400 ),
						);
					}
				}
			}
		}

		return $result;
	}
}
