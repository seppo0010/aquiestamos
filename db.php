<?php
defined('ABSPATH') or die('');

$ae_db_version = 1;
function ae_install() {
	global $wpdb;
	global $ae_db_version;

	$table_name = $wpdb->prefix . 'ae_checkin';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		`id` bigint(9) NOT NULL AUTO_INCREMENT,
		`post_id` bigint(20) UNSIGNED NOT NULL,
		`latitude` decimal(9,6) NOT NULL,
		`longitude` decimal(9,6) NOT NULL,
		KEY ${table_name}_latitude (latitude),
		KEY ${table_name}_longitude (longitude),
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option('ae_db_version', $ae_db_version);
}
register_activation_hook( __FILE__, 'ae_install' );

function ae_update_db_check() {
    global $ae_db_version;
    if ( get_site_option('ae_db_version') != $ae_db_version) {
        ae_install();
    }
}
add_action('plugins_loaded', 'ae_update_db_check');