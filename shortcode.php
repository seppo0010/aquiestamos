<?php
add_shortcode('ae-map', function($atts) {
	return '<div id="ae_map" style="' . (!empty($atts['style']) ? $atts['style'] : '') . '"></div><script>aeSettings(' . json_encode(array(
		'nonce' => wp_create_nonce('wp_rest'),
		'base_url' => get_rest_url(),
	)) . ');</script>';
});