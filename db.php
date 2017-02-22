<?php
/**
 * Aqui estamos plugin database schema and accessor
 *
 * @package Aqui estamos
 * @version 1
 */

defined( 'ABSPATH' ) or die( '' );

$ae_db_version = 4;

/**
 * Installs or updates aqui estamos plugin.
 */
function ae_install() {
	global $wpdb;
	global $ae_db_version;

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $wpdb->ae_checkin (
		`id` bigint(9) NOT NULL AUTO_INCREMENT,
		`post_id` bigint(20) UNSIGNED NOT NULL,
		`latitude` decimal(9,6) NOT NULL,
		`longitude` decimal(9,6) NOT NULL,
		KEY {$wpdb->ae_checkin}_latitude (latitude),
		KEY {$wpdb->ae_checkin}_longitude (longitude),
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'ae_db_version', $ae_db_version );

	foreach ( array( 'editor', 'author', 'contributor', 'subscriber', 'administrator' ) as $role_name ) {
		$role = get_role( $role_name );
		$role->add_cap( 'create_checkins' );
		$role->add_cap( 'publish_checkins' );
	}
}

add_action( 'plugins_loaded', function () {
	global $ae_db_version;
	if ( get_site_option( 'ae_db_version' ) != $ae_db_version ) {
		ae_install();
	}
});

add_action('rest_insert_ae_checkin', function( $post, $request, $a ) {
	global $wpdb;

	$wpdb->query($wpdb->prepare("
		DELETE $wpdb->ae_checkin
		FROM $wpdb->ae_checkin
		JOIN $wpdb->posts ON $wpdb->posts.id = {$wpdb->ae_checkin}.post_id
		WHERE {$wpdb->posts}.post_author = %d
	", $post->post_author));

	$wpdb->insert($wpdb->ae_checkin, array(
		'post_id' => $post->ID,
		'latitude' => (float) $request['lat'],
		'longitude' => (float) $request['lon'],
	));
}, 10, 3);

/**
 * Gets the number of checkins
 */
function ae_count_checkins() {
	global $wpdb;
	$cache_enabled = get_option( 'ae_cache_enabled' );
	$cache_key = '';
	$cache_group = 'ae_count_checkins';
	$cache_duration = 1 * 60;

	if ( $cache_enabled && rand( 0, 100 ) !== 0 ) {
		$cache_value = wp_cache_get( $cache_key, $cache_group );
		if ( $cache_value ) {
			return $cache_value;
		}
	}

	$retval = $wpdb->get_var("
		SELECT
			COUNT(*)
		FROM $wpdb->ae_checkin location
		"
	);

	if ( $cache_enabled ) {
		wp_cache_set( $cache_key, $retval, $cache_group, $cache_duration );
	}

	return $retval;
}

/**
 * Gets all checkins in a range, after a post id.
 *
 * @param [float,float]|null $latitudes  Minimum and maximum latitudes to include.
 * @param [float,float]|null $longitudes Minimum and maximum longitudes to include.
 * @param int|null           $since      Minimum post id to get. This allows polling for new checkins.
 */
function ae_get_posts_in_location( $latitudes, $longitudes, $since = null ) {
	global $wpdb;
	$cache_enabled = get_option( 'ae_cache_enabled' );
	$cache_key = serialize( func_get_args() );
	$cache_group = 'ae_get_posts_in_location';
	$cache_duration = 5 * 60;
	// If we are getting many reads, we want to invalidate one randomly to
	// fetch new data without evicting yet the key, just refreshing it.
	// The keys have a short-lived duration anyway so the latency is limited
	// also, since the $since parameter is used to keep track of the markers,
	// all values will eventually arrive to the user.
	if ( $cache_enabled && rand( 0, 100 ) !== 0 ) {
		$cache_value = wp_cache_get( $cache_key, $cache_group );
		if ( $cache_value ) {
			return $cache_value;
		}
	}

	$results = $wpdb->get_results($wpdb->prepare("
		SELECT
			location.id,
			location.latitude as lat,
			location.longitude as lng,
			posts.post_content
		FROM $wpdb->ae_checkin location
		JOIN $wpdb->posts posts ON location.post_id = posts.ID
		WHERE
			location.latitude BETWEEN %f and %f
		AND
			location.longitude BETWEEN %f and %f
		AND
			location.id > %d
		ORDER BY location.id DESC
		LIMIT 1000
		",
		min( $latitudes ), max( $latitudes ),
		min( $longitudes ), max( $longitudes ),
		intval( $since )
	));

	$retval = array(
		'count' => ae_count_checkins( ),
		'since' => count( $results ) > 0 ? $results[0]->id : (int) $since,
		'results' => array_map(function( $p ) {
			return array(
				'lat' => (float) $p->lat,
				'lng' => (float) $p->lng,
				'post_content' => $p->post_content,
			);
		}, $results),
	);

	if ( $cache_enabled && count( $results ) > 0 ) {
		// we don't want to cache empty results... let's go to the db again.
		wp_cache_set( $cache_key, $retval, $cache_group, $cache_duration );
	}

	return $retval;
}
