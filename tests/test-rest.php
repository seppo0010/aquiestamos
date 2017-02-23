<?php
class AERestTest extends WP_Test_REST_Controller_Testcase {

	static private $users_id = [];
	const PLUGIN = 'aquiestamos/aquiestamos.php';

	public static function wpSetUpBeforeClass( $factory ) {
		if ( ! is_plugin_active( self::PLUGIN ) ) {
			activate_plugin( self::PLUGIN );
			if ( ! is_plugin_active( self::PLUGIN ) ) {
				throw new Exception( 'Unable to activate plugin' );
			}
		}
		foreach ( array( 'editor', 'author', 'contributor', 'subscriber', 'administrator' ) as $role_name ) {
			self::$users_id[] = $factory->user->create( array(
				'role' => $role_name,
			) );
		}
	}

	public static function wpTearDownAfterClass() {
		foreach ( self::$users_id as $user_id ) {
			self::delete_user( $user_id );
		}
	}

	public function test_register_routes() {}
	public function test_context_param() {}
	public function test_get_items() {}
	public function test_get_item() {}

	private function create_item( $user_id, $expect_success ) {
		wp_set_current_user( $user_id );
		$lat = rand( -9000, 9000 ) / 100;
		$lon = rand( -18000, 18000 ) / 100;
		$request = new WP_REST_Request( 'POST', '/ae/v1/checkin' );
		$request->set_body_params( wp_parse_args( array(
			'lat' => $lat,
			'lon' => $lon,
		) ) );
		$response = $this->server->dispatch( $request );
		if ( $expect_success ) {
			$this->assertEquals( 201 , $response->get_status() );
			return [
				'id' => $response->data['id'],
				'lat' => $lat,
				'lon' => $lon,
			];
		} else {
			$this->assertEquals( 401 , $response->get_status() );
		}
	}

	private function _test_create_items() {
		$count = ae_count_checkins( );
		$posts = array_reverse( array_map(function( $user_id ) {
			$data = $this->create_item( $user_id, true );
			return [
				'lat' => $data['lat'],
				'lng' => $data['lon'],
				'post_content' => '',
				'current_user' => $user_id === self::$users_id[0],
			];
		}, self::$users_id ) );
		$this->create_item( 0, false );

		wp_set_current_user( self::$users_id[0] );
		$this->assertEquals(
			ae_get_posts_in_location( [ -90, 90 ], [ -180, 180 ] )['results'],
			$posts
		);
		$this->assertEquals($count + count( $posts ), ae_count_checkins( ) );
	}

	private function _test_create_item_with_content() {
		wp_set_current_user( self::$users_id[0] );
		$lat = rand( -9000, 9000 ) / 100;
		$lon = rand( -18000, 18000 ) / 100;
		$post_content = md5( rand() );
		$request = new WP_REST_Request( 'POST', '/ae/v1/checkin' );
		$request->set_body_params( wp_parse_args( array(
			'lat' => $lat,
			'lon' => $lon,
			'content' => $post_content,
			'status' => 'publish',
		) ) );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201 , $response->get_status() );
		$this->assertContains(
			[
				'lat' => $lat,
				'lng' => $lon,
				'post_content' => $post_content,
			],
			ae_get_posts_in_location( [ -90, 90 ], [ -180, 180 ] )['results']
		);
	}

	private function _test_create_item_removes_previous_location() {
		wp_set_current_user( self::$users_id[0] );
		$request = new WP_REST_Request( 'POST', '/ae/v1/checkin' );
		$request->set_body_params( wp_parse_args( array(
			'lat' => 1,
			'lon' => 1,
		) ) );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201 , $response->get_status() );
		$this->assertContains(
			[
				'lat' => 1,
				'lng' => 1,
				'post_content' => '',
			],
			ae_get_posts_in_location( [ -90, 90 ], [ -180, 180 ] )['results']
		);

		$request = new WP_REST_Request( 'POST', '/ae/v1/checkin' );
		$request->set_body_params( wp_parse_args( array(
			'lat' => 2,
			'lon' => 2,
		) ) );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201 , $response->get_status() );
		$this->assertNotContains(
			[
				'lat' => 1,
				'lng' => 1,
				'post_content' => '',
			],
			ae_get_posts_in_location( [ -90, 90 ], [ -180, 180 ] )['results']
		);
		$this->assertContains(
			[
				'lat' => 2,
				'lng' => 2,
				'post_content' => '',
			],
			ae_get_posts_in_location( [ -90, 90 ], [ -180, 180 ] )['results']
		);
	}

	public function test_create_item() {
		$this->_test_create_items();
		$this->_test_create_item_with_content();
		$this->_test_create_item_removes_previous_location();
	}

	public function test_update_item() {}
	public function test_delete_item() {}
	public function test_prepare_item() {}
	public function test_get_item_schema() {}
}