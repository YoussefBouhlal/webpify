<?php
/**
 * Tests for image conversion.
 *
 * @package Webpify
 */

use PHPUnit\Framework\TestCase;
use Webpify\Helpers\ImageOptimizer;

final class ImageOptimizerTest extends TestCase {

	/**
	 * @var string
	 */
	private $temporary_directory;

	protected function setUp(): void {
		if ( ! extension_loaded( 'gd' ) || ! function_exists( 'imagewebp' ) ) {
			$this->markTestSkipped( 'GD with WebP support is required.' );
		}

		$this->temporary_directory = sys_get_temp_dir() . '/webpify-' . uniqid( '', true );
		mkdir( $this->temporary_directory, 0700, true );
	}

	protected function tearDown(): void {
		$files = glob( $this->temporary_directory . '/*' );
		$files = false === $files ? array() : $files;

		foreach ( $files as $file ) {
			unlink( $file );
		}

		if ( is_dir( $this->temporary_directory ) ) {
			rmdir( $this->temporary_directory );
		}
	}

	public function test_converts_jpeg_to_webp_sidecar(): void {
		$source      = $this->create_jpeg();
		$destination = $source . '.webp';

		$result = ( new ImageOptimizer() )->optimize( $source, $destination, ImageOptimizer::FORMAT_WEBP );

		$this->assertIsArray( $result );
		$this->assertSame( 'image/webp', mime_content_type( $destination ) );
		$this->assertSame( ImageOptimizer::FORMAT_WEBP, $result['format'] );
		$this->assertSame( filesize( $destination ), $result['optimized_size'] );
		$this->assertFileExists( $source );
	}

	public function test_atomically_replaces_an_upload(): void {
		$source = $this->create_jpeg();

		$result = ( new ImageOptimizer() )->optimize( $source, $source, ImageOptimizer::FORMAT_WEBP );

		$this->assertIsArray( $result );
		$this->assertSame( 'image/webp', mime_content_type( $source ) );
		$this->assertSame( $source, $result['path'] );
	}

	public function test_converts_indexed_transparent_png(): void {
		$source      = $this->create_transparent_png();
		$destination = $source . '.webp';

		$result = ( new ImageOptimizer() )->optimize( $source, $destination, ImageOptimizer::FORMAT_WEBP );

		$this->assertIsArray( $result );
		$this->assertSame( array( 8, 8 ), array_slice( getimagesize( $destination ), 0, 2 ) );
	}

	public function test_rejects_invalid_format_without_modifying_source(): void {
		$source   = $this->create_jpeg();
		$contents = file_get_contents( $source );

		$result = ( new ImageOptimizer() )->optimize( $source, $source, 'invalid' );

		$this->assertFalse( $result );
		$this->assertSame( $contents, file_get_contents( $source ) );
	}

	public function test_rejects_corrupt_image_without_creating_destination(): void {
		$source      = $this->temporary_directory . '/invalid.jpg';
		$destination = $source . '.webp';
		file_put_contents( $source, 'not an image' );

		$result = ( new ImageOptimizer() )->optimize( $source, $destination, ImageOptimizer::FORMAT_WEBP );

		$this->assertFalse( $result );
		$this->assertFileDoesNotExist( $destination );
	}

	public function test_converts_to_avif_when_supported(): void {
		if ( ! function_exists( 'imageavif' ) ) {
			$this->markTestSkipped( 'GD does not support AVIF.' );
		}

		$source      = $this->create_jpeg();
		$destination = $source . '.avif';

		$result = ( new ImageOptimizer() )->optimize( $source, $destination, ImageOptimizer::FORMAT_AVIF );

		$this->assertIsArray( $result );
		$this->assertSame( 'image/avif', mime_content_type( $destination ) );
	}

	private function create_jpeg() {
		$path  = $this->temporary_directory . '/source.jpg';
		$image = imagecreatetruecolor( 16, 16 );
		$color = imagecolorallocate( $image, 50, 100, 150 );
		imagefilledrectangle( $image, 0, 0, 15, 15, $color );
		imagejpeg( $image, $path, 90 );
		$this->destroy_image( $image );

		return $path;
	}

	private function create_transparent_png() {
		$path        = $this->temporary_directory . '/transparent.png';
		$image       = imagecreate( 8, 8 );
		$transparent = imagecolorallocatealpha( $image, 0, 0, 0, 127 );
		imagecolortransparent( $image, $transparent );
		imagefilledrectangle( $image, 0, 0, 7, 7, $transparent );
		imagepng( $image, $path );
		$this->destroy_image( $image );

		return $path;
	}

	private function destroy_image( $image ) {
		if ( PHP_VERSION_ID < 80500 ) {
			// phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated -- Required for the supported PHP 7.4 test runtime.
			imagedestroy( $image );
		}
	}
}
