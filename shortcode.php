<?php
add_shortcode('ae-map', function($atts) {
	return '<div id="ae_map" style="' . (!empty($atts['style']) ? $atts['style'] : '') . '"></div><script>aeSetNonce(' . json_encode(wp_create_nonce('wp_rest')) . ');</script>';
});