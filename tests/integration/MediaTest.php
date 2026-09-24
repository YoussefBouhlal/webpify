<?php
/**
 * Tests for media integration behavior.
 *
 * @package Webpify
 */

use Webpify\Admin\Media;
use Webpify\Admin\Settings;

final class MediaTest extends WP_UnitTestCase {

	/**
	 * @var string[]
	 */
	private $temporary_files = array();

	public function tear_down(): void {
		foreach ( $this->temporary_files as $file ) {
			if ( file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}

		delete_option( Settings::OPTION_NAME );
		delete_option( Media::OPTION_BULK_STATUS );
		wp_clear_scheduled_hook( Media::CRON_BULK_HOOK );
		parent::tear_down();
	}

	public function test_upload_prefilter_converts_supported_image(): void {
		$path                    = $this->create_jpeg();
		$this->temporary_files[] = $path;
		update_option(
			Settings::OPTION_NAME,
			array(
				'format'  => Settings::FORMAT_WEBP,
				'display' => Settings::DISPLAY_OFF,
			)
		);

		$result = Media::image_optimization(
			array(
				'tmp_name' => $path,
				'type'     => 'image/jpeg',
				'size'     => filesize( $path ),
			)
		);

		$this->assertSame( 'image/webp', $result['type'] );
		$this->assertSame( 'image/webp', mime_content_type( $path ) );
		$this->assertSame( filesize( $path ), $result['size'] );
	}

	public function test_bulk_cron_finishes_cleanly_when_there_is_no_work(): void {
		update_option( Media::OPTION_BULK_STATUS, 'running', false );

		Media::bulk_optimization_excute();

		$this->assertSame( 'finish', get_option( Media::OPTION_BULK_STATUS ) );
	}

	private function create_jpeg() {
		$path  = wp_tempnam( 'webpify-integration.jpg' );
		$image = imagecreatetruecolor( 16, 16 );
		$color = imagecolorallocate( $image, 50, 100, 150 );
		imagefilledrectangle( $image, 0, 0, 15, 15, $color );
		imagejpeg( $image, $path, 90 );

		if ( PHP_VERSION_ID < 80500 ) {
			// phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated -- Required for the supported PHP 7.4 test runtime.
			imagedestroy( $image );
		}

		return $path;
	}
}
