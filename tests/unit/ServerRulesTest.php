<?php
/**
 * Tests for deterministic server rewrite-rule generation.
 *
 * @package Webpify
 */

use PHPUnit\Framework\TestCase;
use Webpify\Helpers\Apache;
use Webpify\Helpers\Nginx;

final class ServerRulesTest extends TestCase {

	public function test_apache_rules_use_the_site_path_and_prefer_avif(): void {
		$rules = ( new ApacheRulesTestProxy() )->build( '/subdirectory/' );

		$this->assertStringContainsString( 'RewriteBase /subdirectory/', $rules );
		$this->assertLessThan( strpos( $rules, 'image/webp' ), strpos( $rules, 'image/avif' ) );
		$this->assertStringEndsWith( '# END WebPify: Rewrite rules', $rules );
	}

	public function test_nginx_rules_separate_the_closing_brace_and_marker(): void {
		$rules = ( new NginxRulesTestProxy() )->build( '/' );

		$this->assertStringContainsString( 'location ~* ^(/.+)', $rules );
		$this->assertStringContainsString( "}\n# END WebPify: Rewrite rules", $rules );
		$this->assertStringNotContainsString( '}# END WebPify: Rewrite rules', $rules );
	}
}

final class ApacheRulesTestProxy extends Apache {

	public function build( $home_root ) {
		return $this->build_rules( $home_root );
	}
}

final class NginxRulesTestProxy extends Nginx {

	public function build( $home_root ) {
		return $this->build_rules( $home_root );
	}
}
