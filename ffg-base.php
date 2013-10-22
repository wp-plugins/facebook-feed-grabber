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
	 * The feed ID currently being worked with.
	 * 
	 * @since 0.9.0
	 * @var string The feed ID being worked with
	 */
	public $feed_id = null;

	
	/**
	 * Fetches facebook app_id and secret and makes a new connection.
	 * 
	 * Fetches the Facebook appId and secret from the options unless 
	 * otherwise specified. Returns this object if successful or false 
	 * on failure.
	 * 
	 * @param string $appId Optional. The App ID to use.
	 * @param string $secret Optional. The App Secret to use.
	 * 
	 * @return mixed False if we failed connected to Facebook.
	 */
	function __construct( $appId = null, $secret = null )
	{		
		$this->options = ffg_base::get_options();
		
		// Set the app ID.
		if ( ! $this->set_appId( $appId ) )
			return false;
		
		// Set the app secret. Returns false if 
		if ( ! $this->set_secret( $secret) )
			return false;

		// Initiate the Facebook class.
		$facebook = $this->authenticate();
		
		if ( $facebook === false )
			return false;
		else
			return $this;
	}

	
	/**
	 * Load the Facebook SDK.
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
	 * Authenticate App Id and Secret and make the initial connection.
	 * 
	 * @return object returns $this on success false on failure.
	 */
	function authenticate(  ) {
		
		// Check that we have an App ID
		if ( empty($this->appId) && ! $this->set_appId(null) )
			return false;
		
		// Check that we have a secret
		if ( empty($this->secret) && ! $this->set_secret(null) )
			return false;
		
		// Load the Facebook SDK.
		$this->load_sdk();

		// See if we've already initiated the Facebook class.
		if ( $this->facebook != false )
			return $this->facebook;
		
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
			return $this->facebook;
	}


	/**
	 * Get the specified content from Facebook.
	 * 
	 * Get the specified content from Facebook making use 
	 * of caching if enabled.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $path The path.
	 * @param int $cache_feed The number of minutes to cache the feed.
	 * 
	 * @return array The content returned from FB.
	 */
	public function fb_content( $path, $cache_feed )
	{
		// Get the feed (maybe it's cached?)
		if ( $cache_feed != 0 ) {
			
			// Include ffg_cache class
			include_once 'caching.php';
			
			// Let it do it's magic. (Will return the needed content)
			return ffg_cache::theMagic($this, $path, ($cache_feed * 60));
			
		} else
			return$this->facebook->api($path);

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
	 * Set the App ID.
	 * 
	 * Set the App ID if it's changed.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $appId The App ID.
	 * 
	 * @return boolean True if the App ID was changed.
	 */
	public function set_appId( $appId )
	{
		// If we weren't given an app Id then check the options.
		if ( empty($appId) )
			$appId = $this->options['app_id'];

		// If it needs set.
		if ( $this->appId != $appId )
			$this->appId = $appId;
		else
			return false;

		return true;
	}


	/**
	 * 
	 */
	public function set_secret( $secret )
	{
		// See if we have a Secret
		if ( empty($secret) )
			$secret = $this->options['secret'];
		
		// If it needs set.
		if ( $this->secret != $secret )
			$this->secret = $secret;
		else
			return false;

		return true;
	}


	/**
	 * Get or set the feed ID.
	 * 
	 * Sets the feed ID to the value of $param or gets the feed ID if
	 * the parameter is omitted. Returns null if it couldn't find one.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $feed_id A feed ID.
	 * 
	 * @return string The feed ID being worked with.
	 */
	public function feed_id( $feed_id = null )
	{
		// See if a feed ID was given to set.
		if ( ! empty($feed_id) )
			$this->feed_id = $feed_id;

		// If there isn't already a feed ID for this instance 
		// then get the default.
		if ( empty($this->feed_id) )
			$this->feed_id = $this->options['default_feed'];

		// Return the feed ID.
		return $this->feed_id;
	}


	/**
	 * Verify App ID and Secret.
	 * 
	 * Verifies an App ID and Secret. Returns an array with App
	 * info on success or FALSE on failure.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $appId The App ID.
	 * @param string $secret The App secret.
	 * 
	 * @return mixed Array with App info if successful else it returns false.
	 */
	function verify_app_cred( $appId, $secret ) {

		// If the appID was not provided.
		if ( empty($appId) )
			return false;

		// If the secret was not provided.
		if ( empty($secret) )
			return false;

		// Load the facebook SDK.
		$this->load_sdk();

		try {
			// Try to make the connection
			$facebook = new Facebook(array(
				  'appId'  => $appId,
				  'secret' => $secret
				));
		} catch (FacebookApiException $e) {
			return false;
		}

		// If it couldn't connect…
		if ( ! $facebook )
			return false;

		try {
			// This call will always work since we are fetching public data.
			$app = $facebook->api('/'. $appId);
		} catch (FacebookApiException $e) {
			return false;
		}

		$this->set_appId($appId);
		$this->set_secret($secret);
		$this->facebook = $facebook;
		
		return $app;		
	}


	/**
	 * Verify feed.
	 * 
	 * Verifies a feed. Returns an array with Feed
	 * info on success or FALSE on failure.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $feed_id The feed's ID.
	 * 
	 * @return mixed Array with Feed info if successful else it returns false.
	 */
	function verify_feed( $feed_id ) {

		// If there is no feed id.
		if ( empty($feed_id) )
			return false;

		// Load the facebook SDK.
		$this->load_sdk();
		
		// Initiate the Facebook class.
		$facebook = $this->authenticate();

		// If it couldn't connect…
		if ( ! $facebook )
			return false;

		try {
			// This call will always work since we are fetching public data.
			$feed_info = $facebook->api('/'. $feed_id);
		} catch (FacebookApiException $e) {
			return false;
		}
		
		return $feed_info;		
	}


	/**
	 * 
	 */
	public function get_user( $user_id = 'me', $cache = 0 )
	{
				// If there is no feed id.
		if ( empty($user_id) )
			return false;

		// Load the facebook SDK.
		$this->load_sdk();
		
		// Initiate the Facebook class.
		$facebook = $this->authenticate();
		print_r($facebook); echo "hello";

		// If it couldn't connect…
		if ( ! $facebook )
			return false;

		// print_r($facebook->getUser());

		try {
			// This call will always work since we are fetching public data.
			$user = $this->fb_content('/'. $user_id, $cache);
		} catch (FacebookApiException $e) {
		    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
		    exit;
			return false;
		}
		
		return $user;
	}


	/**
	 * Taks a URL and gets the user/page ID.
	 * 
	 * Taks a URL that is presumed to be a Facebook User or Page 
	 * timeline URL and gets the user/page ID.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $url A FB user/page url.
	 * 
	 * @return mixed Feed ID on success or false on failure.
	 */
	public function id_from_url( $url )
	{

		// Our pattern to match a link.
		$pattern = '{^((https?|ftp)://[-a-zA-Z0-9+&@#/%?=~_|!:,.;]*[a-zA-Z0-9+&@#/%=~_|])$}';

		if ( ! preg_match($pattern, $url) )
			return false;

		// Load the facebook SDK.
		$this->load_sdk();
		
		// Initiate the Facebook class.
		$facebook = $this->authenticate();

		try {
			$feed = $this->fb_content('/'. $url, $this->options['cache_feed']);
		} catch (FacebookApiException $e) {
			return false;
		}

		if ( $feed and isset($feed['username']) )
			return $feed['id'];

		return false;
	}
	

	/**
	 * Taks a FB username and gets the user/page ID.
	 * 
	 * Taks a username that is presumed to be a Facebook User or Page 
	 * username and gets the user/page ID.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $username A FB user/page username.
	 * 
	 * @return mixed Feed ID on success or false on failure.
	 */
	public function id_from_username( $username )
	{

		// Our pattern to match a username.
		$pattern = '{^[a-zA-Z0-9.]+$}';

		if ( ! preg_match($pattern, $username) )
			return false;

		// Load the facebook SDK.
		$this->load_sdk();
		
		// Initiate the Facebook class.
		$facebook = $this->authenticate();

		try {
			$feed = $this->fb_content('/'. $username, $this->options['cache_feed']);
		} catch (FacebookApiException $e) {
			return false;
		}

		if ( $feed and isset($feed['username']) )
			return $feed['id'];

		return false;
	}


	/**
	 * Wraps the given string in a container.
	 * 
	 * Wraps the given string in a container.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $output The string to wrap.
	 * @param string $args An array of argumnts.
	 * 
	 * @return string The HTML output.
	 */
	public function container( $output, $args = array() )
	{
		// If args were provided in a query style string.
		if ( is_string($args) )
			parse_str($args, $args);
		
		// Default arguments
		$defaults = array(
			'name' => 'aside',
			'class' => '',
			'id' => '',
			);
		
		// Overwrite the defaults and exract our arguments.
		extract( array_merge($defaults, $args) );

		if ( empty($name) )
			return $output;

		// The ID for the container.
		$id = ( !empty($id) ) ? " id='". $id ."'" : null;

		// The classes for the container.
		$class = " class='". $class ."'";

		// Open the container element
		$open = "<". $name . $id . $class .">\n";

		$output .= "\n";

		// Close the container element.
		$close = "</". $name .">\n";

		return $open . $output . $close;
	}

	/**
	 * Looks to see if $text is a date.
	 * 
	 * Looks to see if $text is a date in one of the following formats,
	 * 		-Tomorrow at 5:00pm
	 * 		-Wednesday at 5:00pm
	 * 		-Wednesday, August 24 at 5:00pm
	 * 		-Wednesday, August 24, 2011 at 5:00pm
	 * 		
	 * 	
	 * 
	 * @since 0.6
	 * 
	 * @param string $text The string to test.
	 * 
	 * @return mixed Returns false if it is not a date, if is a 
	 * date the it returns it in a string that strtotime() will 
	 * recognize. 
	 */
	function is_date( $text )
	{
		
		// Days for preg_match regular expression
		$days = "(Sunday|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday)";
		// Months for preg_match regular expression
		$months = "(January|February|March|April|May|June|July|August|September|October|November|December)";
		
		// 	if ( preg_match([Tomorrow at time], $text) )
		if ( preg_match("/^Tomorrow at ([1-9]|1[012]):([0-6][0-9])(am|pm)$/i", $text, $date) )
			$date = "Tomorrow {$date[1]}:{$date[2]}{$date[3]}";
		
		// if ( preg_match([day at time], $text) )
		elseif ( preg_match("/^$days at ([1-9]|1[012]):([0-6][0-9])(am|pm)$/i", $text, $date) )
			$date = "{$date[1]} {$date[2]}:{$date[3]}{$date[4]}";

		// if ( preg_match([day, month day at time], $text) )
		elseif ( preg_match("/^$days, $months ([0-9]|[12][0-9]|3[01]) at ([1-9]|1[012]):([0-6][0-9])(am|pm)$/i", $text, $date) )
			$date = "{$date[2]} {$date[3]} {$date[4]}:{$date[5]}{$date[6]}";
		
		
		// if ( preg_match([day, month day, year at time], $text) )
		elseif ( preg_match("/^$days, $months ([0-9]|[12][0-9]|3[01]), (20[0-9][0-9]) at ([1-9]|1[012]):([0-6][0-9])(am|pm)$/i", $text, $date) )
			$date = "{$date[2]} {$date[3]}, {$date[4]} {$date[5]}:{$date[6]}{$date[7]}";
		
		else
			return false;
		
		return $date;
		
	}

	/**
	 * Reformat a date/time to our standards.
	 * 
	 * $format = Defaults to feed which means it'll expect a unix 
	 * timestamp in the first parameter $date. If set to 'event' it 
	 * will assume we were fed a string that strtotime() will 
	 * interpret.
	 * 
	 * @todo Make the timezone based on logged in Facebook user?
	 * 
	 * @since 0.6
	 * 
	 * @param string $date The time to format
	 * @param string $format Format preset name.
	 * 
	 * @return mixed Returns false on failure or formated string 
	 * on success.
	 */
	function format_date( $date, $format = 'feed' )
	{
		
		if ( $format == 'event' ) {

			$date = strtotime($date);

			// If we couln't make a unix timestamp
			if ( $timestamp === false )
				return false;

		}

		// Convert to our wp timezone
		$date = $date + ( get_option( 'gmt_offset' ) * 3600 );

		return human_time_diff($date);
		
	}


}
 ?>