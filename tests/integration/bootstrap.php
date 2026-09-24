<?php
/**
 * Bootstrap the WordPress integration test suite.
 *
 * @package Webpify
 */

$webpify_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $webpify_tests_dir ) {
	$webpify_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );
}

if ( ! file_exists( $webpify_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find the WordPress test library. Run bin/install-wp-tests.sh first.' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once $webpify_tests_dir . '/includes/functions.php';

/**
 * Load the plugin after WordPress has initialized must-use plugins.
 */
function webpify_manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/webpify.php';
}

tests_add_filter( 'muplugins_loaded', 'webpify_manually_load_plugin' );

require $webpify_tests_dir . '/includes/bootstrap.php';
