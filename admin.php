<?php
function ae_get_option($option) {
	$r = get_option($option);
	if ($r) {
		return $r;
	} else {
		return array_values(array_filter(ae_settings(), function($v) use ($option) {
			return $v['name'] === $option;
		}))[0]['default'];
	}
}

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
			'sanitize_callback' => function($value) {
				if ($value !== '' && json_decode($value) === NULL) {
					add_settings_error('ae_map_styles', 'ae_map_styles-invalid_json', 'JSON is invalid');
					return '';
				}
				return $value;
			}
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
			'label' => 'Cluster Image Prefix<br />Base url for cluster images. E.g.:<br />/images/myimage<br />will use /images/myimage1.png, /images/myimage2.png, ... /images/myimage5.png',
			'name' => 'ae_cluster_prefix',
			'default' => 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
		),
		array(
			'label' => 'Cluster Image Suffix',
			'name' => 'ae_cluster_suffix',
			'default' => 'png',
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
	foreach (ae_settings() as $setting) {
		add_option($setting['name'], '');
		register_setting('ae_options', $setting['name'], isset($setting['sanitize_callback']) ? $setting['sanitize_callback'] : NULL);
	}
});

add_action('admin_menu', function() {
	add_options_page('Aqui Estamos Options', 'Aqui Estamos', 'manage_options', 'aqui-estamos', function() {
	?>
	<div>
	<?php screen_icon(); ?>
	<h2>Aqui estamos</h2>
	<form method="post" action="options.php">
	<?php settings_fields('ae_options'); ?>
	<table>
	<?php foreach (ae_settings() as $setting) { ?>
	<tr valign="top">
	<th scope="row"><label for="<?php echo $setting['name']; ?>"><?php echo $setting['label']; ?></label></th>
	<?php if (isset($setting['type']) && $setting['type'] === 'checkbox') { ?>
	<td><input type="checkbox" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="1" <?php echo ae_get_option($setting['name']) ? 'checked=""' : ''; ?> /></td>
	<?php } elseif (isset($setting['type']) && $setting['type'] === 'textarea') { ?>
	<td><textarea id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" cols="80" rows="6"><?php echo htmlentities(ae_get_option($setting['name']), ENT_QUOTES); ?></textarea></td>
	<?php } else { ?>
	<td><input type="text" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="<?php echo htmlentities(ae_get_option($setting['name']), ENT_QUOTES); ?>" /></td>
	<?php } ?>
	</tr>
	<?php } ?>
	</table>
	<?php submit_button(); ?>
	</form>
	</div><?php
	});
});