<?php
/**
 * Aqui estamos plugin
 *
 * @package aquiestamos
 * @version 1
 */

/*
Plugin Name: aquiestamos
Description: Check in map
Author: Sebastian Waisbrot
Version: 1
*/

defined( 'ABSPATH' ) or die( '' );

require dirname( __FILE__ ) . '/admin.php';
require dirname( __FILE__ ) . '/db.php';
require dirname( __FILE__ ) . '/js.php';
require dirname( __FILE__ ) . '/post.php';
require dirname( __FILE__ ) . '/class-wp-rest-checkin-controller.php';
require dirname( __FILE__ ) . '/shortcode.php';

register_activation_hook( 'aquiestamos/aquiestamos.php', 'ae_install' );
