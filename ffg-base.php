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