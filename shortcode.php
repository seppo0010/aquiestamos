<?php
/**
 * Aqui estamos plugin shortcodes
 *
 * @package Aqui estamos
 * @version 1
 */

defined( 'ABSPATH' ) or die( '' );

add_shortcode('ae-map', function( $atts ) {
	$marker = array(
		'url' => get_option( 'ae_marker_url' ),
		'width' => (int) get_option( 'ae_marker_width' ),
		'height' => (int) get_option( 'ae_marker_height' ),
		'vertexX' => (int) get_option( 'ae_marker_vertexX' ),
		'vertexY' => (int) get_option( 'ae_marker_vertexY' ),
	);
	if ( empty( $marker['url'] ) || empty( $marker['width'] ) || empty( $marker['height'] ) ) {
		$marker = null;
	}
	wp_enqueue_style( 'ae-css', plugins_url( 'css/main.css', __FILE__ ), false, '1' );
	return
	'<div id="ae_map_container" style="' . ( ! empty( $atts['style'] ) ? $atts['style'] : '') . '">' .
	'<div id="ae_map"></div>' .
	'<div id="ae_checkin">' . do_shortcode( ae_get_option( 'ae_checkin_text' ) ) . '</div>' .
	'<div id="ae_login">' . do_shortcode( ae_get_option( 'ae_login_text' ) ) . '</div>' .
	'<div id="ae_thanks">' . do_shortcode( ae_get_option( 'ae_thanks_text' ) ) . '</div>' .
	'</div>' .
	'<script>aeSettings(' . json_encode(array(
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'base_url' => get_rest_url(),
		'loggedin' => ! ! wp_get_current_user()->ID,
		'marker' => $marker,
		'styles' => json_decode( get_option( 'ae_map_styles' ) ),
		'cluster_options' => json_decode( ae_get_option( 'ae_cluster_options' ) ),
		'checkin_html' => ae_get_option( 'ae_checkin_html' ),
	)) . ');</script>';
});
