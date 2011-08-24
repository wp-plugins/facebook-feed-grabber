<?php

/* - - - - - -
	
	Add our menu and hook in our javascript.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_add_menu() {
	
	$page = add_options_page('Facebook Feed Grabber Options', 'Facebook Feed Grabber', 'manage_options', __file__, 'ffg_options');

	add_action( "admin_print_scripts-". $page, 'ffg_options_js' );
	add_action( "admin_print_styles-". $page, 'ffg_options_css' );
}
add_action('admin_menu', 'ffg_add_menu');

/* - - - - - -
	
	Javascript for the options page…
	
		-hooked in from the ffg_add_menu() function
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_options_js() {
	// Url to plugin directory
	 $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
	
	// Include our scripts.
	wp_enqueue_script('jquery');
	wp_enqueue_script('ffg_options', $plugin_url .'/options.js', array('jquery'));
	
	// We need to feed some stuff to our script
	// This allows us to pass PHP variables to the Javascript code. We can pass multiple vars in the array.
	wp_localize_script( 'ffg_options', 'ffg_options', array(
		'wpurl' => get_bloginfo('wpurl'),
		'nonce' => wp_create_nonce('ffg_verif_app_cred')
		));
	
}

/* - - - - - -
	
	Javascript for the options page…
	
		-hooked in from the ffg_add_menu() function
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_options_css() {
	?>
	<style type="text/css" media="screen">
		.icon {
			margin: 0px 6px 0px 5px;
			vertical-align: middle;
		}
	</style>
	<?php
}

/* - - - - - -
	
	Used to verify App Id & Secret from our options page.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
add_action('wp_ajax_ffg_verif_app_cred', 'ffg_verif_app_cred');
function ffg_verif_app_cred() {
	// global $wpdb; // this is how you get access to the database
	
	if ( !current_user_can('manage_options') ) {
		echo __('You do not have sufficient permissions to access this page.');
		die();
	}
	
	check_ajax_referer( 'ffg_verif_app_cred', 'secure' );
	
	// Verify App ID
	if ( !preg_match('/[0-9]{10,}/', trim($_POST['app_id'])) ) {
		echo " Invalid App Id";
		die();
	} else
		$result['app_id'] = trim($_POST['app_id']);
	
	// Verify Secret
	if ( !preg_match('/[0-9a-z]{27,37}/i', trim($_POST['secret'])) ) {
		echo "Invalid Secret";
		die();
	} else
		$result['secret'] = trim($_POST['secret']);
		
	// Try to make the connection
	$facebook = new Facebook(array(
		  'appId'  => $result['app_id'],
		  'secret' => $result['secret']
		));
	
	// If it couldn't connect…
	if ( !$facebook ) {
		echo 'Invalid';
		die();
	}
	
	// This call will always work since we are fetching public data.
	$app = $facebook->api('/'. $result['app_id'] .'?date_format=U');
	
	if ( $app ) {
		echo 'Name: '. $app['name'];
	} else {
		echo 'Invalid';
	}

	die(); // this is required to return a proper result
}

// 
// Register our settings section and fields
function ffg_settings_api_init() {
	register_setting( 'facebook-feed-grabber', 'ffg_options', 'ffg_validate_options' ); 
	
	// App ID & Secret
	add_settings_section('fb_app_info', 'Facebook App ID & Secret', 'ffg_setting_section_callback_function', __file__);
	add_settings_field('ffg_app_id', 'App ID', 'ffg_app_id', __file__, 'fb_app_info');
	add_settings_field('ffg_secret', 'App Secret', 'ffg_secret', __FILE__, 'fb_app_info');
	add_settings_field('ffg_verify', 'Verify App Id & Secret', 'ffg_verify', __FILE__, 'fb_app_info');

	// Misc
	add_settings_section('misc_settings', 'Misc Settings', 'ffg_setting_section_callback_function', __file__);
	add_settings_field('ffg_num_entries', 'Number of Entries', 'ffg_num_entries', __file__, 'misc_settings');
	add_settings_field('restore_defaults', 'Restore Defaults Upon Reactivation?', 'ffg_restore_defaults', __FILE__, 'misc_settings');
}// End ffg_settings_api_init()
add_action('admin_init', 'ffg_settings_api_init');

// The text to display for settings section
function ffg_setting_section_callback_function( $section ) {
	switch ( $section['id'] ) {
		case 'fb_app_info':
			echo "<p>You will need a facebook App ID and Secret key. To get them you must register as a developer and create an application which you can do from their <a href='https://developers.facebook.com/setup'>Create an App</a> page.</p>";
			break;
		
		case 'misc_settings':
			echo "<p>Some Miscellaneous settings.</p>\n";
			break;
		
	}
}

// 
// App ID field
function ffg_app_id() {
	$options = get_option('ffg_options');
	?>
	<input type="text" name="ffg_options[app_id]" value="<?php echo $options['app_id']; ?>" class="regular-text" id="ffg-app-id" autocomplete="off" />
	<span class="description">Required for the plugin to work…</span>
	<?php
}

// 
// Number of entries field
function ffg_secret() {
	$options = get_option('ffg_options');
	?>
	<input type="text" name="ffg_options[secret]" value="<?php echo $options['secret']; ?>" class="regular-text" id="ffg-secret" autocomplete="off" />
	<span class="description">Required for the plugin to work</span>
	<?php
}

// 
// Number of entries field
function ffg_verify() {
	$options = get_option('ffg_options');
	?>
	<input type="button" name="ffg_verify" value="Verify App Credentials" class="button" id="ffg-verify" />
	<span id="ffg_verify_d" class="description"></span>
	<?php
}

// 
// Number of entries field
function ffg_num_entries() {
	$options = get_option('ffg_options');
	?>
	<input type="text" name="ffg_options[num_entries]" value="<?php echo $options['num_entries']; ?>" class="regular-text" /> 
	<span class="description">The default number of entries to display. If empty or set to 0, posts will not be limited.</span>
	<?php
}

// CHECKBOX - Name: plugin_options[chkbox1]
function ffg_restore_defaults() {
	$options = get_option('ffg_options');
	$checked =  $options['restore_defaults'] ? ' checked="checked" ' : null;
	?>
	<fieldset>
		<legend class="screen-reader-text"><span>Membership</span></legend>
		<label for="restore_defaults"><input <?php echo $checked; ?> type="checkbox" value="1" id="restore_defaults" name="ffg_options[restore_defaults]"> When check this plugins settings will be deleted on deactivation.</label>
	</fieldset>
	<?php
}

// The Options
function ffg_options() {
	if (!current_user_can('manage_options'))
		wp_die( __('You do not have sufficient permissions to access this page.') );
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Facebook Feed Grabber</h2>
		<form action="options.php" method="post">
		<?php settings_fields('facebook-feed-grabber'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>
	<?php
}

function ffg_validate_options( $input ) {
	
	if (!current_user_can('manage_options'))
		wp_die( __('You do not have sufficient permissions to access this page.') );
	
	// Validate App Id
	if ( !preg_match('/^[0-9]{10,}$/', trim($input['app_id'])) ) {
		$input['app_id'] = null;
		add_settings_error( 'ffg_app_id', 'app-id', __('You do not appear to have proivided a valid App Id for your Facebook Application.') );
	} else
		$input['app_id'] = trim($input['app_id']);
	
	// Validate Secret
	if ( !preg_match('/^[0-9a-z]{27,37}$/i', trim($input['secret'])) ) {
		$input['secret'] = null;		
		add_settings_error( 'ffg_secret', 'secret', __('You do not appear to have proivided a valid Secret for your Facebook Application.') );
	} else
		$input['secret'] = trim($input['secret']);
	
	// Misc Settigns
	$input['num_entries'] = intval($input['num_entries']);
	$input['restore_defaults'] = $input['restore_defaults'] ? 1 : 0;
	
	return $input;
}
?>