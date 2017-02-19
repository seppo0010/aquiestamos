<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Aquiestamos
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/aquiestamos.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

$core_dir = rtrim( getenv( 'WP_CORE_DIR' ) ?: '/tmp/wordpress', '/' );
$plugin_dir = dirname( dirname( __FILE__ ) );
$plugin_name = basename( $plugin_dir );
$target_path = $core_dir . '/wp-content/plugins/' . $plugin_name;
if ( file_exists( $target_path ) ) {
	unlink( $target_path );
}
symlink( $plugin_dir, $target_path );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
