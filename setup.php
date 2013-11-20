<?php 
/**
 * Settup things for the plugin.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */


/**
 * Class containing plugin setup and deactivation stuff.
 */
class ffg_setup {
		
	/**
	 * For the defaults. (Look in $this->__construct()).
	 * 
	 * @access public
	 * @var array The default ffg options.
	 */
	public static $defaults = false;
		
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
				
	}

	/**
	 * Get the default values.
	 */
	static public function get_defaults( $set = 'ffg_options' )
	{

		if ( self::$defaults != false ) 
			return self::$defaults[$set];

		// The defaults array.
		$defaults = array(
			'ffg_options' => array(
				// Facebook App ID & Secret
				'app_id' => null,
				'secret' => null,
			
				// Misc Settings
				'default_feed' => null,
				'show_title' => 1,
				'cache_feed' => 5,
				'cache_base' => 'wp-content',
				'cache_folder' => '/uploads/cache/',
				'num_entries' => 3,
				'locale' => 'en_US',
				'proxy_url' => null,
				'limit' => 1,
				'show_thumbnails' => 1,
				'style_sheet' => 'style.css',
				'delete_options' => 0,
			
				// Current Version
				'version' => FFG_VERSION,
			)
		);

		self::$defaults = $defaults;

		return $defaults[$set];
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

		// Display the admin notification
		add_action( 'admin_notices', array( $this, 'activation_notice' ) ) ;

	
		// Get stored plugin options
		$options = get_option('ffg_options');
				
		// If there aren't already settings defined then set the defaults.
	    if( !is_array($options) ) {
		
			$options = $this->defaults;
		
		// If the defined settings aren't for this version add any new settings.
		} else if ( $options['version'] != FFG_VERSION ) {
			
			$options = array_merge($this->defaults, $options);
			
		}
		
		$options['version'] = FFG_VERSION;
	
		update_option('ffg_options', $options);
	}


	public function activation_notice()
	{
		$output = '<div class="updated">';
			$output .= '<p>';
			$output .= __( 'Facebook Feed Grabber has been activated. Head on over to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a> to get it ready to use!', 'advanced-google-analytics' );
			$output .= '</p>';
		$output .= "</div><!-- /.updated -->\n";

		echo $output;
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
	function sessionStart()
	{
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
	function sessionDestroy()
	{
	    session_destroy();
	}
		
	
	function check_version( $options, $set = 'ffg-options' )
	{

		if ( empty($options) )
			return self::upgrade(array(), $set);

		// See if we need to upgrade the options.
		switch ( version_compare($options['version'], FFG_VERSION) ) {
			case -1:
			case 1:
				return self::upgrade($options, $set);			
				break;


			case 0:
			default:
				return $options;
				break;
		}
	}


	/**
	 * Upgrade the options.
	 *
	 * @param string $set Name of the options panel to upgrade.
	 * @param array $options The options to be upgraded.
	 * @return void
	 **/
	function upgrade( $options, $set ) {

		$defaults = self::get_defaults($set);

		// See if we need to update the stored cache folder by
		// seeing if it's current value matches the old value.
		if ( $options['cache_folder'] == WP_CONTENT_DIR . $defaults['cache_folder'] ) {

			$options['cache_folder'] = $defaults['cache_folder'];

		// See if there's a custom cache folder under the old format.
		} elseif ( !isset($options['cache_base']) ) {

			$options['cache_base'] == '';

		}

		$options = array_merge($defaults, $options);
		$options['version'] = FFG_VERSION;

		update_option($set, $options);

		return $options;
	} // End function upgrade
	
	
}// End class ffg_setup


// On activation or deactivation
global $ffg_setup;
$ffg_setup = new ffg_setup();
register_activation_hook(__FILE__, array($ffg_setup, 'activate'));
register_deactivation_hook(__FILE__, array($ffg_setup, 'deactivate'));


// The Facebook PHP SDK uses sessions. Lets hook in session start and stop functionality.
add_action('init', array($ffg_setup, 'sessionStart'), 1);
add_action('wp_logout', array($ffg_setup, 'sessionDestroy'));
add_action('wp_login', array($ffg_setup, 'sessionDestroy'));

?>