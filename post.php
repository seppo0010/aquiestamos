<?php
/**
 * Aqui estamos plugin post types
 *
 * @package Aqui estamos
 * @version 1
 */

defined( 'ABSPATH' ) or die( '' );

add_action( 'init', function () {
	ae_init_db();

	register_post_type('ae_checkin',
		array(
			'capabilities' => array(
				'create_posts' => 'create_checkins',
				'publish_posts' => 'publish_checkins',
			),
			'labels' => array(
				'name' => __( 'Checkins' ),
				'singular_name' => __( 'Checkin' ),
			),
			'public' => true,
			'has_archive' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'rest_controller_class' => 'WP_REST_Checkin_Controller',
			'supports' => array(
				'editor',
				'author',
				'description',
			),
		)
	);
});
