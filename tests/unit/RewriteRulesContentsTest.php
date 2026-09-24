<?php
/**
 * Tests for deterministic rewrite-rule content merging.
 *
 * @package Webpify
 */

use PHPUnit\Framework\TestCase;
use Webpify\Helpers\RewriteRulesAbstract;

final class RewriteRulesContentsTest extends TestCase {

	/**
	 * @var RewriteRulesContentsTestProxy
	 */
	private $rules;

	protected function setUp(): void {
		$this->rules = new RewriteRulesContentsTestProxy();
	}

	public function test_adds_rules_before_unrelated_contents(): void {
		$existing = "# Existing rule\nValue";
		$new      = "# BEGIN WebPify: Rewrite rules\nGenerated\n# END WebPify: Rewrite rules";

		$this->assertSame( $new . "\n\n" . $existing, $this->rules->merge( $existing, $new ) );
	}

	public function test_replaces_every_existing_marked_block(): void {
		$existing = "Before\n# BEGIN WebPify: Rewrite rules\nOld one\n# END WebPify: Rewrite rules\nMiddle\n# BEGIN WebPify: Rewrite rules\nOld two\n# END WebPify: Rewrite rules\nAfter";
		$new      = "# BEGIN WebPify: Rewrite rules\nNew\n# END WebPify: Rewrite rules";

		$actual = $this->rules->merge( $existing, $new );

		$this->assertSame( 1, substr_count( $actual, '# BEGIN WebPify: Rewrite rules' ) );
		$this->assertStringNotContainsString( 'Old one', $actual );
		$this->assertStringNotContainsString( 'Old two', $actual );
		$this->assertStringContainsString( "Before\n\nMiddle\n\nAfter", $actual );
	}

	public function test_removes_rules_without_removing_other_contents(): void {
		$existing = "Before\n# BEGIN WebPify: Rewrite rules\nGenerated\n# END WebPify: Rewrite rules\nAfter";

		$this->assertSame( "Before\n\nAfter", $this->rules->merge( $existing, '' ) );
	}
}

final class RewriteRulesContentsTestProxy extends RewriteRulesAbstract {

	public function merge( $contents, $new_contents ) {
		return $this->merge_tagged_contents( $contents, $new_contents );
	}

	protected function get_file_path() {
		return '';
	}

	protected function get_raw_new_contents() {
		return '';
	}
}
