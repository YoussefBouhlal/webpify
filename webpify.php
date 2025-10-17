<?php
/**
 * Plugin Name:       WebPify
 * Plugin URI:        https://github.com/YoussefBouhlal/webpify
 * Description:       Give your WordPress site a performance boost — effortlessly convert and serve modern image formats (WebP & AVIF) for faster pages and better SEO.
 * Tags:              image optimization, webp, avif, image converter, performance, speed, seo, image compression, next-gen images, wordpress optimization, media, convert images, image formats, web performance
 * Version:           1.0.0
 * Requires at least: 6.0.0
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

if ( ! defined('ABSPATH') ) {
	exit;
}

// Include Composer's autoload file.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
} else {
	add_action( 'admin_notices', function() {
		?>
			<div class="notice notice-error">
				<p><?php echo esc_html_e( 'WebPify: Composer autoload file not found. Please run `composer install`.', 'webpify' ); ?></p>
			</div>
		<?php
	} );
	return;
}
