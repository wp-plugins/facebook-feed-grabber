<?php
/**
 * Everything for our options page.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.4
 */

/**
 * Class containing everything for our options page.
 * 
 * Class that hooks in, displays and validats the ffg options page.
 * 
 * @since 0.5
 * 
 * @return void 
 */
class ffg_admin extends ffg_base 
{
	
	/**
	 * The URL to a list of Facebook's localization choices.
	 * 
	 * @access protected
	 * @var string URL to Facebook's localaization choices.
	 */
	protected $locale_xml = 'https://www.facebook.com/translations/FacebookLocales.xml';

	/**
	 * HTML to display WP's yes.png. Set by __construct().
	 * 
	 * @access protected
	 * @var string HTML to display WP's yes.png.
	 */
	protected $yes_img = '/wp-admin/images/yes.png';
	
	/**
	 * HTML to display WP's no.png. Set by __construct().
	 * 
	 * @access protected
	 * @var string HTML to display WP's no.png.
	 */
	protected $no_img = '/wp-admin/images/no.png';

	/**
	 * Gets this class ready.
	 * 
	 * Get's the plugin options and sets some other class variables.
	 * 
	 * @return void 
	 */
	function __construct(  ) {

		$this->options = ffg_base::get_options('ffg_options');

		$this->yes_img = '<img alt="" class="icon" src="'. get_bloginfo('wpurl') . $this->yes_img .'" />';
		$this->no_img = '<img alt="" class="icon" src="'. get_bloginfo('wpurl') . $this->no_img .'" />';

	}
	// End __construct()
	
	
	/**
	 * Add our menu and hook in our javascript and styles.
	 * 
	 * Add our menu and hook in our javascript and styles.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function add_menu() {
		
		$page = add_options_page('Facebook Feed Grabber Options', 'Facebook Feed Grabber', 'manage_options', __file__, array($this, 'options_page'));

		add_action( "admin_print_scripts-". $page, array($this, 'javascript') );
		add_action( "admin_print_styles-". $page, array($this, 'css') );
	}
	
	
	/**
	 * Register our settings.
	 * 
	 * Register our settings with WordPress.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function settings_api_init() {
		
		
		register_setting( 'facebook-feed-grabber', 'ffg_options', array($this, 'validate_options') ); 

		// App ID & Secret
		add_settings_section('fb_app_info', __('Facebook App ID & Secret'), array($this, 'setting_section_callback_function'), __file__);

		add_settings_field('ffg_app_id', __('App ID'), array($this, 'app_id_field'), __file__, 'fb_app_info');
		add_settings_field('ffg_secret', __('App Secret'), array($this, 'secret_field'), __FILE__, 'fb_app_info');
		add_settings_field('ffg_verify', __('Verify App Id & Secret'), array($this, 'verify_button'), __FILE__, 'fb_app_info');

		// Default Feed Settings
		add_settings_section('default_feed', __('Default Feed'), array($this, 'setting_section_callback_function'), __file__);

		add_settings_field('ffg_default_feed', __('Default Feed'), array($this, 'default_feed_field'), __file__, 'default_feed');
		add_settings_field('ffg_num_entries', __('Number of Entries'), array($this, 'num_entries_field'), __file__, 'default_feed');
		add_settings_field('ffg_show_title', __('Show Title'), array($this, 'show_title_checkbox'), __FILE__, 'default_feed');
		add_settings_field('ffg_limit', __('Limit to Posts From Feed'), array($this, 'limit_checkbox'), __FILE__, 'default_feed');
		add_settings_field('ffg_show_thumbnails', __('Show Thumbnails'), array($this, 'show_thumbnails_checkbox'), __FILE__, 'default_feed' );

		// Misc Settings
		add_settings_section('misc_settings', __('Misc Settings'), array($this, 'setting_section_callback_function'), __file__);
		add_settings_field('ffg_cache_feed', __('Cache Feed'), array($this, 'cache_feed_select'), __FILE__, 'misc_settings');
		add_settings_field('ffg_locale', __('Localization'), array($this, 'locale_select'), __FILE__, 'misc_settings');
		add_settings_field('ffg_style_sheet', __('Style Sheet'), array($this, 'style_sheet_radio'), __FILE__, 'misc_settings');
		add_settings_field('ffg_proxy_url', __('Proxy URL'), array($this, 'proxy_url_field'), __file__, 'misc_settings');
		add_settings_field('delete_options', __('Delete Options on Deactivation'), array($this, 'delete_options_checkbox'), __FILE__, 'misc_settings');
			
	}
	// End settings_api_init()
	
	
	/**
	 * Hook in javascript for the options page.
	 * 
	 * Hook in javascript for the options page.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function javascript() {
		// Url to plugin directory
		 $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );

		// Include our scripts.
		wp_enqueue_script('ffg_options', $plugin_url .'/js/ffg-options.js', array('jquery'));

		// We need to feed some stuff to our script
		// This allows us to pass PHP variables to the Javascript code. We can pass multiple vars in the array.
		wp_localize_script( 'ffg_options', 'ffg_options', array(
			'wpurl' => get_bloginfo('wpurl'),
			'nonce' => wp_create_nonce('ffg_verif_app_cred')
			));

	}
	
	
	/**
	 * Echos the custom css for the options page.
	 * 
	 * Echos the custom css for the options page.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function css() {
		?>
		<style type="text/css" media="screen">
			.icon {
				margin: 0px 6px 2px 5px;
				vertical-align: middle;
			}
		</style>
		<?php
	}
	// End css()
	
	
	/**
	 * Echo the text to display for our setting's sections.
	 * 
	 * Echo the text to display for our setting's sections.
	 * 
	 * @since 0.5
	 * 
	 * @param array $section Section parameters.
	 * 
	 * @return void 
	 */
	function setting_section_callback_function( $section ) {
		switch ( $section['id'] ) {
			case 'fb_app_info':
				_e("<p>You will need a facebook App ID and Secret key. To get them you must register as a developer and create an application, which you can do from their <a href='https://developers.facebook.com/setup'>Create an App</a> page.</p>");
				break;

			case 'default_feed':
				_e("<p>The default settings for used for displaying a feed. These can be overridden when displaying a feed from a widget or shortcode.</p>");
				break;

			case 'misc_settings':
				_e("<p>Some Miscellaneous settings.</p>\n");
				break;

		}
	}
	
	
	/**
	 * The options page.
	 * 
	 * The options page.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
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
	
		
	/**
	 * App ID field
	 * 
	 * App ID field
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function app_id_field() {
		?>
		<input type="text" name="ffg_options[app_id]" value="<?php echo esc_attr($this->options['app_id']); ?>" class="regular-text" id="ffg-app-id" autocomplete="off" />
		<span class="description"><?php _e('Required for the plugin to work.') ?></span>
		<?php
	}
	
	
	/**
	 * Number of entries field
	 * 
	 * Number of entries field
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function secret_field() {
		?>
		<input type="text" name="ffg_options[secret]" value="<?php echo esc_attr($this->options['secret']); ?>" class="regular-text" id="ffg-secret" autocomplete="off" />
		<span class="description"><?php _e('Required for the plugin to work.') ?></span>
		<?php
	}


	/**
	 * Number of entries field
	 * 
	 * Number of entries field
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function verify_button() {
		$verify =  $this->verify_app_cred($this->options['app_id'], $this->options['secret']);

		if ( ! $verify )
			$descript = $this->no_img . __(' Invalid App ID or Secret');
		else
			$descript = $this->yes_img . __(' Name: ') . $verify['name'];
		?>
		<input type="button" name="ffg_verify" value="<?php _e('Verify App Credentials') ?>" class="button" id="ffg-verify" />
		<span id="ffg_verify_d" class="description"><?php echo $descript; ?></span>
		<?php
	}
	
	
	/**
	 * Default Feed field.
	 * 
	 * Default Feed field.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function default_feed_field() {
		?>
		<input type="text" name="ffg_options[default_feed]" value="<?php echo esc_attr($this->options['default_feed']); ?>" class="regular-text" /> 
		<span class="description"><?php _e('The id of the default feed to be grabbed by fb_feed().') ?></span>
		<?php
	}
	// End default_feed_field()


	/**
	 * Number of entries field
	 * 
	 * Number of entries field
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function num_entries_field() {
		?>
		<input type="text" name="ffg_options[num_entries]" value="<?php echo esc_attr($this->options['num_entries']); ?>" class="regular-text" /> 
		<span class="description"><?php _e('The default number of entries to display.<br /> If empty or set to 0, posts will not be limited.') ?></span>
		<?php
	}
	// End num_entries_field()
	
	
	/**
	 * Show locale select box.
	 * 
	 * Show locale select box.
	 * 
	 * @since 0.8.2
	 * 
	 * @return void 
	 */
	function locale_select() {
		
		if ( !function_exists('curl_init') ) {
			echo "<span class='error'>To use this feature you must have the PHP CURL extension installed.</span>\n";
			return 0;
		}
		
	    $localeCon = curl_init( $this->locale_xml );
		
	    // Return the output from the cURL session rather than displaying in the browser.
	    curl_setopt($localeCon, CURLOPT_RETURNTRANSFER, 1);

	    //Execute the session, returning the results to $definition, and close.
	    $local_xml = curl_exec($localeCon);
	
	    curl_close($localeCon);
	
		$locale = simplexml_load_string($local_xml);
		?>
		<select name="ffg_options[locale]">
			<?php
			foreach ($locale as $value) {
				if ( $this->options['locale'] == $value->codes->code->standard->representation )
					$select = " selected='selected'";
				else
					$select = null;
					
				echo "<option value='". $value->codes->code->standard->representation ."'$select>". $value->englishName ."</option>\n";
			}
			?>
		</select>
		<span class="description"><?php _e('Select a language.') ?></span>
		
		<?php
	}
	
	
	/**
	 * Proxy URL field
	 * 
	 * Proxy URL field
	 * 
	 * @since 0.7.1
	 * 
	 * @return void 
	 */
	function proxy_url_field() {
		$hide = ' style="display: none;"';
		?>
		<div id="proxyDisabled"<?php echo !empty($this->options['proxy_url']) ? $hide : null; ?>>
			<a href="#enableProxy"><?php _e('Enable Proxy'); ?></a> - <span class="description"><?php _e('Click to enable if you\'re server is behind a proxy.') ?></span>
		</div>
		<div id="proxyEnabled"<?php echo empty($this->options['proxy_url']) ? $hide : null; ?>>
			<input type="text" name="ffg_options[proxy_url]" value="<?php echo esc_attr($this->options['proxy_url']); ?>" class="regular-text" />
			<span class="description"><?php _e('If your server connects to the internet via a proxy, set the URL. Otherwise leave blank.') ?></span>
		</div>
	<?php
	}
	
	
	/**
	 * Show cache feed select box.
	 * 
	 * Show cache feed select box.
	 * 
	 * @since 0.7
	 * 
	 * @return void 
	 */
	function cache_feed_select() {
		
		// See if there is a cache folder and that it's writable. 
		if ( ! wp_mkdir_p($this->options['cache_folder']) ) {
			$nocache = true;
			$this->options['cache_feed'] = 0;
		} else 
			$nocache = false;
			
		?>
		<select name="ffg_options[cache_feed]">
			<option value="0"<?php echo $this->options['cache_feed'] == 0 ? ' selected="selected"' : ''; ?>><?php _e("Don't Cache") ?></option>
			<option value="5"<?php echo $this->options['cache_feed'] == 5 ? ' selected="selected"' : ''; ?>><?php _e('5 Minute') ?></option>
			<option value="10"<?php echo $this->options['cache_feed'] == 10 ? ' selected="selected"' : ''; ?>><?php _e( '10 Minute') ?></option>
			<option value="15"<?php echo $this->options['cache_feed'] == 15 ? ' selected="selected"' : ''; ?>><?php _e('15 Minute') ?></option>
			<option value="30"<?php echo $this->options['cache_feed'] == 30 ? ' selected="selected"' : ''; ?>><?php _e('30 Minute') ?></option>
			<option value="45"<?php echo $this->options['cache_feed'] == 45 ? ' selected="selected"' : ''; ?>><?php _e('45 Minute') ?></option>
			<option value="60"<?php echo $this->options['cache_feed'] == 60 ? ' selected="selected"' : ''; ?>><?php _e('60 Minute') ?></option>
			
		</select>
		<span class="description"><?php _e('How long to cache feeds before refreshing them.') ?></span>
		<?php
		if ( $nocache == true ) {
			echo '<span class="error-message">';
			_e('Facebook Feed Grabber will be unable to cache feeds because the location "'. $this->options['cache_folder'] .'" does not appear to be a writable directory.');
			echo "</span>\n";
		}
			
	}
	
	
	/**
	 * Show feed Title checkbox.
	 * 
	 * Show feed Title checkbox.
	 * 
	 * @since 0.6
	 * 
	 * @return void 
	 */
	function show_title_checkbox() {
		$checked =  $this->options['show_title'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e("Show the feed title.") ?></span></legend>
			<label for="show_title"><input <?php echo $checked; ?> type="checkbox" value="1" id="show_title" name="ffg_options[show_title]"> <?php _e("Show the feed title.") ?></label>
		</fieldset>
		<?php
	}
	

	/**
	 * Limit to posts from feed checkbox.
	 * 
	 * Limit to posts from feed checkbox.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function limit_checkbox() {
		$checked =  $this->options['limit'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e('Limit to posts from feed.') ?></span></legend>
			<label for="limit"><input <?php echo $checked; ?> type="checkbox" value="1" id="limit" name="ffg_options[limit]"> <?php _e("When checked the posts displayed will be limited to those posted by the page who's feed is being retrieved.") ?></label>
		</fieldset>
		<?php
	}


	/**
	 * Show thumbnails when available.
	 * 
	 * Show thumbnails when available.
	 * 
	 * @since 0.7
	 * 
	 * @return void 
	 */
	function show_thumbnails_checkbox() {
		$checked =  $this->options['show_thumbnails'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e("Show thumbnails for post if there is one.") ?></span></legend>
			<label for="show_thumbnails"><input <?php echo $checked; ?> type="checkbox" value="1" id="show_thumbnails" name="ffg_options[show_thumbnails]"> <?php _e("Show thumbnails for post if there is one.") ?></label>
		</fieldset>
		<?php
	}


	/**
	 * Use default style sheet checkbox.
	 * 
	 * Use default style sheet checkbox.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function style_sheet_radio() {
		?>
		<fieldset>
			<legend class="screen-reader-text"><span>Date Format</span></legend>
			<label><input type="radio"<?php echo ( !isset($this->options['style_sheet']) || $this->options['style_sheet'] == 'style.css' ) ? 'checked="checked"' : null; ?> value="style.css" name="ffg_options[style_sheet]"> <span>Use Default Style Sheet</span> <span class="description"><?php _e('Font color and size will be based on the theme\'s style rules for the current context.') ?></span></label><br />
			<label><input type="radio"<?php echo (  $this->options['style_sheet'] == 'style-2.css' ) ? 'checked="checked"' : null; ?> value="style-2.css" name="ffg_options[style_sheet]"> <span>Use Secondary Style Sheet</span> <span class="description"><?php _e('This one is more specific in it\'s declarations than the default. It requires the feed container to have an id of "fb-feed".') ?></span></label><br />
			<label><input type="radio"<?php echo ( $this->options['style_sheet'] == false ) ? 'checked="checked"' : null; ?> value="0" name="ffg_options[style_sheet]"> <span>I'll Define My Own Styles.</span></label><br />
		</fieldset>
		<?php
	}
	
	
	/**
	 * Delete options on deactivation checkbox.
	 * 
	 * Delete options on deactivation checkbox.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function delete_options_checkbox() {
		$checked =  $this->options['delete_options'] ? ' checked="checked" ' : null;
		?>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e('Delete options on deactivation?') ?></span></legend>
			<label for="delete_options"><input <?php echo $checked; ?> type="checkbox" value="1" id="delete_options" name="ffg_options[delete_options]"> <?php _e("When checked this plugin's settings will be deleted on deactivation.") ?></label>
		</fieldset>
		<?php
	}
		
	
	/**
	 * Used to verify App Id & Secret from our options page.
	 * 
	 * Used to verify App Id & Secret from our options page.
	 * 
	 * @since 0.9
	 * 
	 * @return mixed description
	 */
	function ajax_verif_app_cred() {

		if ( !current_user_can('manage_options') ) {
			echo __('You do not have sufficient permissions to access this page.');
			die();
		}

		check_ajax_referer( 'ffg_verif_app_cred', 'secure' );

		// Verify App ID
		if ( !preg_match('/[0-9]{10,}/', trim($_POST['app_id'])) ) {
			echo $this->no_img;
			_e(" Invalid App Id");
			die();
		} else
			$app_id= trim($_POST['app_id']);

		// Verify Secret
		if ( !preg_match('/[0-9a-z]{27,37}/i', trim($_POST['secret'])) ) {
			echo $this->no_img;
			_e(" Invalid Secret");
			die();
		} else
			$secret = trim($_POST['secret']);
	
		$verify = $this->verify_app_cred($app_id, $secret);		

		if ( ! $verify )
			echo $this->no_img . __(' Invalid App ID or Secret');
		else
			echo $this->yes_img . __(' Name: ') . $verify['name'];

		die(); // this is required to return a proper result
	}
	
	
	/**
	 * Validate our options.
	 * 
	 * Validate our options.
	 * 
	 * @since 0.5
	 * 
	 * @return void 
	 */
	function validate_options( $input ) {

		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		
		// Wordpress should be handling the nonce.

		// Validate App Id
		if ( !preg_match('/^[0-9]{10,}$/', trim($input['app_id'])) ) {

			$input['app_id'] = null;

			// Tell wp of the error
			add_settings_error( 'ffg_app_id', 'app-id', __('You do not appear to have provided a valid App Id for your Facebook Application.') );

		} else
			$input['app_id'] = trim($input['app_id']);

		// Validate Secret
		if ( !preg_match('/^[0-9a-z]{27,37}$/i', trim($input['secret'])) ) {

			$input['secret'] = null;		

			// Tell wp of the error
			add_settings_error( 'ffg_secret', 'secret', __('You do not appear to have proivided a valid Secret for your Facebook Application.') );

		} else
			$input['secret'] = trim($input['secret']);

		// Misc Settigns
		$input['default_feed'] = ctype_digit($input['default_feed']) !== false ? $input['default_feed'] : null;
		$input['num_entries'] = intval($input['num_entries']);
		$input['locale'] = trim($input['locale']);
		$input['proxy_url'] = trim($input['proxy_url']);
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

} // End class ffg_admin

// Hook stuff in.
$ffg_admin = new ffg_admin();
add_action('admin_menu', array($ffg_admin, 'add_menu'));
add_action('wp_ajax_ffg_verif_app_cred', array($ffg_admin, 'ajax_verif_app_cred'));
add_action('admin_init', array($ffg_admin, 'settings_api_init'));
?>