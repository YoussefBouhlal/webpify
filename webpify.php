<?php
/**
 * Plugin Name:       WebPify
 * Plugin URI:        https://github.com/YoussefBouhlal/webpify
 * Description:       Give your WordPress site a performance boost — effortlessly convert and serve modern image formats (WebP & AVIF) for faster pages and better SEO.
 * Tags:              image optimization, webp, avif, image converter, performance, speed, seo, image compression, next-gen images, WordPress optimization, media, convert images, image formats, web performance
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Youssef Bouhlal
 * Author URI:        pro.youssef.bouhlal@gmail.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       webpify
 * Domain Path:       /languages
 *
 * @package           Webpify
 */

namespace Webpify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
const VERSION             = '1.0.0';
const PLUGIN_FILE         = __FILE__;
const PLUGIN_REQUIREMENTS = array(
	'php_version' => '7.4',
	'wp_version'  => '6.8',
);

/**
 * Autoload packages.
 */
$webpify_autoloader = __DIR__ . '/vendor/autoload.php';

if ( ! is_readable( $webpify_autoloader ) ) {
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html_e( 'WebPify: Composer autoload file not found. Please run `composer install`.', 'webpify' ); ?></p>
			</div>
			<?php
		}
	);

	return;
}

require $webpify_autoloader;

Main::bootstrap();
