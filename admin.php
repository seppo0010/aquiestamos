<?php
/**
 * Aqui estamos plugin admin
 *
 * @package aquiestamos
 * @version 1
 */

defined( 'ABSPATH' ) or die( '' );

/**
 * Gets an ae option. This can be changed by the site admin or have a plugin
 * default.
 *
 * @param string $option Name of the option.
 */
function ae_get_option( $option ) {
	$r = get_option( $option );
	if ( $r ) {
		return $r;
	} else {
		return array_values(array_filter(ae_settings(), function( $v ) use ( $option ) {
			return $v['name'] === $option;
		}))[0]['default'];
	}
}

/**
 * Lists all aquiestamos settings.
 */
function ae_settings() {
	return array(
		array(
			'label' => 'Google Maps key',
			'name' => 'ae_google_maps_key',
		),
		array(
			'label' => 'Enable cache?<br />Only if you install a <a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Cache_Plugins" target="_blank">cache plugin</a>',
			'name' => 'ae_cache_enabled',
			'type' => 'checkbox',
		),
		array(
			'label' => 'Map JSON styles<br />See <a href="https://developers.google.com/maps/documentation/javascript/styling">reference</a>',
			'name' => 'ae_map_styles',
			'type' => 'textarea',
			'sanitize_callback' => function( $value ) {
				if ( '' !== $value && json_decode( $value ) === null ) {
					add_settings_error( 'ae_map_styles', 'ae_map_styles-invalid_json', 'JSON is invalid' );
					return '';
				}
				return $value;
			},
		),
		array(
			'label' => 'Checkin text<br />Use the property data-checkin to indicate clickable target',
			'name' => 'ae_checkin_text',
			'type' => 'textarea',
			'default' => '<a data-checkin href="#">Check in</a>',
		),
		array(
			'label' => 'Login text<br />Use [TheChamp-Login] to include super-socializer login methods',
			'name' => 'ae_login_text',
			'type' => 'textarea',
			'default' => '[TheChamp-Login]',
		),
		array(
			'label' => 'Thanks text<br />Use [TheChamp-Sharing] to prompt for super-socializer sharing',
			'name' => 'ae_thanks_text',
			'type' => 'textarea',
			'default' => 'Thanks for checkin in.<br />[TheChamp-Sharing]',
		),
		array(
			'label' => 'Checkin HTML<br />Use [ae-checkin-text] for user submitted content',
			'name' => 'ae_checkin_html',
			'type' => 'textarea',
			'default' => '',
		),
		array(
			'label' => 'Marker Cluster options<br />JSON opt_options object to send to <a href="http://htmlpreview.github.io/?https://github.com/googlemaps/v3-utility-library/blob/master/markerclusterer/docs/reference.html" target="_blank">MarkerClusterer</a>',
			'name' => 'ae_cluster_options',
			'type' => 'textarea',
			'default' => '{"styles":[
				{"width":53,"height":53,"textColor":"black","url":"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m1.png"},
				{"width":56,"height":56,"textColor":"black","url":"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m2.png"},
				{"width":66,"height":66,"textColor":"black","url":"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m3.png"},
				{"width":78,"height":78,"textColor":"black","url":"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m4.png"},
				{"width":90,"height":90,"textColor":"black","url":"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m5.png"}
			]}',
			'sanitize_callback' => function( $value ) {
				if ( '' !== $value && json_decode( $value ) === null ) {
					add_settings_error( 'ae_cluster_options', 'ae_cluster_options-invalid_json', 'JSON is invalid' );
					return '';
				}
				return $value;
			},
		),
		array(
			'label' => 'Marker URL',
			'name' => 'ae_marker_url',
		),
		array(
			'label' => 'Marker width',
			'name' => 'ae_marker_width',
		),
		array(
			'label' => 'Marker height',
			'name' => 'ae_marker_height',
		),
		array(
			'label' => 'Marker vertex x',
			'name' => 'ae_marker_vertexX',
		),
		array(
			'label' => 'Marker vertex y',
			'name' => 'ae_marker_vertexY',
		),
	);
}

add_action('admin_init', function() {
	foreach ( ae_settings() as $setting ) {
		add_option( $setting['name'], '' );
		register_setting( 'ae_options', $setting['name'], isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : null );
	}
});

add_action('admin_menu', function() {
	add_options_page('Aqui Estamos Options', 'Aqui Estamos', 'manage_options', 'aquiestamos', function() {
		?>
	<div>
	<?php screen_icon(); ?>
	<h2>Aqui estamos</h2>
	<form method="post" action="options.php">
	<?php settings_fields( 'ae_options' ); ?>
	<table>
	<?php foreach ( ae_settings() as $setting ) { ?>
	<tr valign="top">
	<th scope="row"><label for="<?php echo $setting['name']; ?>"><?php echo $setting['label']; ?></label></th>
	<?php if ( isset( $setting['type'] ) && 'checkbox' === $setting['type']  ) { ?>
	<td><input type="checkbox" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="1" <?php echo ae_get_option( $setting['name'] ) ? 'checked=""' : ''; ?> /></td>
	<?php } elseif ( isset( $setting['type'] ) && 'textarea' === $setting['type'] ) { ?>
	<td><textarea id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" cols="80" rows="6"><?php echo htmlentities( ae_get_option( $setting['name'] ), ENT_QUOTES ); ?></textarea></td>
	<?php } else { ?>
	<td><input type="text" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="<?php echo htmlentities( ae_get_option( $setting['name'] ), ENT_QUOTES ); ?>" /></td>
	<?php } ?>
	</tr>
	<?php } ?>
	</table>
	<?php submit_button(); ?>
	</form>
	</div><?php
	});
});
