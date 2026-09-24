<?php
/**
 * Image conversion helper.
 *
 * @package Webpify/Helpers/
 */

namespace Webpify\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert supported images using GD without WordPress state.
 */
final class ImageOptimizer {

	const FORMAT_WEBP = 'webp';
	const FORMAT_AVIF = 'avif';

	const MIME_WEBP = 'image/webp';
	const MIME_AVIF = 'image/avif';

	const ALLOWED_MIME_TYPES = array( 'image/jpeg', 'image/png' );

	/**
	 * Convert an image to a destination path.
	 *
	 * @param string $source_path      Source image path.
	 * @param string $destination_path Destination image path.
	 * @param string $format           Destination format.
	 * @return array|false
	 */
	public function optimize( $source_path, $destination_path, $format ) {
		if ( ! is_file( $source_path ) || ! in_array( $format, array( self::FORMAT_WEBP, self::FORMAT_AVIF ), true ) ) {
			return false;
		}

		$source_mime = mime_content_type( $source_path );
		if ( ! in_array( $source_mime, self::ALLOWED_MIME_TYPES, true ) ) {
			return false;
		}

		$image = $this->create_image( $source_path, $source_mime );
		if ( ! $image ) {
			return false;
		}

		$temporary_path = tempnam( dirname( $destination_path ), '.webpify-' );
		if ( false === $temporary_path ) {
			$this->destroy_image( $image );
			return false;
		}

		$original_size = filesize( $source_path );

		try {
			$encoded = $this->encode_image( $image, $temporary_path, $format );
			if ( ! $encoded ) {
				return false;
			}

			$optimized_size = filesize( $temporary_path );
			if ( false === $optimized_size || 0 === $optimized_size ) {
				return false;
			}

			$permissions = fileperms( $source_path );
			if ( false !== $permissions ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
				chmod( $temporary_path, $permissions & 0777 );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
			if ( ! rename( $temporary_path, $destination_path ) ) {
				return false;
			}

			$difference = $original_size - $optimized_size;
			$percent    = $original_size > 0 ? round( $difference / $original_size * 100, 2 ) : 0;

			return array(
				'success'        => 1,
				'original_size'  => $original_size,
				'optimized_size' => $optimized_size,
				'percent'        => $percent,
				'path'           => $destination_path,
				'format'         => $format,
				'mime_type'      => self::FORMAT_AVIF === $format ? self::MIME_AVIF : self::MIME_WEBP,
			);
		} finally {
			$this->destroy_image( $image );

			if ( file_exists( $temporary_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				unlink( $temporary_path );
			}
		}
	}

	/**
	 * Create a true-color GD image.
	 *
	 * @param string $source_path Source image path.
	 * @param string $source_mime Source MIME type.
	 * @return \GdImage|resource|false
	 */
	private function create_image( $source_path, $source_mime ) {
		if ( 'image/jpeg' === $source_mime ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$image = @imagecreatefromjpeg( $source_path );
		} else {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$image = @imagecreatefrompng( $source_path );
		}

		if ( ! $image || imageistruecolor( $image ) ) {
			return $image;
		}

		$truecolor = imagecreatetruecolor( imagesx( $image ), imagesy( $image ) );
		if ( ! $truecolor ) {
			$this->destroy_image( $image );
			return false;
		}

		if ( 'image/png' === $source_mime ) {
			imagealphablending( $truecolor, false );
			imagesavealpha( $truecolor, true );
			$transparent = imagecolorallocatealpha( $truecolor, 0, 0, 0, 127 );
			imagefilledrectangle( $truecolor, 0, 0, imagesx( $image ), imagesy( $image ), $transparent );
		}

		imagecopy( $truecolor, $image, 0, 0, 0, 0, imagesx( $image ), imagesy( $image ) );
		$this->destroy_image( $image );

		return $truecolor;
	}

	/**
	 * Release GD resources on PHP versions where this is still required.
	 *
	 * @param \GdImage|resource $image GD image.
	 */
	private function destroy_image( $image ) {
		if ( PHP_VERSION_ID < 80500 ) {
			// phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated -- Required to release GD resources on supported PHP versions below 8.0.
			imagedestroy( $image );
		}
	}

	/**
	 * Encode a GD image.
	 *
	 * @param \GdImage|resource $image            GD image.
	 * @param string            $destination_path Temporary destination path.
	 * @param string            $format           Destination format.
	 */
	private function encode_image( $image, $destination_path, $format ) {
		if ( self::FORMAT_AVIF === $format ) {
			return function_exists( 'imageavif' ) && imageavif( $image, $destination_path, 50 );
		}

		return function_exists( 'imagewebp' ) && imagewebp( $image, $destination_path, 75 );
	}
}
