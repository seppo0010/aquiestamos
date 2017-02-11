<?php
add_action('admin_init', function() {
	add_option('ae_google_maps_key', '');
	register_setting('ae_options', 'ae_google_maps_key');
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
	</table>
	<?php submit_button(); ?>
	</form>
	</div><?php
	});
});