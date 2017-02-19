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

	public function test_create_item() {
		foreach ( self::$users_id as $user_id ) {
			wp_set_current_user( $user_id );
			$request = new WP_REST_Request( 'POST', '/ae/v1/checkin' );
			$request->set_body_params( wp_parse_args( array(
				'lat' => -12,
				'lon' => 13,
			) ) );
			$response = $this->server->dispatch( $request );
			$this->assertEquals( 201, $response->get_status() );
		}
	}
	public function test_update_item() {}
	public function test_delete_item() {}
	public function test_prepare_item() {}
	public function test_get_item_schema() {}
}