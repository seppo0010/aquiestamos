<?php
class AEDbTest extends WP_UnitTestCase {

	const PLUGIN = 'aquiestamos/aquiestamos.php';

	public static function wpSetUpBeforeClass( $factory ) {
		if ( ! is_plugin_active( self::PLUGIN ) ) {
			activate_plugin( self::PLUGIN );
			if ( ! is_plugin_active( self::PLUGIN ) ) {
				throw new Exception( 'Unable to activate plugin' );
			}
		}
	}

	private function get_describe() {
		global $wpdb;
		return array_map(
			function( $e ) { return (array) $e; },
			$wpdb->get_results( "DESC {$wpdb->ae_checkin}" )
		);
	}
	public function test_update_db_schema() {
		global $wpdb;
		$expected = [
			'Field' => 'post_id',
			'Type' => 'bigint(20) unsigned',
			'Null' => 'NO',
			'Key' => '',
			'Default' => null,
			'Extra' => '',
		];
		$this->assertContains( $expected, $this->get_describe() );
		$wpdb->query( "ALTER TABLE {$wpdb->ae_checkin} DROP COLUMN post_id" );
		$this->assertNotContains( $expected, $this->get_describe() );
		ae_install();
		$this->assertContains( $expected, $this->get_describe() );
	}
}
