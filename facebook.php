<?php
/*
Plugin Name: Facebook Feed Grabber
Plugin URI: http://wordpress.org/extend/plugins/facebook-feed-grabber/
Description: Allows you to display the feed of a public page or profile on your website. Requires that you create a Facebook Application. Only works with profiles that have public content. To set your App ID & Secret as well as other settings go to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a>.
Version: 0.9 Beta
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

define('FFG_VERSION', '0.9.0');

/**
 * Run the settup stuff for the plugin.	
 */
include_once 'ffg-setup.php';

/**
 * Get the base class for ffg.
 */
include_once 'ffg-base.php';

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
class ffg extends ffg_base
{	

	/**
	 * Uses the ffg_base __construct().
	 */
	
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
		
		print_r( array_merge($defaults, $args) );
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