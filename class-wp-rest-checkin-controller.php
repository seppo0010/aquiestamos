<?php
/**
 * Aqui estamos plugin rest access
 *
 * @package Aqui estamos
 * @version 1
 */

defined( 'ABSPATH' ) or die( '' );

add_action('rest_api_init', function () {
	$controller = new WP_REST_Checkin_Controller();
	$controller->register_routes();
});

/**
 * Rest accessor for checkin objects
 */
class WP_REST_Checkin_Controller extends WP_REST_Posts_Controller {
	/**
	 * Sets base rest properties
	 */
	public function __construct() {
		parent::__construct( 'ae_checkin' );
		$this->namespace = 'ae/v1';
		$this->rest_base = 'checkin';
	}

	/**
	 * Registers create and read checkin routes
	 */
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			array(
				'methods'	 => WP_REST_Server::READABLE,
				'callback'	=> array( $this, 'get_checkins' ),
			),
		));
	}

	/**
	 * Fetches all checkin data for a given request.
	 *
	 * The request may include `latitude` and `longitude` as two elements
	 * arrays of floats to filter a specific location.
	 *
	 * The request may include `since` as an integer representing a minimum
	 * post id. If provided, only checkins after that post will be included.
	 *
	 * @access public
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	function get_checkins( $request ) {
		if (
			isset( $request['latitude'] ) &&
			is_array( $request['latitude'] ) &&
			count( $request['latitude'] ) == 2 &&
			isset( $request['longitude'] ) &&
			is_array( $request['longitude'] ) &&
			count( $request['longitude'] ) == 2
			) {
			$latitudes = array( (float) $request['latitude'][0], (float) $request['latitude'][1] );
			$longitudes = array( (float) $request['longitude'][0], (float) $request['longitude'][1] );
		} else {
			$latitudes = array( -90, 90 );
			$longitudes = array( -180, 180 );
		}
		$since = empty( $request['since'] ) ? null : (int) $request['since'];
		$upto = empty( $request['upto'] ) ? null : (int) $request['upto'];
		return ae_get_posts_in_location( $latitudes, $longitudes, $since, $upto );
	}

	/**
	 * Creates a checkin
	 *
	 * Only logged in users can create a checkin. `lat` and `lon` are required.
	 *
	 * @access public
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item( $request ) {
		foreach ( array(
				'lat' => array( -90, 90 ),
				'lon' => array( -180, 180 ),
			) as $k => $limits ) {
			if ( ! isset( $request[ $k ] ) ) {
				return new WP_Error( 'required_parameter', __( 'Coordinates are required.' ), array(
					'status' => 400,
				) );
			}
			$val = $request[ $k ];
			if ( (float) $val > $limits[1] || (float) $val < $limits[0] ) {
				return new WP_Error( 'invalid_coordinate', __( 'Coordinates provided are not valid.' ), array(
					'status' => 400,
				) );
			}
		}
		return parent::create_item( $request );
	}
}
