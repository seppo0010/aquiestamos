<?php
add_action('admin_init', function() {
	add_option('ae_google_maps_key', '');
	register_setting('ae_options', 'ae_google_maps_key');
	add_option('ae_cache_enabled', '');
	register_setting('ae_options', 'ae_cache_enabled');
});

add_action('admin_menu', function() {
	add_options_page('Aqui Estamos Options', 'Aqui Estamos', 'manage_options', 'aqui-estamos', function() {
	$settings = [
		[
			'label' => 'Google Maps key',
			'name' => 'ae_google_maps_key',
		],
		[
			'label' => 'Enable cache?<br />Only if you install a <a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Cache_Plugins" target="_blank">cache plugin</a>',
			'name' => 'ae_cache_enabled',
			'type' => 'checkbox',
		],
	];
	?>
	<div>
	<?php screen_icon(); ?>
	<h2>Aqui estamos</h2>
	<form method="post" action="options.php">
	<?php settings_fields('ae_options'); ?>
	<table>
	<?php foreach ($settings as $setting) { ?>
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