<?php
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
		register_setting('ae_options', $setting['name']);
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
	<td><input type="checkbox" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="1" <?php echo get_option($setting['name']) ? 'checked=""' : ''; ?> /></td>
	<?php } elseif (isset($setting['type']) && $setting['type'] === 'textarea') { ?>
	<td><textarea id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>"><?php echo htmlentities(get_option($setting['name']), ENT_QUOTES); ?></textarea></td>
	<?php } else { ?>
	<td><input type="text" id="<?php echo $setting['name']; ?>" name="<?php echo $setting['name']; ?>" value="<?php echo htmlentities(get_option($setting['name']), ENT_QUOTES); ?>" /></td>
	<?php } ?>
	</tr>
	<?php } ?>
	</table>
	<?php submit_button(); ?>
	</form>
	</div><?php
	});
});