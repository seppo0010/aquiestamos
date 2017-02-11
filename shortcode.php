<?php
add_shortcode('nonce-wp-rest', function() {
	return wp_create_nonce('wp_rest');
});