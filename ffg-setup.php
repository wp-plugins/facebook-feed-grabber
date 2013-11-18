<?php 
/**
 * Settup things for the plugin.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.8.3
 */


/**
 * Class containing plugin setup and deactivation stuff.
 */
class ffg_setup {
	

	/**
	 * Current plugin version
	 * 
	 * @access protected
	 * @var string ffg version.
	 */
	protected $version = '0.8.4';
	
	/**
	 * For the defaults. (Look in $this->__construct()).
	 * 
	 * @access public
	 * @var array The default ffg options.
	 */
	public $defaults = false;
		
	/**
	 * Set to true when the Facebook SDK is loaded by this plugin.
	 * 
	 * @access private
	 * @var boolean Tells us if the SDK has been loaded.
	 */
	private $sdk_loaded = false;
	

	/**
	 * Set the default settings.
	 * 
	 * @return void
	 */
	function __construct(  ) {
		
				
		// The defaults
		$this->defaults = array(
			// Facebook App ID & Secret
			'app_id' => null,
			'secret' => null,
		
			// Misc Settings
			'default_feed' => null,
			'show_title' => 1,
			'cache_feed' => 5,
			'cache_folder' => WP_CONTENT_DIR. '/uploads/cache/',
			'num_entries' => 3,
			'locale' => 'en_US',
			'proxy_url' => null,
			'limit' => 1,
			'show_thumbnails' => 1,
			'style_sheet' => 'style.css',
			'delete_options' => 0,
		
			// Current Version
			'version' => $this->version
		);
		
		if ( ! wp_mkdir_p($this->defaults['cache_folder']) ) {
			$this->defaults['cache_feed'] = 0;
			
			// Tell wp of the error (wp 3+)
			if ( function_exists('add_settings_error') )
				add_settings_error( 'ffg_cache_folder', 'cache-folder', __('We were unable to create directory '. $this->defaults['cache_folder'] .' which would be used for caching the feed to reduce page load time. Check to see if it\'s parent directory writable by the server?') );
		}
		
	}
	
	
	/**
	 * Define default ffg options.
	 * 
	 * If the pluggin is newly installed define the default 
	 * options and if the plugin has been updated then add 
	 * any new options.
	 * 
	 * @return void 
	 */
	function activate() {
	
		// Get stored plugin options
		$options = get_option('ffg_options');
				
		// If there aren't already settings defined then set the defaults.
	    if( !is_array($options) ) {
		
			$options = $this->defaults;
		
		// If the defined settings aren't for this version add any new settings.
		} else if ( $options['version'] != $this->version) {
			
			$options = array_merge($this->defaults, $options);
			
		}
		
		$options['version'] = $this->version;
	
		update_option('ffg_options', $options);
	}

	
	/**
	 * Delete ffg options.
	 * 
	 * Deletes the ffg options on deactivation if 'restore_defaults' is true.
	 * 
	 * @return void
	 */
	function deactivate(  ) {
	
		$options = get_option('ffg_options');
	
		if ( $options['delete_options'] )
			delete_option('ffg_options');
	
	}
	
	
	/**
	 * Starts a session if it isn't already done.
	 * 
	 * Checks to see if a session has been started and if not 
	 * initiate one.
	 * 
	 * @return void 
	 */
	function sessionStart() {
	    if( !session_id() )
			session_start();
	}
	
	
	/**
	 * Destroy the session.
	 * 
	 * Destroy the session.
	 * 
	 * @return void 
	 */
	function sessionDestroy() {
	    session_destroy();
	}
	
	
	/**
	 * Load the facebook SDK.
	 * 
	 * Loads the Facebook SDK if this plugin hasn't loaded it 
	 * and there isn't already a Facebook class.
	 * 
	 * @return void 
	 */
	function load_sdk() {
		
		if ( $this->sdk_loaded )
			return true;
		
		// 
		// Get the facebook sdk
		if ( ! class_exists('Facebook') )
			require_once 'facebook-sdk/facebook.php';
		
		$this->sdk_loaded = true;
		
		return true;
	}

	
	/**
	 * Get's the plugin options.
	 * 
	 * Get's the plugin options. This has been set up in a way that
	 * lets us add another options panel in the future if necessary.
	 *
	 * @param string $set Optional. Name of the options panel to get.
	 * @return Array Array of plugin options.
	 **/
	public function get_options( $set = 'ffg_options' ) {
		static $options = array();

		if ( array_key_exists($set, $options) )
			return $options[$set];

		$optionSet = get_option( $set );
		$optionSet = array_merge($this->defaults, $optionSet); 

		// See if we need to upgrade the options.
		if ( version_compare($optionSet['version'], $this->version) )
			$this->upgrade($set, $optionSet);

		$options[$set] = $optionSet;

		return $optionSet;

	} // End function get_options
	
	
	/**
	 * Upgrade the options.
	 *
	 * @param string $set Name of the options panel to upgrade.
	 * @param array $options The options to be upgraded.
	 * @return void
	 **/
	function upgrade( $set, $options ) {
		
		$options['version'] = $this->version;
		
		update_option($set, $options);
	} // End function upgrade
	
	
}// End class ffg_setup


// On activation or deactivation
$ffg_setup = new ffg_setup();
register_activation_hook(__FILE__, array($ffg_setup, 'activate'));
register_deactivation_hook(__FILE__, array($ffg_setup, 'deactivate'));


// The Facebook PHP SDK uses sessions. Lets hook in session start and stop functionality.
add_action('init', array($ffg_setup, 'sessionStart'), 1);
add_action('wp_logout', array($ffg_setup, 'sessionDestroy'));
add_action('wp_login', array($ffg_setup, 'sessionDestroy'));

?>