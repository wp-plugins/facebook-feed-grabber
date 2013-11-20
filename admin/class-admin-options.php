<?php 
/**
 * Everything for our options page.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.4
 */

// Get our cache class
include_once FFG_PATH .'/class-cache.php';

/**
 * Class containing everything for our options page.
 * 
 * Class that hooks in, displays and validats the ffg options page.
 * 
 * @since 0.5
 * 
 * @return void 
 */
class ffg_admin_options extends ffg_admin_base 
{

	/**
	 * The settings group name.
	 * 
	 * @access protected
	 * @var string Setting group.
	 */
	protected $option_group = 'facebook-feed-grabber';

	/**
	 * The page title.
	 * 
	 * @access protected
	 * @var string The page title.
	 */
	protected $page_title = 'Facebook Feed Grabber Options';

	/**
	 * Menu title.
	 * 
	 * @access protected
	 * @var string The menu title.
	 */
	protected $menu_title = 'Facebook Feed Grabber';
	
	/**
	 * The URL to a list of Facebook's localization choices.
	 * 
	 * @access protected
	 * @var string URL to Facebook's localaization choices.
	 */
	protected $locale_xml = 'https://www.facebook.com/translations/FacebookLocales.xml';

	/**
	 * WP's yes.png.
	 * 
	 * @access protected
	 * @var string WP's yes.png.
	 */
	protected $yes_icon = '/wp-admin/images/yes.png';
	
	/**
	 * WP's no.png.
	 * 
	 * @access protected
	 * @var string HTML to display WP's no.png.
	 */
	protected $no_icon = '/wp-admin/images/no.png';


	/**
	 * HTML to display WP's yes.png. Set by __construct().
	 * 
	 * @access protected
	 * @var string HTML to display WP's yes.png.
	 */
	protected $yes_img = null;
	
	/**
	 * HTML to display WP's no.png. Set by __construct().
	 * 
	 * @access protected
	 * @var string HTML to display WP's no.png.
	 */
	protected $no_img = null;

	/**
	 * Currect FaceBook user (If they're logged in).
	 * 
	 * @var array Array contain user info.
	 */
	public $fb_user = false;

	/**
	 * If we have valid app credentials.
	 * 
	 * @var array Array contain App info.
	 */
	public $app = false;


	/**
	 * Gets this class ready.
	 * 
	 * Get's the plugin options and sets some other class variables.
	 * 
	 * @return void 
	 */
	function __construct(  ) {

		// Get the options.
		$this->options = ffg_base::get_options('ffg_options');

		if ( ! ffg_cache::cache_folder() ) {
			$this->options['cache_feed'] = 0;
			
			// Tell wp of the error (wp 3+)
			if ( function_exists('add_settings_error') )
				add_settings_error( 'ffg_cache_folder', 'cache-folder', __('We were unable to create directory '. $this->defaults['cache_folder'] .' which would be used for caching the feed to reduce page load time. Check to see if it\'s parent directory writable by the server?') );
		}


		if ( isset($_GET['page']) && $_GET['page'] == 'ffg_options' ) {
			// Verify the app credentials.
			// $this->app =  $this->verify_app_cred($this->options['app_id'], $this->options['secret']);

			// print_r($this->verify_app_cred($this->options['app_id'], $this->options['secret'])); exit;

			// if ( $this->app )
				// $this->fb_user = $this->get_user();

			// print_r($this->fb_user); exit;
				
		}

		// Add our menus.
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		// Hook in our settings.
		add_action('admin_init', array($this, 'settings_api_init'));

		// Hook in our settings.
		add_action('admin_init', array($this, 'enqueue'));

		// Hook in verify app credentials ajax
		add_action('wp_ajax_ffg_verif_app_cred', array($this, 'ajax_verif_app_cred'));

		// Hook in verify app credentials ajax
		add_action('wp_ajax_ffg_select_feed', array($this, 'ajax_select_feed'));

		// Set icons.
		$this->set_icons();

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
		
		$this->page = add_options_page(__($this->page_title), __($this->menu_title), 'manage_options', 'ffg_options', array($this, 'options_page'));
	}


	/**
	 * Set icons HTML
	 * 
	 * Set icons HTML.
	 * 
	 * @since 0.9.0
	 * 
	 * @return void 
	 */
	public function set_icons(  )
	{
		$this->yes_img = '<img alt="" class="icon" src="'. get_bloginfo('wpurl') . $this->yes_icon .'" />';
		$this->no_img = '<img alt="" class="icon" src="'. get_bloginfo('wpurl') . $this->no_icon .'" />';
	}
	

	/**
	 * Hooks in our custom style and javascript.
	 * 
	 * Hooks in our custom style and javascript.
	 * 
	 * @since 0.9.0
	 * 
	 * @return void 
	 */
	public function enqueue(  )
	{
		add_action( "admin_print_scripts-". $this->page, array($this, 'javascript') );
		add_action( "admin_print_styles-". $this->page, array($this, 'css') );
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
		
		register_setting( $this->option_group, 'ffg_options', array($this, 'validate_options') ); 

		// App ID & Secret Section
		add_settings_section('fb_app_info', __('Facebook App ID & Secret'), array($this, 'setting_section'), $this->page);

		// App ID
		add_settings_field(
			'ffg_app_id',
			__('App ID'),
			array($this, 'setting_field_text'),
			$this->page,
			'fb_app_info',
			array(
				'key' => 'app_id',
				'descript' => __('Required for the plugin to work.'),
				'autocomplete' => false,
				)
			);

		// Secret
		add_settings_field(
			'ffg_secret',
			__('App Secret'),
			array($this, 'setting_field_text'),
			$this->page,
			'fb_app_info',
			array(
				'key' => 'secret',
				'descript' => __('Required for the plugin to work.'),
				'autocomplete' => false,
				)
			);

		// Verify App Id & Secret
		add_settings_field(
			'ffg_verify', 
			__('Verify App Id & Secret'), 
			array($this, 'verify_button'), 
			$this->page, 
			'fb_app_info'
			);

		// Default Feed Settings
		add_settings_section(
			'default_feed', 
			__('Default Feed'), 
			array($this, 'setting_section'), 
			$this->page
			);

		// Default Feed ID
		add_settings_field(
			'ffg_default_feed',
			__('Default Feed'),
			array($this, 'default_feed_field'),
			$this->page,
			'default_feed'
			);

		// Number of Entries
		add_settings_field(
			'ffg_num_entries',
			__('Number of Entries'),
			array($this, 'setting_field_text'),
			$this->page,
			'default_feed',
			array(
				'key' => 'num_entries',
				'descript' => __('The default number of entries to display.<br /> If empty or set to 0, posts will not be limited.'),
				)
			);

		// Show Title
		add_settings_field(
			'ffg_show_title',
			__('Show Title'),
			array($this, 'setting_field_checkbox'),
			$this->page,
			'default_feed',
			array(
				'key' => 'show_title',
				'descript' => __("Show the feed title."),
				)
			);

		// Limit to Posts from Feed
		add_settings_field(
			'ffg_limit',
			__('Limit to Posts From Feed'),
			array($this, 'setting_field_checkbox'),
			$this->page,
			'default_feed',
			array(
				'key' => 'limit',
				'descript' => __("When checked the posts displayed will be limited to those posted by the page who's feed is being retrieved."),
				)
			);

		// Show Thumbnails
		add_settings_field(
			'ffg_show_thumbnails',
			__('Show Thumbnails'),
			array($this, 'setting_field_checkbox'),
			$this->page, 
			'default_feed',
			array(
				'key' => 'show_thumbnails',
				'descript' => __("Show thumbnails for post if there is one."),
				)
			);

		// Misc Settings Section
		add_settings_section(
			'misc_settings',
			__('Misc Settings'),
			array($this, 'setting_section'),
			$this->page
			);

		// Cache Feed
		add_settings_field(
			'ffg_cache_feed',
			__('Cache Feed'),
			array($this, 'cache_feed_select'),
			$this->page,
			'misc_settings'
			);

		// Localization
		add_settings_field(
			'ffg_locale',
			__('Localization'),
			array($this, 'locale_select'), 
			$this->page,
			'misc_settings'
			);

		// Style Sheet
		add_settings_field(
			'ffg_style_sheet',
			__('Style Sheet'),
			array($this, 'style_sheet_radio'),
			$this->page,
			'misc_settings');

		// Proxy URL
		add_settings_field(
			'ffg_proxy_url',
			__('Proxy URL'),
			array($this, 'proxy_url_field'),
			$this->page,
			'misc_settings');

		// Delete Options
		add_settings_field(
			'delete_options',
			__('Delete Options on Deactivation'),
			array($this, 'setting_field_checkbox'),
			$this->page,
			'misc_settings',
			array(
				'key' => 'delete_options',
				'descript' => __("When checked this plugin's settings will be deleted on deactivation."),
				)
			);
			
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

		 // Register our javascript.
		 wp_register_script( 'ffg_options', $plugin_url .'/js/ffg-options.js', array( 'backbone', 'jquery'), null, false );

		// Include our scripts.
		wp_enqueue_script( 'ffg_options' );

		// We need to feed some stuff to our script
		// This allows us to pass PHP variables to the Javascript code. We can pass multiple vars in the array.
		wp_localize_script( 'ffg_options', 'ffgOptions', array(
			'wpurl' => get_bloginfo('wpurl'),
			'nonce_app_cred' => wp_create_nonce('ffg_verif_app_cred'),
			'nonce_select_feed' => wp_create_nonce('ffg_select_feed'),
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
			.field-container {
				background-color: rgba(0,0,0,0.1);
				border-radius: 3px;
				float: left;
				margin-right: 4px;
			}
			.fieldMeta {
				font-style: italic;
				padding: 1px 5px;
			}
			.icon {
				margin: 0px 6px 2px 5px;
				vertical-align: middle;
			}
		</style>
		<?php
	}
	
	
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
	function setting_section( $section ) {
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
	 * A setting text field.
	 * 
	 * Display a generic setting text field. Expects a argument 
	 * 'key' to be passed with the option key.
	 * 
	 * @since 0.9.0
	 * 
	 * @return void Echos it's output.
	 */
	public function setting_field_text( $args )
	{

		if ( empty($args['key']) )
			return false;

		$defaults = array(
			'autocomplete' => true,
			'descript' => null,
			'id' => null,
			'value' => $this->options[$args['key']],
			);

		$viewData = array_merge($defaults, $args);

		echo MVCview::render('setting-field-text.php', $viewData);
	}
		

	/**
	 * A setting checkbox.
	 * 
	 * Display a generic setting checkbox.
	 * 
	 * @since 0.9.0
	 * 
	 * @return void Echos it's output.
	 */
	public function setting_field_checkbox( $args )
	{

		if ( empty($args['key']) )
			return false;

		$defaults = array(
			'id' => null,
			'descript' => null,
			'value' => $this->options[$args['key']],
			);

		$viewData = array_merge($defaults, $args);

		echo MVCview::render('setting-field-checkbox.php', $viewData);
	}


	/**
	 * Verify Credentials
	 * 
	 * Verify Credentials
	 * 
	 * @since 0.9.0
	 * 
	 * @return void 
	 */
	function verify_button() {
		$this->app =  $this->verify_app_cred($this->options['app_id'], $this->options['secret']);

		// If we already have valid FB App credentials
		if ( ! $this->app ) {
			$appMeta = array(
				'name' => __('Invalid App ID or Secret'),
				'icon' => $this->no_icon,
				);
		} else {
			$appMeta = $this->app;
			$appMeta['icon'] = $this->yes_icon;
		}

		$viewData = array(
			'appMeta' => json_encode($appMeta),
			'value' => __('Verify App Credentials'),
			);

		echo MVCview::render('verify-button.php', $viewData);

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
		if ( empty($this->options['default_feed']) ) {
				$feed_name = null;
		} else {


			// Verify the feed id.
			$feedMeta = $this->verify_feed($this->options['default_feed']);

			// If there were no results.
			if ( ! $feedMeta )
				$feedMeta = array();

			else
				$feedMeta['feedType'] = ( isset($feedMeta['category']) ) ? __('Page') : __('User');
			
		}

		$viewData = array(
			'descript' => __('The ID, username or URL of the feed you wish to display.'),
			'feed_name' => $feed_name,
			'feedMeta' => json_encode($feedMeta),
			'key' => 'default_feed',
			'value' => $this->options['default_feed'],
			);

		echo MVCview::render('default-feed-field.php', $viewData);

	}
	// End default_feed_field()
	
	
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
		
		// Open the connection
	    $localeCon = curl_init( $this->locale_xml );
		
	    // Return the output from the cURL session rather than displaying in the browser.
	    curl_setopt($localeCon, CURLOPT_RETURNTRANSFER, 1);

	    //Execute the session, returning the results to $locale
	    $locales = curl_exec($localeCon);
		
	    // Close the connection.
	    curl_close($localeCon);
	
		$locales = simplexml_load_string($locales);
		$options = array();

		foreach( $locales as $locale ) {
			$value = (string) $locale->codes->code->standard->representation ;
			$options[$value] = $locale->englishName;
		}

		$viewData = array(
			'descript' => __('Select a language.'),
			'error' => null,
			'key' => 'locale',
			'options' => $options,
			'selected' => $this->options['locale'],
			);

		echo MVCview::render('setting-field-selectbox.php', $viewData);

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

		$viewData = array(
			'descript' => __('If your server connects to the internet via a proxy, set the URL. Otherwise leave blank.'),
			'key' => 'proxy_url',
			'value' => $this->options['proxy_url'],
			);

		echo MVCview::render('proxy-url-field.php', $viewData);

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
		if ( ! ffg_cache::cache_folder() ) {
			$nocache = true;
			$this->options['cache_feed'] = 0;
		} else 
			$nocache = false;

		$options = array(
			0  => __("Don't Cache"),
			5  => __('5 Minute'),
			10 => __('10 Minute'),
			15 => __('15 Minute'),
			30 => __('30 Minute'),
			45 => __('45 Minute'),
			60 => __('60 Minute'),
			);

		if ( $nocache == true ) {
			$error = __('Facebook Feed Grabber will be unable to cache feeds because the location "'. $this->options['cache_folder'] .'" does not appear to be a writable directory.');

		} else
			$error = null;

		$viewData = array(
			'descript' => __('How long to cache feeds before refreshing them.'),
			'error' => $error,
			'feed_name' => $feed_name,
			'key' => 'cache_feed',
			'options' => $options,
			'selected' => $this->options['cache_feed'],
			);

		echo MVCview::render('setting-field-selectbox.php', $viewData);
			
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

		$viewData = array(
			'key' => 'style_sheet',
			'value' => $this->options['style_sheet'],
			);

		echo MVCview::render('style-sheet-radio.php', $viewData);

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
	 * Select a feed via AJAX
	 * 
	 * Select a feed that the current FB user is an admin of.
	 * 
	 * @since 0.9
	 * 
	 * @return void Echos it's results
	 */
	function ajax_select_feed() {

		if ( !current_user_can('manage_options') ) {
			echo __('You do not have sufficient permissions to access this page.');
			die();
		}

		check_ajax_referer( 'ffg_select_feed', 'secure' );

		// Load the facebook SDK.
		$this->load_sdk();
		
		// Initiate the Facebook class.
		$facebook = $this->authenticate();

		// Get User ID
		$user = $facebook->getUser();

		if ( $user )
			$url = $facebook->getLogoutUrl();
		else
			$url = $facebook->getLoginUrl();

		echo $url;

		die();
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

		/*
		 * Facebook App ID & Secret
		 */

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

		/*
		 * Default Feed Settings
		 */
		if ( ctype_digit(trim($input['default_feed'])) !== false ) {

			// Verify the feed id.
			$input['default_feed'] = $this->verify_feed(trim($input['default_feed']));

			if ( ! $input['default_feed'] ) {
				$input['default_feed'] = null;

				add_settings_error( 'ffg_default_feed', 'default_feed', __('The ID you provided for the Default Feed does not seem to be valid.') );
			} else
				$input['default_feed'] = $input['default_feed']['id'];

		} else if ( preg_match('{^((https?|ftp)://[-a-zA-Z0-9+&@#/%?=~_|!:,.;]*[a-zA-Z0-9+&@#/%=~_|])$}', trim($input['default_feed'])) ) {

			// We have a feed URL so get the feed ID.
			$input['default_feed'] = $this->id_from_url(trim($input['default_feed']));

			if ( ! $input['default_feed'] ) {
				$input['default_feed'] = null;

				add_settings_error( 'ffg_default_feed', 'default_feed', __('The URL you provided for the Default Feed does not seem to be valid.') );
			}

		} else if ( preg_match('{^[a-zA-Z0-9.]+$}', trim($input['default_feed'])) ) {

			// We have a username so get the feed ID.
			$input['default_feed'] = $this->id_from_username(trim($input['default_feed']));

			if ( ! $input['default_feed'] ) {
				$input['default_feed'] = null;

				add_settings_error( 'ffg_default_feed', 'default_feed', __('The username you provided for the Default Feed does not seem to be valid.') );
			}

		} else
			$input['default_feed'] = null;

		$input['num_entries'] = intval($input['num_entries']);
		$input['show_title'] = ( isset($input['show_title']) ) ? 1 : 0;
		$input['limit'] = ( isset($input['limit']) ) ? 1 : 0;
		$input['show_thumbnails'] = ( isset($input['show_thumbnails']) ) ? 1 : 0;

		// Misc Settigns
		$input['locale'] = trim($input['locale']);
		$input['proxy_url'] = trim($input['proxy_url']);
		$input['cache_feed'] = intval($input['cache_feed']);
		
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


} // End class ffg_admin_options

new ffg_admin_options; 
?>