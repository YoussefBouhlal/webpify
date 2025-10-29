<?php
/**
 * Handle media hooks.
 *
 * @class       Admin
 * @version     1.0.0
 * @package     Webpify/Admin/
 */

namespace Webpify\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media
 */
final class Media {

	/**
	 * Hook in methods.
	 */
	public static function hooks() {
		add_filter( 'wp_handle_upload_prefilter', array( __CLASS__, 'image_optimizition' ) );
		add_action( 'wp_ajax_webpify_bulk_optimization_start', array( __CLASS__, 'bulk_optimization_start' ) );
		add_action( 'wp_ajax_webpify_bulk_optimization_end', array( __CLASS__, 'bulk_optimization_end' ) );
	}

	/**
	 * Optimize an image added in wp_media
	 *
	 * @param array $file file data.
	 */
	public static function image_optimizition( $file ) {

		$settings = get_option( Settings::OPTION_NAME );
		$format   = $settings['format'] ?? '1';
		$format   = absint( $format );

		if ( ! self::validate_image( $file ) ) {
			return $file;
		}

		$gd_image = self::get_gd_image( $file );
		if ( empty( $gd_image ) ) {
			return false;
		}

		$file_tmp_name = $file['tmp_name'] ?? '';

		if ( 1 === $format ) {
			$optimized_image = imagewebp( $gd_image, $file_tmp_name, 75 );
			$optimized_type  = 'image/webp';
		} elseif ( 2 === $format ) {
			$optimized_image = imageavif( $gd_image, $file_tmp_name, 50 );
			$optimized_type  = 'image/avif';
		}
		imagedestroy( $gd_image );

		if ( empty( $optimized_image ) ) {
			return $file;
		}

		if ( $optimized_image ) {
			$file['size'] = filesize( $file_tmp_name );
			$file['type'] = $optimized_type;
		}

		return $file;
	}

	/**
	 * Start bulk optimization
	 */
	public static function bulk_optimization_start() {

		$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'webpify_settings_bulk' ) ) {
			wp_send_json_error( __( 'Refresh the page and try again.', 'webpify' ) );
		}

		wp_send_json_success();
	}

	/**
	 * End bulk optimization
	 */
	public static function bulk_optimization_end() {

		$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'webpify_settings_bulk' ) ) {
			wp_send_json_error( __( 'Refresh the page and try again.', 'webpify' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Check if the file is an image
	 *
	 * @param array $file file data.
	 */
	private static function validate_image( $file ) {

		$file_tmp_name = $file['tmp_name'] ?? '';
		if ( empty( $file_tmp_name ) ) {
			return false;
		}

		$file_size = wp_getimagesize( $file_tmp_name );
		if ( ! $file_size ) {
			return false;
		}

		$file_mime_type = wp_get_image_mime( $file_tmp_name );
		if ( ! $file_mime_type ) {
			return false;
		}

		$types_to_optimize = array( 'image/jpeg', 'image/png' );
		if ( false === in_array( $file_mime_type, $types_to_optimize, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get gd image
	 *
	 * @param array $file file data.
	 */
	private static function get_gd_image( $file ) {

		$file_tmp_name = $file['tmp_name'] ?? '';
		if ( empty( $file_tmp_name ) ) {
			return false;
		}

		$file_mime_type = wp_get_image_mime( $file_tmp_name );

		if ( 'image/jpeg' === $file_mime_type ) {
			$gd_image = imagecreatefromjpeg( $file_tmp_name );
		} elseif ( 'image/png' === $file_mime_type ) {
			$gd_image = imagecreatefrompng( $file_tmp_name );
		}

		if ( empty( $gd_image ) ) {
			return false;
		}

		if ( ! imageistruecolor( $gd_image ) ) {
			$truecolor = imagecreatetruecolor( imagesx( $gd_image ), imagesy( $gd_image ) );

			if ( 'image/png' === $file_mime_type ) {
				imagealphablending( $truecolor, false );
				imagesavealpha( $truecolor, true );
				$transparent = imagecolorallocatealpha( $truecolor, 0, 0, 0, 127 );
				imagefilledrectangle( $truecolor, 0, 0, imagesx( $gd_image ), imagesy( $gd_image ), $transparent );
			}

			imagecopy( $truecolor, $gd_image, 0, 0, 0, 0, imagesx( $gd_image ), imagesy( $gd_image ) );
			$gd_image = $truecolor;
		}

		return $gd_image;
	}
}
