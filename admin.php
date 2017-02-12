<?php
add_action('admin_init', function() {
	add_option('ae_google_maps_key', '');
	register_setting('ae_options', 'ae_google_maps_key');
	add_option('ae_cache_enabled', '');
	register_setting('ae_options', 'ae_cache_enabled');
});

add_action('admin_menu', function() {
	add_options_page('Aqui Estamos Options', 'Aqui Estamos', 'manage_options', 'aqui-estamos', function() { ?>
	<div>
	<?php screen_icon(); ?>
	<h2>My Plugin Page Title</h2>
	<form method="post" action="options.php">
	<?php settings_fields('ae_options'); ?>
	<table>
	<tr valign="top">
	<th scope="row"><label for="ae_google_maps_key">Google Maps key</label></th>
	<td><input type="text" id="ae_google_maps_key" name="ae_google_maps_key" value="<?php echo get_option('ae_google_maps_key'); ?>" /></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="ae_cache_enabled">Enable cache? Only if you install a <a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Cache_Plugins" target="_blank">cache plugin</a></label></th>
	<td><input type="checkbox" id="ae_cache_enabled" name="ae_cache_enabled" value="1" <?php echo get_option('ae_cache_enabled') ? 'checked=""' : ''; ?> /></td>
	</tr>
	</table>
	<?php submit_button(); ?>
	</form>
	</div><?php
	});
});