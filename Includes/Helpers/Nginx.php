<?php
/**
 * Rewrite rules for Nginx.
 *
 * @class       Admin
 * @version     1.0.0
 * @package     Webpify/Helpers/
 */

namespace Webpify\Helpers;

use Webpify\Helpers\RewriteRulesAbstract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite rules for Nginx
 */
class Nginx extends RewriteRulesAbstract {

	/**
	 * Get the path to the file.
	 */
	protected function get_file_path() {
		$file_path = $this->get_site_root() . 'conf/webpify.conf';
		return $file_path;
	}

	/**
	 * Get unfiltered new contents to write into the file.
	 */
	protected function get_raw_new_contents() {
		$home_root = wp_parse_url( home_url( '/' ) );
		$home_root = $home_root['path'];
		$tag_name  = $this->tag_name;

		$content  = "# BEGIN $tag_name" . PHP_EOL;
		$content .= "location ~* ^($home_root.+)\\.(jpg|jpeg|jpe|png)$ {" . PHP_EOL;
		$content .= "\tadd_header Vary Accept;" . PHP_EOL . PHP_EOL;
		// Check for AVIF support and file existence.
		$content .= "\tif (\$http_accept ~* \"avif\") {" . PHP_EOL;
		$content .= "\t\tset \$imavif A;" . PHP_EOL;
		$content .= "\t}" . PHP_EOL;
		$content .= "\tif (-f \$request_filename.avif) {" . PHP_EOL;
		$content .= "\t\tset \$imavif \"\${imavif}B\";" . PHP_EOL;
		$content .= "\t}" . PHP_EOL;
		$content .= "\tif (\$imavif = AB) {" . PHP_EOL;
		$content .= "\t\trewrite ^(.*) \$1.avif break;" . PHP_EOL;
		$content .= "\t}" . PHP_EOL . PHP_EOL;
		// Check for WebP support and file existence.
		$content .= "\tif (\$http_accept ~* \"webp\") {" . PHP_EOL;
		$content .= "\t\tset \$imwebp A;" . PHP_EOL;
		$content .= "\t}" . PHP_EOL;
		$content .= "\tif (-f \$request_filename.webp) {" . PHP_EOL;
		$content .= "\t\tset \$imwebp \"\${imwebp}B\";" . PHP_EOL;
		$content .= "\t}" . PHP_EOL;
		$content .= "\tif (\$imwebp = AB) {" . PHP_EOL;
		$content .= "\t\trewrite ^(.*) \$1.webp break;" . PHP_EOL;
		$content .= "\t}" . PHP_EOL;
		$content .= '}';
		$content .= "# END $tag_name " . PHP_EOL;

		return trim( $content );
	}
}
