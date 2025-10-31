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

	const META_ALREADY_OPTIMIZED = 'webpify_already_optimized';
	const OPTION_BULK_STATUS     = 'webpify_bulk_status';
	const OPTION_BULK_TOTAL      = 'webpify_bulk_total';
	const OPTION_BULK_CURRENT    = 'webpify_bulk_current';
	const CRON_BULK_HOOK         = 'webpify_bulk_optimization_hook';
	const CRON_BULK_RECURRENCE   = 'webpify_bulk_optimization';
	const ALLOWED_MIME_TYPES     = array( 'image/jpeg', 'image/png' );

	/**
	 * Hook in methods.
	 */
	public static function hooks() {
		add_filter( 'wp_handle_upload_prefilter', array( __CLASS__, 'image_optimizition' ) );
		add_action( 'wp_ajax_webpify_bulk_optimization_start', array( __CLASS__, 'bulk_optimization_start' ) );
		add_action( 'wp_ajax_webpify_bulk_optimization_end', array( __CLASS__, 'bulk_optimization_end' ) );
		add_action( self::CRON_BULK_HOOK, array( __CLASS__, 'bulk_optimization_excute' ) );
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

		$file_tmp_name = $file['tmp_name'] ?? '';

		$gd_image = self::get_gd_image( $file_tmp_name );
		if ( empty( $gd_image ) ) {
			return false;
		}

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

		$media_ids = self::get_unoptimized_media_ids();
		if ( empty( $media_ids ) ) {
			wp_send_json_error( __( 'No images to optimize.', 'webpify' ) );
		}

		$status = get_option( self::OPTION_BULK_STATUS, 'finish' );
		if ( $status !== 'finish' ) {
			wp_send_json_error( __( 'Bulk optimization is already running.', 'webpify' ) );
		}

		if ( ! wp_next_scheduled( self::CRON_BULK_HOOK ) ) {
			wp_schedule_event( time(), self::CRON_BULK_RECURRENCE, self::CRON_BULK_HOOK );

			$total   = count( $media_ids );
			$current = 0;

			update_option( self::OPTION_BULK_TOTAL, $total, false );
			update_option( self::OPTION_BULK_CURRENT, $current, false );
			update_option( self::OPTION_BULK_STATUS, 'running', false );

			$data = array(
				'progress' => $current . '/' . $total,
				'percent'  => $total ? round( abs( ( $current / $total ) ) * 100 ) . '%' : '0%',
			);
			wp_send_json_success( $data );
		}

		wp_send_json_error( __( 'Refresh the page and try again.', 'webpify' ) );
	}

	/**
	 * End bulk optimization
	 */
	public static function bulk_optimization_end() {

		$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'webpify_settings_bulk' ) ) {
			wp_send_json_error( __( 'Refresh the page and try again.', 'webpify' ) );
		}

		if ( self::clear_bulk_optimization() ) {
			wp_send_json_success( __( 'Bulk optimization stopped.', 'webpify' ) );
		}

		wp_send_json_error( __( 'Refresh the page and try again.', 'webpify' ) );
	}

	/**
	 * Bulk optimization excute
	 */
	public static function bulk_optimization_excute() {

		$time_start = microtime( true );
		$media_ids  = self::get_unoptimized_media_ids();

		if ( empty( $media_ids ) ) {
			self::end_cron_job();
		}

		$status = get_option( self::OPTION_BULK_STATUS, 'finish' );
		if ( $status !== 'running' ) {
			self::end_cron_job();
		}

		$current = (int) get_option( self::OPTION_BULK_CURRENT, 0 );

		foreach ( $media_ids as $id ) {
			if ( $time_start + 55 < microtime( true ) ) {
				exit;
			}

			$sizes     = self::get_media_files( $id );
			$new_sizes = array();

			foreach ( $sizes as $key => $size ) {
				$result = self::optimize_local_file( $size );

				if ( $result ) {
					$new_sizes[ $key ] = $result;
				}
			}
		}

		self::end_cron_job();
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

		if ( false === in_array( $file_mime_type, self::ALLOWED_MIME_TYPES, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get gd image
	 *
	 * @param string $file_path file path.
	 */
	private static function get_gd_image( $file_path ) {

		$file_mime_type = wp_get_image_mime( $file_path );

		if ( 'image/jpeg' === $file_mime_type ) {
			$gd_image = imagecreatefromjpeg( $file_path );
		} elseif ( 'image/png' === $file_mime_type ) {
			$gd_image = imagecreatefrompng( $file_path );
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

	/**
	 * End cron job
	 */
	private function end_cron_job() {
		self::clear_bulk_optimization();
		exit;
	}

	/**
	 * Clear bulk optimization
	 */
	private static function clear_bulk_optimization() {
		update_option( self::OPTION_BULK_STATUS, 'finish' );

		if ( wp_next_scheduled( self::CRON_BULK_HOOK ) ) {
			wp_clear_scheduled_hook( self::CRON_BULK_HOOK );
			return true;
		}

		return false;
	}

	/**
	 * Get unoptimized media ids
	 */
	private static function get_unoptimized_media_ids() {

		$statuses        = array(
			'inherit' => 'inherit',
			'private' => 'private',
		);
		$custom_statuses = get_post_stati( array( 'public' => true ) );
		unset( $custom_statuses['publish'] );
		if ( $custom_statuses ) {
			$statuses = array_merge( $statuses, $custom_statuses );
		}

		$media_ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => self::ALLOWED_MIME_TYPES,
				'post_status'    => array_keys( $statuses ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => self::META_ALREADY_OPTIMIZED,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => self::META_ALREADY_OPTIMIZED,
						'compare' => '!=',
						'value'   => '1',
					),
				),
			)
		);

		return $media_ids;
	}

	/**
	 * Get media files
	 *
	 * @param int $media_id media id.
	 */
	private static function get_media_files( $media_id ) {

		$fullsize_path = get_attached_file( $media_id );
		if ( ! $fullsize_path ) {
			return array();
		}

		$media_data = wp_get_attachment_image_src( $media_id, 'full' );
		$file_type  = wp_check_filetype( $fullsize_path );
		$all_sizes  = array(
			'full' => array(
				'size'      => 'full',
				'path'      => $fullsize_path,
				'width'     => $media_data[1],
				'height'    => $media_data[2],
				'mime-type' => $file_type['type'],
				'disabled'  => false,
			),
		);

		$sizes = wp_get_attachment_metadata( $media_id, true );
		$sizes = ! empty( $sizes['sizes'] ) && is_array( $sizes['sizes'] ) ? $sizes['sizes'] : array();

		$dir_path = trailingslashit( dirname( $fullsize_path ) );

		foreach ( $sizes as $size => $size_data ) {
			$all_sizes[ $size ] = array(
				'size'      => $size,
				'path'      => $dir_path . $size_data['file'],
				'width'     => $size_data['width'],
				'height'    => $size_data['height'],
				'mime-type' => $size_data['mime-type'],
				'disabled'  => false,
			);
		}

		return $all_sizes;
	}

	/**
	 * Optimize local file
	 *
	 * @param array $size size.
	 */
	private static function optimize_local_file( $size ) {

		$file_path = $size['path'] ?? '';
		if ( ! is_file( $file_path ) ) {
			return false;
		}

		$settings = get_option( Settings::OPTION_NAME );
		$format   = $settings['format'] ?? '1';
		$format   = absint( $format );

		$real_type = mime_content_type( $file_path );
		if ( ! in_array( $real_type, self::ALLOWED_MIME_TYPES, true ) ) {
			return false;
		}

		$gd_image = self::get_gd_image( $file_path );
		if ( empty( $gd_image ) ) {
			return false;
		}

		$size_before = filesize( $file_path );

		if ( 1 === $format ) {

			$format_name     = 'webp';
			$new_path        = $file_path . '.' . $format_name;
			$optimized_image = imagewebp( $gd_image, $new_path, 75 );

			if ( $optimized_image ) {
				$size_after = filesize( $new_path );
			}
		} elseif ( 2 === $format ) {

			$format_name     = 'avif';
			$new_path        = $file_path . '.' . $format_name;
			$optimized_image = imageavif( $gd_image, $new_path, 50 );

			if ( $optimized_image ) {
				$size_after = filesize( $new_path );
			}
		}

		if ( empty( $size_after ) ) {
			return false;
		}

		$deference = $size_before - $size_after;
		$percent   = $deference / $size_before * 100;

		return array(
			'success'        => 1,
			'original_size'  => $size_before,
			'optimized_size' => $size_after,
			'percent'        => round( $percent, 2 ),
			'path'           => $new_path,
			'format'         => $format_name,
		);
	}
}
