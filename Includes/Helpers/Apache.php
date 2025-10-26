<?php
/**
 * Rewrite rules for Apache.
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
 * Rewrite rules for Apache
 */
class Apache extends RewriteRulesAbstract {

	/**
	 * Get the path to the file.
	 */
	protected function get_file_path() {
		$file_path = $this->get_site_root() . '.htaccess';
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
		$content .= '<IfModule mod_setenvif.c>' . PHP_EOL;
		$content .= "\tSetEnvIf Request_URI \"\\.(jpg|jpeg|jpe|png)$\" REQUEST_image" . PHP_EOL;
		$content .= '</IfModule>' . PHP_EOL . PHP_EOL;

		$content .= '<IfModule mod_rewrite.c>' . PHP_EOL;
		$content .= "\tRewriteEngine On" . PHP_EOL;
		$content .= "\tRewriteBase $home_root" . PHP_EOL;
		// Serve AVIF if browser supports it and file exists.
		$content .= "\tRewriteCond %{HTTP_ACCEPT} image/avif" . PHP_EOL;
		$content .= "\tRewriteCond %{REQUEST_FILENAME}.avif -f" . PHP_EOL;
		$content .= "\tRewriteRule (.+)\\.(jpg|jpeg|jpe|png)$ $1.$2.avif [T=image/avif,NC,E=REQUEST_image:avif,L]" . PHP_EOL;
		// Otherwise, serve WebP if browser supports it and file exists.
		$content .= "\tRewriteCond %{HTTP_ACCEPT} image/webp" . PHP_EOL;
		$content .= "\tRewriteCond %{REQUEST_FILENAME}.webp -f" . PHP_EOL;
		$content .= "\tRewriteRule (.+)\\.(jpg|jpeg|jpe|png)$ $1.$2.webp [T=image/webp,NC,E=REQUEST_image:webp,L]" . PHP_EOL;
		$content .= '</IfModule>' . PHP_EOL . PHP_EOL;

		$content .= '<IfModule mod_headers.c>' . PHP_EOL;
		$content .= "\tHeader append Vary Accept env=REQUEST_image" . PHP_EOL;
		$content .= '</IfModule>' . PHP_EOL . PHP_EOL;

		$content .= '<IfModule mod_mime.c>' . PHP_EOL;
		$content .= "\tAddType image/webp .webp" . PHP_EOL;
		$content .= "\tAddType image/avif .avif" . PHP_EOL;
		$content .= '</IfModule>' . PHP_EOL;
		$content .= "# END $tag_name " . PHP_EOL;

		return trim( $content );
	}
}
