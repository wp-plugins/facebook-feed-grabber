<?php 
/**
 * Settup things for the plugin.
 * 
 * @package Facebook_Feed_Grabber
 * @since 0.9.0
 */


/**
* Contains the components for connecting to Facebook.
* 
* Contains methods for starting/stopping a session, loading the SDK,
* authenticating the connection... etc
* 
* @since 0.9.0
* 
* @param string $appId Optional. The App ID to use.
* @param string $secret Optional. The App Secret to use.
* 
* @return boolean True if connected to Facebook.
*/
class ffg_base
{

	/* - - - Beginning of settings - - - */
		
	/**
	 * The Facebook App ID (Filled in by __construct())
	 * 
	 * @access protected
	 * @var string Facebook App ID
	 */
	protected $appId = null;
	
	/**
	 * The Facebook App Secret (Filled in by __construct())
	 * 
	 * @access protected
	 * @var string Facebook App Secret
	 */
	protected $secret = null;
	
	/**
	 * Settings retrieved from the options page. (Filled in by __construct())
	 * 
	 * @access public
	 * @var array FFG options.
	 */
	public $options = false;
		
	/**
	 * Date formats for event times.
	 * 
	 * @access public
	 * @var array date formats.
	 */
	public $date_formats = array(
		// Event date formats
		'event' => array(
			'today' => '\T\o\d\a\y \a\t g:ia',
			'this_year' => 'l, F jS \a\t g:ia',
			'other_year' => 'l, F jS, Y \a\t g:ia',
		),
		// Date formats for when something was posted
		'feed' => array(
			'today' => '\T\o\d\a\y \a\t g:ia',
			'this_year' => 'M jS g:ia',
			'other_year' => 'M jS, Y g:ia',
		),
	);

	/* - - - End of settings - - - */
	
	/**
	 * Our facebook connection gets stored here.
	 */
	public $facebook = false;

	/**
	 * Set to true when the Facebook SDK is loaded by this plugin.
	 * 
	 * @access private
	 * @var boolean Tells us if the SDK has been loaded.
	 */
	private $sdk_loaded = false;

	
	/**
	 * Fetches facebook app_id and secret and makes a new connection.
	 * 
	 * @param string $appId Optional. The App ID to use.
	 * @param string $secret Optional. The App Secret to use.
	 * 
	 * @return boolean True if connected to Facebook.
	 */
	function __construct( $appId = null, $secret = null )
	{		
		$this->options = ffg_base::get_options();
		
		// See if we're getting the default App Id.
		if ( $appId == null )
			$appId = $this->options['app_id'];

		// See if we're getting the default secret.
		if ( $secret == null )
			$secret = $this->options['secret'];

		// See if we have an App Id.
		if ( $appId == null)
			return false;

		// See if we have a Secret
		if ( $secret == null )
			return false;
		
		$this->appId = $appId;
		$this->secret = $secret;
		
		$this->authenticate();
		
		if ( $this->facebook === false )
			return false;
		else
			return $this;
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
	 * @global object The Setup object $ffg_setup
	 * 
	 * @param string $set Optional. Name of the options panel to get.
	 * @return Array Array of plugin options.
	 **/
	static public function get_options( $set = 'ffg_options' ) {
		global $ffg_setup;

		static $options = array();

		if ( array_key_exists($set, $options) )
			return $options[$set];

		$optionSet = get_option( $set );

		$optionSet = ffg_setup::check_version($optionSet, $set);

		$options[$set] = $optionSet;

		return $optionSet;

	} // End function get_options


	/**
	 * Authenticate App Id and Secret and make the initial connection.
	 * 
	 * @return object returns $this on success false on failure.
	 */
	function authenticate(  ) {
		
		// Check that we have an App ID
		if ( $this->appId == null )
			return false;
		
		// Check that we have a secret
		if ( $this->secret == null )
			return false;
		
		// Load the facebook SDK.
		$this->load_sdk();
				
		// Make our facebook connection.
		$this->facebook = new Facebook(array(
			  'appId'  => $this->appId,
			  'secret' => $this->secret,
			));
		
		// Proxy support
		if ( isset($this->options['proxy_url']) && !empty($this->options['proxy_url']) ) {
			Facebook::$CURL_OPTS[CURLOPT_PROXY] = $this->options['proxy_url'];
		}
		
		if ( $this->facebook === false )
			return false;
		else
			return $this;
	}

}
 ?>