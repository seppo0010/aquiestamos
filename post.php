<?php
defined('ABSPATH') or die('');

function create_post_type() {
	register_post_type('ae_checkin',
		array(
			'labels' => array(
				'name' => __('Checkins'),
				'singular_name' => __('Checkin')
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
				'author',
				'description',
			),
		)
	);
}
add_action('init', 'create_post_type');