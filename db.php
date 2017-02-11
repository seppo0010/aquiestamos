<?php
defined('ABSPATH') or die('');

function ae_checkin_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'ae_checkin';
}

$ae_db_version = 1;
function ae_install() {
	global $wpdb;
	global $ae_db_version;

	$table_name = ae_checkin_table_name();

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

function ae_insert_post($post_id, $author, $latitude, $longitude) {
	global $wpdb;
	$table_name = ae_checkin_table_name();
	$posts_table_name = $wpdb->prefix . 'posts';

	$wpdb->query($wpdb->prepare("
		DELETE $table_name
		FROM $table_name
		JOIN $posts_table_name ON $posts_table_name.id = $table_name.post_id
		WHERE $posts_table_name.post_author = %d
	", $author));

	$wpdb->insert($table_name, array(
		'post_id' => $post_id,
		'latitude' => (float)$latitude,
		'longitude' => (float)$longitude,
	));
}

add_action("rest_insert_ae_checkin", function($post, $request, $a) {
	ae_insert_post($post->ID, $post->post_author, $request['lat'], $request['lon']);
}, 10, 3);

function ae_get_posts_in_location($latitudes, $longitudes) {
	global $wpdb;
	$table_name = ae_checkin_table_name();
	return array_map(function($p) {
		return array('lat' => (float)$p->lat, 'lng' => (float)$p->lng);
	}, $wpdb->get_results($wpdb->prepare("
		SELECT
			latitude as lat,
			longitude as lng
		FROM $table_name
		WHERE
			latitude BETWEEN %f and %f
		AND
			longitude BETWEEN %f and %f
		ORDER BY id DESC
		LIMIT 1000
	",
	min($latitudes), max($latitudes),
	min($longitudes), max($longitudes)
	)));
}