<?php
add_action('wp_enqueue_scripts', 'ae_enqueue_scripts');

function ae_enqueue_scripts() {
	$googlemapskey = get_option('ae_google_maps_key');
	wp_enqueue_script('ae_script_js_map', plugins_url('js/map.js', __FILE__), array('jquery'));
	wp_enqueue_script('ae_script_marker_clusterer', "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js");
	wp_enqueue_script('ae_script_api_js', "https://maps.googleapis.com/maps/api/js?key=$googlemapskey&callback=aeMapReady", array('ae_script_js_map'));
}