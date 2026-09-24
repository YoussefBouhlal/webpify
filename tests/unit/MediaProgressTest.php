<?php
/**
 * Tests for bulk progress normalization.
 *
 * @package Webpify
 */

use PHPUnit\Framework\TestCase;
use Webpify\Admin\Media;

final class MediaProgressTest extends TestCase {

	public function test_zero_total_has_zero_progress(): void {
		$this->assertSame(
			array(
				'running'  => false,
				'progress' => '0/0',
				'percent'  => '0%',
			),
			Media::get_progress_data( 0, 0, false )
		);
	}

	public function test_progress_is_clamped_to_valid_bounds(): void {
		$this->assertSame( '0%', Media::get_progress_data( -2, 10, true )['percent'] );
		$this->assertSame( '100%', Media::get_progress_data( 15, 10, true )['percent'] );
		$this->assertSame( '10/10', Media::get_progress_data( 15, 10, true )['progress'] );
	}

	public function test_progress_calculates_percentage(): void {
		$data = Media::get_progress_data( 3, 4, true );

		$this->assertTrue( $data['running'] );
		$this->assertSame( '3/4', $data['progress'] );
		$this->assertSame( '75%', $data['percent'] );
	}
}
