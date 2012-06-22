<?php

/* - - - - - -
	
	Class containing everything for our options page.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
class ffg_admin {
	
	// Plugin options set in wordpress
	protected $options = false;
	
	function __construct(  ) {
		global $ffg_setup;
		
		if ( is_array($ffg_setup->defaults) )
			$this->options = array_merge($ffg_setup->defaults, get_option('ffg_options'));
		else
			$this->options = get_option('ffg_options');
	}
	// End __construct()
	
	
	/* - - - - - -
	
		Add our menu and hook in our javascript and styles.
	
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function add_menu() {
		
		$page = add_options_page('Facebook Feed Grabber Options', 'Facebook Feed Grabber', 'manage_options', __file__, array(&$this, 'options_page'));

		add_action( "admin_print_scripts-". $page, array(&$this, 'javascript') );
		add_action( "admin_print_styles-". $page, array(&$this, 'css') );
	}
	// End add_menu()
	
	
	/* - - - - - -
		
		Register our settings.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function settings_api_init() {
		
		
		register_setting( 'facebook-feed-grabber', 'ffg_options', array(&$this, 'validate_options') ); 

		// App ID & Secret
		add_settings_section('fb_app_info', __('Facebook App ID & Secret'), array(&$this, 'setting_section_callback_function'), __file__);
		add_settings_field('ffg_app_id', __('App ID'), array(&$this, 'app_id_field'), __file__, 'fb_app_info');
		add_settings_field('ffg_secret', __('App Secret'), array(&$this, 'secret_field'), __FILE__, 'fb_app_info');
		add_settings_field('ffg_verify', __('Verify App Id & Secret'), array(&$this, 'verify_button'), __FILE__, 'fb_app_info');

		// Misc Settings
		add_settings_section('misc_settings', __('Misc Settings'), array(&$this, 'setting_section_callback_function'), __file__);
		add_settings_field('ffg_default_feed', __('Default Feed'), array(&$this, 'default_feed_field'), __file__, 'misc_settings');
		add_settings_field('ffg_num_entries', __('Number of Entries'), array(&$this, 'num_entries_field'), __file__, 'misc_settings');
		add_settings_field('ffg_cache_feed', __('Cache Feed'), array(&$this, 'cache_feed_select'), __FILE__, 'misc_settings');
		add_settings_field('ffg_show_title', __('Show Title'), array(&$this, 'show_title_checkbox'), __FILE__, 'misc_settings');
		add_settings_field('ffg_limit', __('Limit to Posts From Feed'), array(&$this, 'limit_checkbox'), __FILE__, 'misc_settings');
		add_settings_field('ffg_show_thumbnails', __('Show Thumbnails'), array(&$this, 'show_thumbnails_checkbox'), __FILE__, 'misc_settings' );
		add_settings_field('ffg_style_sheet', __('Styles Sheet'), array(&$this, 'style_sheet_radio'), __FILE__, 'misc_settings');
		add_settings_field('delete_options', __('Delete Options on Deactivation'), array(&$this, 'delete_options_checkbox'), __FILE__, 'misc_settings');
			
	}
	// End settings_api_init()
	
	
	/* - - - - - -

		Javascript for the options page…

			-hooked in from the add_menu() function

	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function javascript() {
		// Url to plugin directory
		 $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

		// Include our scripts.
		wp_enqueue_script('ffg_options', $plugin_url .'/options.js', array('jquery'));

		// We need to feed some stuff to our script
		// This allows us to pass PHP variables to the Javascript code. We can pass multiple vars in the array.
		wp_localize_script( 'ffg_options', 'ffg_options', array(
			'wpurl' => get_bloginfo('wpurl'),
			'nonce' => wp_create_nonce('ffg_verif_app_cred')
			));

	}
	// End javascript()
	
	
	/* - - - - - -

		Javascript for the options page…

			-hooked in from the add_menu() function

	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function css() {
		?>
		<style type="text/css" media="screen">
			.icon {
				margin: 0px 6px 0px 5px;
				vertical-align: middle;
			}
		</style>
		<?php
	}
	// End css()
	
	
	/* - - - - - -
		
		The text to display for our setting's sections.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function setting_section_callback_function( $section ) {
		switch ( $section['id'] ) {
			case 'fb_app_info':
				_e("<p>You will need a facebook App ID and Secret key. To get them you must register as a developer and create an application, which you can do from their <a href='https://developers.facebook.com/setup'>Create an App</a> page.</p>");
				break;

			case 'misc_settings':
				_e("<p>Some Miscellaneous settings.</p>\n");
				break;

		}
	}
	// End setting_section_callback_function
	
	
	/* - - - - - -
		
		The options page.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function options_page() {
		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e('Facebook Feed Grabber') ?></h2>
			<form action="options.php" method="post">
			<?php settings_fields('facebook-feed-grabber'); ?>
			<?php do_settings_sections(__FILE__); ?>
			<p class="submit">
				<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
			</p>
			</form>
		</div>
		<?php
	}
	
		
	/* - - - - - -
		
		App ID field
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function app_id_field() {
		?>
		<input type="text" name="ffg_options[app_id]" value="<?php echo esc_attr($this->options['app_id']); ?>" class="regular-text" id="ffg-app-id" autocomplete="off" />
		<span class="description"><?php _e('Required for the plugin to work.') ?></span>
		<?php
	}
	// End app_id_field()
	
	
	/* - - - - - -
		
		Number of entries field
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function secret_field() {
		?>
		<input type="text" name="ffg_options[secret]" value="<?php echo esc_attr($this->options['secret']); ?>" class="regular-text" id="ffg-secret" autocomplete="off" />
		<span class="description"><?php _e('Required for the plugin to work.') ?></span>
		<?php
	}
	// End secret_field()


	/* - - - - - -
		
		Number of entries field
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function verify_button() {
		?>
		<input type="button" name="ffg_verify" value="<?php _e('Verify App Credentials') ?>" class="button" id="ffg-verify" />
		<span id="ffg_verify_d" class="description"></span>
		<?php
	}
	// End verify_button()
	
	
	/* - - - - - -
		
		Number of entries field
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function default_feed_field() {
		?>
		<input type="text" name="ffg_options[default_feed]" value="<?php echo esc_attr($this->options['default_feed']); ?>" class="regular-text" /> 
		<span class="description"><?php _e('The id of the default feed to be grabbed by fb_feed().') ?></span>
		<?php
	}
	// End default_feed_field()


	/* - - - - - -
		
		Number of entries field
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function num_entries_field() {
		?>
		<input type="text" name="ffg_options[num_entries]" value="<?php echo esc_attr($this->options['num_entries']); ?>" class="regular-text" /> 
		<span class="description"><?php _e('The default number of entries to display.<br /> If empty or set to 0, posts will not be limited.') ?></span>
		<?php
	}
	// End num_entries_field()
	
	
	/* - - - - - -
		
		Show cache feed select box.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function cache_feed_select() {
		
		// See if there is a cache folder and that it's writable. 
		if ( ! wp_mkdir_p($this->options['cache_folder']) ) {
			$nocache = true;
			$this->options['cache_feed'] = 0;
		} else 
			$nocache = false;
			
		?>
		<select name="ffg_options[cache_feed]">
			<option value="0"<?php echo $this->options['cache_feed'] == 0 ? ' selected="selected"' : ''; ?>>False</option>
			<option value="5"<?php echo $this->options['cache_feed'] == 5 ? ' selected="selected"' : ''; ?>>5 Minute</option>
			<option value="10"<?php echo $this->options['cache_feed'] == 10 ? ' selected="selected"' : ''; ?>>10 Minute</option>
			<option value="15"<?php echo $this->options['cache_feed'] == 15 ? ' selected="selected"' : ''; ?>>15 Minute</option>
			<option value="30"<?php echo $this->options['cache_feed'] == 30 ? ' selected="selected"' : ''; ?>>30 Minute</option>
			<option value="45"<?php echo $this->options['cache_feed'] == 45 ? ' selected="selected"' : ''; ?>>45 Minute</option>
			<option value="60"<?php echo $this->options['cache_feed'] == 60 ? ' selected="selected"' : ''; ?>>60 Minute</option>
			
		</select>
		<span class="description"><?php _e('How long to cache feeds before refreshing them.') ?></span>
		<?php
		if ( $nocache == true ) {
			echo '<span class="error-message">';
			_e('Facebook Feed Grabber will be unable to cache feeds because the location "'. $this->options['cache_folder'] .'" does not appear to be a writable directory.');
			echo "</span>\n";
		}
			
	}
	
	/* - - - - - -
		
		Show feed Title.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function show_title_checkbox() {
		$checked =  $this->options['show_title'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e("Show the feed title.") ?></span></legend>
			<label for="show_title"><input <?php echo $checked; ?> type="checkbox" value="1" id="show_title" name="ffg_options[show_title]"> <?php _e("Show the feed title.") ?></label>
		</fieldset>
		<?php
	}
	// End show_title_checkbox()
	

	/* - - - - - -
		
		Limit to posts from feed checkbox.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function limit_checkbox() {
		$checked =  $this->options['limit'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e('Limit to posts from feed.') ?></span></legend>
			<label for="limit"><input <?php echo $checked; ?> type="checkbox" value="1" id="limit" name="ffg_options[limit]"> <?php _e("When checked the posts displayed will be limited to those posted by the page who's feed is being retrieved.") ?></label>
		</fieldset>
		<?php
	}
	// End limit_checkbox()


	/* - - - - - -
		
		Show thumbnails when available.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function show_thumbnails_checkbox() {
		$checked =  $this->options['show_thumbnails'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e("Show thumbnails for post if there is one.") ?></span></legend>
			<label for="show_thumbnails"><input <?php echo $checked; ?> type="checkbox" value="1" id="show_thumbnails" name="ffg_options[show_thumbnails]"> <?php _e("Show thumbnails for post if there is one.") ?></label>
		</fieldset>
		<?php
	}
	// End show_thumbnails_checkbox()


	/* - - - - - -
		
		Use default style sheet checkbox. 
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function style_sheet_radio() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span>Date Format</span></legend>
			<label><input type="radio"<?php echo ( !isset($this->options['style_sheet']) || $this->options['style_sheet'] == 'style.css' ) ? 'checked="checked"' : null; ?> value="style.css" name="ffg_options[style_sheet]"> <span>Use Default Style Sheet</span></label><br />
			<label><input type="radio"<?php echo (  $this->options['style_sheet'] == 'style-2.css' ) ? 'checked="checked"' : null; ?> value="style-2.css" name="ffg_options[style_sheet]"> <span>Use Secondary Style Sheet</span> <span class="description"><?php _e('More specific in it\'s declarations than the default. (Requires container to have an id of "fb-feed".)') ?></span></label><br />
			<label><input type="radio"<?php echo ( $this->options['style_sheet'] == false ) ? 'checked="checked"' : null; ?> value="0" name="ffg_options[style_sheet]"> <span>I'll Define My Own Styles.</span></label><br />
		</fieldset>
		<?php
	}
	// End default_style_checkbox()
	
	
	/* - - - - - -
		
		Delete options on deactivation checkbox.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function delete_options_checkbox() {
		$checked =  $this->options['delete_options'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e('Delete options on deactivation?') ?></span></legend>
			<label for="delete_options"><input <?php echo $checked; ?> type="checkbox" value="1" id="delete_options" name="ffg_options[delete_options]"> <?php _e("When checked this plugin's settings will be deleted on deactivation.") ?></label>
		</fieldset>
		<?php
	}
	// End delete_options_checkbox()
		
	
	/* - - - - - -

		Used to verify App Id & Secret from our options page.

	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function verif_app_cred() {

		if ( !current_user_can('manage_options') ) {
			echo __('You do not have sufficient permissions to access this page.');
			die();
		}

		check_ajax_referer( 'ffg_verif_app_cred', 'secure' );

		// Verify App ID
		if ( !preg_match('/[0-9]{10,}/', trim($_POST['app_id'])) ) {
			echo "e:1-";
			_e(" Invalid App Id");
			die();
		} else
			$result['app_id'] = trim($_POST['app_id']);

		// Verify Secret
		if ( !preg_match('/[0-9a-z]{27,37}/i', trim($_POST['secret'])) ) {
			echo "e:2-";
			_e("Invalid Secret");
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
	// End verify_app_cred()
	
	
	/* - - - - - -
		
		Validate our options.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function validate_options( $input ) {

		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		
		// Wordpress should be handling the nonce.

		// Validate App Id
		if ( !preg_match('/^[0-9]{10,}$/', trim($input['app_id'])) ) {

			$input['app_id'] = null;

			// Tell wp of the error (wp 3+)
			if ( function_exists('add_settings_error') )
				add_settings_error( 'ffg_app_id', 'app-id', __('You do not appear to have provided a valid App Id for your Facebook Application.') );

		} else
			$input['app_id'] = trim($input['app_id']);

		// Validate Secret
		if ( !preg_match('/^[0-9a-z]{27,37}$/i', trim($input['secret'])) ) {

			$input['secret'] = null;		

			// Tell wp of the error (wp 3+)
			if ( function_exists('add_settings_error') )
				add_settings_error( 'ffg_secret', 'secret', __('You do not appear to have proivided a valid Secret for your Facebook Application.') );

		} else
			$input['secret'] = trim($input['secret']);

		// Misc Settigns
		$input['default_feed'] = ctype_digit($input['default_feed']) !== false ? $input['default_feed'] : null;
		$input['num_entries'] = intval($input['num_entries']);
		$input['cache_feed'] = intval($input['cache_feed']);
		$input['show_title'] = ( isset($input['show_title']) ) ? 1 : 0;
		$input['limit'] = ( isset($input['limit']) ) ? 1 : 0;
		$input['show_thumbnails'] = ( isset($input['show_thumbnails']) ) ? 1 : 0;
		
		if ( isset($input['style_sheet']) ) {
			switch ( trim($input['style_sheet']) ) {
				
				case 'style.css':
				case 'style-2.css':
				
					$input['style_sheet'] = trim($input['style_sheet']);
					
					break;
				
				default:
					
					$input['style_sheet'] = 0;
					
					break;
				
			}
		} else
			$input['style_sheet'] = 'style.css';
		// End if isset($input['style_sheet'])
		
		$input['delete_options'] = ( isset($input['delete_options']) ) ? 1 : 0;
		
		$input = array_merge($this->options, $input);
		
		return $input;
	}
	// End validate_options()

}

// Hook stuff in.
$ffg_admin = new ffg_admin();
add_action('admin_menu', array(&$ffg_admin, 'add_menu'));
add_action('wp_ajax_ffg_verif_app_cred', array(&$ffg_admin, 'verif_app_cred'));
add_action('admin_init', array(&$ffg_admin, 'settings_api_init'));
?>