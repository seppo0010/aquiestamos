<?php
/**
 * @package Aqui estamos
 * @version 1
 */
/*
Plugin Name: Aqui estamos
Description: Check in map
Author: Sebastian Waisbrot
Version: 1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'rest_api_init', function () {
	$controller = new WP_REST_Test_Controller();
	$controller->register_routes();
} );
class WP_REST_Test_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'test';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'   => WP_REST_Server::READABLE,
				'callback'  => array( $this, 'get_tests' ),
			),
		) );
	}

	function get_tests() { return 'OK'; }
}