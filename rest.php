<?php
defined('ABSPATH') or die('');

add_action('rest_api_init', function () {
	$controller = new WP_REST_Checking_Controller();
	$controller->register_routes();
});

class WP_REST_Checkin_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'ae/v1';
		$this->rest_base = 'checkin';
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	=> array( $this, 'get_checkins' ),
			),
		) );
	}

	function get_checkins() {
		return [];
	}
}