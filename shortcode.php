<?php
add_shortcode('ae-map', function($atts) {
	wp_enqueue_style('ae-css', plugins_url('css/main.css', __FILE__), false, '1');
	return
	'<div id="ae_map_container" style="' . (!empty($atts['style']) ? $atts['style'] : '') . '">' .
	'<div id="ae_map"></div>' .
	'<div id="ae_checkin"><a href="javascript:void(null)">Aqu&iacute; estoy</a></div>' .
	'</div>' .
	'<script>aeSettings(' . json_encode(array(
		'nonce' => wp_create_nonce('wp_rest'),
		'base_url' => get_rest_url(),
	)) . ');</script>';
});