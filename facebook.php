<?php
/*
Plugin Name: Facebook Feed Grabber
Plugin URI: http://wordpress.org/extend/plugins/facebook-feed-grabber/
Description: Allows you to display the feed of a public page or profile on your website. Requires that you create a Facebook Application. Only works with profiles that have public content. To set your App ID & Secret as well as other settings go to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a>.
Version: 0.8.4
Author: Lucas Bonner
Author URI: http://www.lucasbonner.com 
License: GPLv2 or Later

 *
 * Tested and Developed with php 5
 * 
 * Uses facebook/php-sdk v3.2.2
 * http://github.com/facebook/php-sdk/
 *
 */

/**
 * Copyright 2011 Lucas Bonner.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Run the settup stuff for the plugin.	
 */
include_once 'ffg-setup.php';

/**
 * Get the ffg options page stuff if in the admin area.
 */
if ( is_admin() )
	include_once 'ffg-options.php';

/**
 * Hook in ffg widgets.
 */
include_once 'ffg-widgets.php';

/**
 * A class to display a wordpress feed.
 * 
 * @global object The ffg setup object.
 * 
 * @param string $appId Optional. The App ID to use.
 * @param string $secret Optional. The App Secret to use.
 * 
 * @return boolean True if connected to Facebook.
 */
class ffg {
	
	/* - - - Beginning of settings - - - */
	
	// Required settings
	
	// Your app id
	protected $appId = null;
	
	// You app secret.
	protected $secret = null;
	
	// Settings retrieved from the options page.
	public $options = false;
		
	// Date formats for event times.
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
	
	// Our facebook connection gets stored here.
	public $facebook = false;
	
	
	/* - - - - - -

		Fetches facebook app_id and secret and makes a new connection.

	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function __construct( $appId = null, $secret = null ) {
		global $ffg_setup;
		
		$this->options = $ffg_setup->get_options();
		
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
	// End __construct()
	
	
	/* - - - - - -
		
		Authenticate App Id and Secret and make initial connection.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function authenticate(  ) {
		
		// Check that we have an App ID
		if ( $this->appId == null )
			return false;
		
		// Check that we have a secret
		if ( $this->secret == null )
			return false;
		
		// Load the facebook SDK.
		global $ffg_setup;
		$ffg_setup->load_sdk();
				
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
	// End authenticate()
	
	
	/* - - - - - -
		
		Looks to see if $text is a date in one of the following formats,
			-Tomorrow at 5:00pm
			-Wednesday at 5:00pm
			-Wednesday, August 24 at 5:00pm
			-Wednesday, August 24, 2011 at 5:00pm
			
		Returns false if it is not a date, if is a date the it returns it in a string that strtotime() will recognize. 
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function is_date( $text ) {
		
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
	// End is_date()
	
	
	/* - - - - - -
		
		$published = The time to format
		$format = Defaults to feed which means it'll expect a unix timestamp in the first parameter $published. If set to 'event' it will assume we were fed a string that strtotime() will interpret.
		
		Uses the date formats defined in $this->date_formats[$format] for the output.
		
		Returns false on failure of formated string on success.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function format_date( $published, $format = 'feed' ) {
		global $wp_local;
	
		switch ( $format ) {
			
			case 'event':// {
				
				$timestamp = strtotime($published);

				// If we couln't make a unix timestamp
				if ( $timestamp === false )
					return false;
				else
					$published = $timestamp;
				
				// Get the date formats
				$date_formats = $this->date_formats['event'];

				break;
			// }
				
			case 'feed':
			default:
			
				// Get the date formats
				$date_formats = $this->date_formats['feed'];

				break;
				
		}
		
		/*
			LBTD : Make timezone based on if user is logged into facebook and use that timezone?
		*/
	
		// Convert to our wp timezone
		$published = $published + ( get_option( 'gmt_offset' ) * 3600 );
		
		if ( date_i18n('Ymd', $published) == date_i18n('Ymd') )
			$published = date_i18n( $date_formats['today'], $published );
			
		else if ( date_i18n('Y', $published) == date_i18n('Y') )
			$published = date_i18n( $date_formats['this_year'], $published );
			
		else
			$published = date_i18n( $date_formats['other_year'], $published );
		
		return $published;
	}
	// End format_date()


	/**
	 * Count the number of comments
	 * 
	 * @since 0.8.3
	 * 
	 * @param array $item The items array.
	 * 
	 * @return string The HTML output.
	 */
	public function count_comments( $item )
	{

		$output = __('No Comments');

		// Check for comments
		if ( ! isset($item['comments']) )
			return $output;

		$count =  count($item['comments']['data']);

		if ( $count > 1 ) {
			$output = __("$count Comments");
		} else if ( $count == 1 ) {
			// Is there more then one?
			$output = __("1 Comment");
		}

		return $output;
	}

	/**
	 * Get and format event's time and date.
	 * 
	 * Get the specified event name and date/time and 
	 * prepare it for display.
	 * 
	 * @param string Event URL.
	 * 
	 * @since 0.8.4
	 * @return string The events date/time.
	 */
	function event_date( $event_url, $args = array() )
	{

		if ( preg_match('{/events/([0-9]+)/}', $event_url, $match) )
			$event_id = $match[1];
		else
			return null;

		// If args were provided in a query style string.
		if ( is_string($args) )
			parse_str($args, $args);
		
		// Default arguments
		$defaults = array(
			'cache_feed' => $this->options['cache_feed'],
			'locale' => $this->options['locale'],
		);
		
		// Overwrite the defaults and exract our arguments.
		extract( array_merge($defaults, $args) );

		// Get the feed (maybe it's cached?)
		if ( $cache_feed != 0 ) {
			
			// Include cache class
			include_once 'caching.php';
			
			// Initiate class
			$cache = new ffg_cache();

			// Let it do it's magic. (Will return the needed content)
			$event = $cache->theMagic($this, '/'. $event_id .'/?date_format=U&locale='. $locale, (($cache_feed * 60)));
			
		} else
			$event = $this->facebook->api('/'. $event_id .'/?date_format=U&locale='. $locale);

		if ( ! $event )
			return false;

		$output = "<p><a href='". $event_url ."' class='the_link' target='_blank'>". $event['name'] ."</a></p>\n";
		$output .= "<p><small class='caption'>". $this->format_date($event['start_time'], 'event') ."</small></p>\n";
		$output .= "<p><small>". $event['location'] ."</small></p>\n";

		return $output;
	}


	
	/* - - - - - -
		
		Retrieves a feed ID if given a facebook nickname or validates a feed id if givin a number.
		
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function validate_feed( $feed ) {
		
		// TODO
		
	}
	// End validate_feed()
	

	/* - - - - - -

		Retrieves a public page's news feed and by default echos it.

		$feed_id	-optional default:null
			| If you did not set a default page id then you must pass the id of the feed to the function.
			| If no feed id is set in the options and isn't passed directly to the function the it will return false.

		$args	-optional	default: array()
			| below are the possible arguments to change and the default values.
			| array(
				
				~ Cache duration in minutes. To disable set as 0.
				  'cache_feed' => $this->options['cache_feed'],
				
				~ The container to put the results in. If it's null no container will be used.
				  'container' => 'div',

				~ The class or classes of the container.
				  'container_class' => 'fb-feed',

				~ The id of the container.
				  'container_id' => 'fb-feed',

				~ Whether to echo or return the results.
				  'echo' => true,
				
				~ Whether to limit the display to posts posted by the page who's feed is being retrieved.
				  'limit' => $this->options['limit'],

				~ Whether to show the page title before the feed.
				  'show_title' => true
				
				~ Display thumbnails. (TRUE or FALSE)
				  'show_thumbnails' => $this->options['show_thumbnails'],

				~ The maximum number of items to display.
				  'num_entries' => $this->options['num_entries'],
				
			),


	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
	function feed( $feed_id = null, $args = array()) {

		if ( $this->facebook === false )
			return false;

		// See if we're using the default feed id.
		if ( $feed_id == null )
			$feed_id = $this->options['default_feed'];

		// If we still don't have a feed id…
		if ( $feed_id == null )
			return false;

		// If args were provided in a query style string.
		if ( is_string($args) )
			parse_str($args, $args);
		
		// Default arguments
		$defaults = array(
			'cache_feed' => $this->options['cache_feed'],
			'locale' => $this->options['locale'],
			'container' => 'div',
			'container_class' => 'fb-feed',
			'container_id' => 'fb-feed',
			'echo' => true,
			'limit' => $this->options['limit'],
			'show_title' => $this->options['show_title'],
			'show_thumbnails' => $this->options['show_thumbnails'],
			'num_entries' => $this->options['num_entries'],
		);
		
		// Overwrite the defaults and exract our arguments.
		extract( array_merge($defaults, $args) );
		
		// Get the feed (maybe it's cached?)
		if ( $cache_feed != 0 ) {
			
			// Include cache class
			include_once 'caching.php';
			
			// Initiate class
			$cache = new ffg_cache();

			// Let it do it's magic. (Will return the needed content)
			$content = $cache->theMagic($this, '/'. $feed_id .'/feed?date_format=U&locale='. $locale, (($cache_feed * 60)));
			
		} else
			$content = $this->facebook->api('/'. $feed_id .'/feed?date_format=U&locale='. $locale);
			
		if ( $content && count($content['data']) > 0 ) {

			// Output string
			$output = "";

			// Count the items as we use them.
			$count = 0;

			// Open the container element?
			if ( !empty($container) ) {

				$container_id = ( !empty($container_id) ) ? " id='". $container_id ."'" : null;
				$container_class = ( !empty($container_class) ) ? " class='". $container_class ."'" : null;
				$output .= "<". $container . $container_id . $container_class .">\n";

			}

			// Get the page title
			if ( $show_title == true ) {

				// This call will always work since we are fetching public data.
				$app = $this->facebook->api('/'. $feed_id .'?date_format=U&locale='. $locale);

				if ( $app ) {
					$output .= "<p class='fb-page-name'><a href='". $app['link'] ."' title='". $app['name'] ."'>". $app['name'] ."</a></p>\n";
				}

			}
						
			foreach($content['data'] as $item) {
				
				if ( empty($item) )
					continue;
				
				if ( isset($item['status_type']) && $item['status_type'] == 'approved_friend' )
					continue;
				
								
				// If we're limiting it to posts from the retrieved page
				if ( $limit == true ) {

					if ( $feed_id != $item['from']['id'] )
						continue;

				} else {

					// It's not limited to the pages posts so lets get who posted it.

					$from = "<p class='from'>";
						$from .= "<a href='http://www.facebook.com/". $item['from']['id'] ."'>". $item['from']['name'] ."</a>";
					$from .= "</p>\n";

				}

				// Get the description of item or the message of the one who posted the item
				$message = isset($item['message']) ? trim($item['message']) : null;
				$message = preg_replace(array('{\b((https?|ftp)://[-a-zA-Z0-9+&@#/%?=~_|!:,.;]*[a-zA-Z0-9+&@#/%=~_|])}', '/\n/'), array("<a href='$1'>\\1</a>", '<br />'), $message);

				// Get the description of item or the message of the one who posted the item
				$descript = isset($item['description']) ? trim($item['description']) : null;
				// Turn urls into links and replace new lines with <br />
				$descript = preg_replace(array('{\b((https?|ftp)://[-a-zA-Z0-9+&@#/%?=~_|!:,.;]*[a-zA-Z0-9+&@#/%=~_|])}', '/\n/'), array("<a href='$1'>\\1</a>", '<br />'), $descript);
				
				// Get the description of item or the message of the one who posted the item
				$story = isset($item['story']) ? trim($item['story']) : null;
				$story = preg_replace('/\n/', '<br />', $story);


				// See if we have an event
				if ( $item['type'] == 'link' && stristr($item['link'], '/events/') )
					$properties = $this->event_date($item['link']);
				else 
					$properties = null;

				// Format the date
				$published = $this->format_date($item['created_time']);


				$comments = ' &bull; '.$this->count_comments($item);

				// Create a link to the item on facebook
				$item_link = preg_split('/_/', $item['id']);
				$item_link = 'http://www.facebook.com/'. $item_link[0] .'/posts/'. $item_link[1];

				/*
					LBTD : If $descript is an event date it shows in the correct time by default but it does not account for daylight savings time? Fix this?
				*/
				
				// The published date
				$date = "<p class='fb-date'>";
					$date .= "<a href='". $item_link ."' target='_blank' class='quiet' title='". __('See this post on Facebook') ."'>". $published . " ". $comments ."</a>";
				$date .= "</p>\n";
				
				// 
				// finish pieceing together the output.
				// 
				
				// Item opening tag
				$output .= "<div class='fb-feed-item fb-item-". $count ."' id='fb-feed-". $item['id'] ."'>\n";				
					
					// See if we should display who posted it
					if ( $limit == false )
						$output .= $from;
					
					// The actual users status
					if ( $message != null  )
						$output .= "<p class='message'>". $message ."</p>\n";
					else if ( $story != null )
						$output .= "<p class='story'>". $story ."</p>\n";
					
					// See if there's something like a link or video to show.
					if ( isset($item['link']) || $descript != null || $properties != null ) {
						
						$output .= "<blockquote>\n";

							if ( ($show_thumbnails != false && isset($item['picture'])) || (isset($item['link']) && isset($item['name'])) ) {

								$output .= "<p>\n";
								
								if ( $show_thumbnails != false && isset($item['picture']) ) {
									$img = "<img src='". htmlentities($item['picture']) ."' class='thumbnail alignleft' />\n";
									if ( isset($item['link']) )
										$output .= "<a href='". esc_attr($item['link']) ."' class='the_link'>$img</a>\n";
								}

								// The item link
								if ( isset($item['link']) && isset($item['name']) )
									$output .= "<a href='". esc_attr($item['link']) ."' class='the_link'>". $item['name'] ."</a>\n";
								
								$output .= "</p>\n";
							}
								
							// The item caption
							if ( isset($item['caption']) ) {
								if ( preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $item['caption']) ) {
									$caption = preg_replace('/^(?!https?:\/\/)/', 'http://', $item['caption']);
									$caption = "<a href='". esc_attr($caption) ."'>". $item['caption'] ."</a>\n";
									
								} else
									$caption = $item['caption'];
									
								$output .= "<p class='caption'>". $caption ."</p>\n";
							}							
							
							if ( $descript != null || $properties != null ) {
																						
								if ( $descript != null )
									$output .= "<p class='descript'>". $descript ."</p>\n";

								if ( $properties != null )
									$output .= $properties;
																
							}

						$output .= "</blockquote>\n";
						
					}

					$output .= $date;
				
				$output .= "</div>\n";
				
				// Add one to our count tally
				$count++;

				// If we reached our limit
				if( $count == $num_entries)
					break;

			}// End foreach



			// Close the container element.
			if ( $container != null ) {
				$output .= "</". $container .">";
			}

			if ( $echo == true ) {
				echo $output;
				return true;
			} else {
				return $output;
			}

		// end if count($content['data']) > 0	
		} else
			return false;
	}
	// End fb_feed()
		
}


/* - - - - - -
	
	Used to display a feed without you having to mess with the class.
	If you're displaying more than one feed I suggest using the class 
	and not this function.
		
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed( $feed_id = null, $args = array() ) {
	
	$facebook = new ffg();
	
	$facebook = $facebook->feed($feed_id, $args);
	
	return $facebook;
	
}


/* - - - - - -
	
	Add Shortcode tag.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed_shortcode( $args, $feed_id = null ) {
	
	$args['echo'] = false;
	
	$facebook = new ffg();
	$facebook = $facebook->feed($feed_id, $args);
	
	return $facebook;
	
}
add_shortcode('fb_feed', 'fb_feed_shortcode');


/* - - - - - -
	
	Add default style
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_add_style() {
	
	$options = get_option('ffg_options');
	
	// See if we should show a style sheet.
	if ( $options['style_sheet'] == false )
		return false;

	// Get the stylesheet we should use and make it's url.
	$style_url = plugins_url($options['style_sheet'], __FILE__);

	// Tell wp to use the stylesheet.
	wp_register_style('ffg_style', $style_url);
	wp_enqueue_style( 'ffg_style');

}
add_action('wp_print_styles', 'ffg_add_style');
?>