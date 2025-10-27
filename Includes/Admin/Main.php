<?php
/**
 * Handle admin hooks.
 *
 * @class       Admin
 * @version     1.0.0
 * @package     Webpify/Admin/
 */

namespace Webpify\Admin;

use Webpify\Admin\Settings;
use Webpify\Admin\RewriteRules;
use Webpify\Admin\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin main class
 */
final class Main {

	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	public static function hooks() {

		Settings::hooks();
		RewriteRules::hooks();
		Media::hooks();
	}
}
