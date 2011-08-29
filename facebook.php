<?php
/*
Plugin Name: Facebook Feed Grabber
Plugin URI: http://wordpress.org/extend/plugins/facebook-feed-grabber/
Description: Lets you display a facebook feed from a public profile. Requires a facebook App Id andSecret key. Only works with profiles that have public content at this time. To adjust the default number of entries it displays then go to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a>.
Version: 0.5.2
Author: Lucas Bonner
Author URI: http://www.lucasbonner.com 
 *
 * Tested and Developed with php 5
 * 
 * Uses facebook/php-sdk 3.1.1
 * http://github.com/facebook/php-sdk/
 *
 */

/**
 * Copyright (c) 2011 Lucas Bonner. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

// On activation or deactivation
register_activation_hook(__FILE__, array('ffg_setup', 'activate'));
register_deactivation_hook(__FILE__, array('ffg_setup', 'deactivate'));

/* - - - - - -
	
	Class containing setup and deactivation stuff.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
class ffg_setup {
	
	// 
	// Define default options
	function activate() {
		
		// Current plugin version
		$version = '0.5.2';
	
		// Get stored plugin options
		$options = get_option('ffg_options');
	
		$defaults = array(
			// Facebook App ID & Secret
			"app_id" => null,
			"secret" => null,
			
			// Misc Settings
			"default_feed" => null,
			"num_entries" => 3,
			"limit" => 1,
			"style_sheet" => 'style_sheet.css',
			"delete_options" => 0,
			
			// Current Version
			'version' => $version
			);
		
		// If there aren't already settings defined then set the defaults.
	    if( !is_array($options) ) {
		
			$options = $defaults;
		
		// If the defined settings aren't for this version add any new settings.
		} else if ( $options['version'] != $version) {
			$options = array_merge($defaults, $options);
		}
		
		$options['version'] = '0.5';
	
		update_option('ffg_options', $options);
	}

	// 
	// Delete ffg options if 'restore_defaults' is true
	function deactivate(  ) {
	
		$opt = get_option('ffg_options');
	
		if ( $opt['delete_options'] )
			delete_option('ffg_options');
	
	}
	
}

// 
// Get the options page stuff if in the admin area.
if ( is_admin() )
	include 'ffg-options.php';

// 
// Get the facebook sdk
require_once 'facebook-sdk/facebook.php';

/*
	LBTD : Turn the stuff below into a class?
*/

/* - - - - - -
	
	Fetches facebook app_id and secret and makes a new connection.
	
- - - - - - - - - - - - - -3 - - - - - - - - - - - - - - - - - - */
function fb_authentication( $appId = null, $secret = null  ) {
	
	$options = get_option('ffg_options');
	
	// See if we're getting the default App Id.
	if ( $appId == null )
		$appId = $options['app_id'];

	// See if we're getting the default secret.
	if ( $secret == null )
		$secret = $options['secret'];
	
	// See if we have an App Id.
	if ( $appId == null)
		return false;
	
	// See if we have a Secret
	if ( $secret == null )
		return false;
	
	// 
	// Set facebook appId and secret
	return new Facebook(array(
		  'appId'  => $options['app_id'],
		  'secret' => $options['secret'],
		));
}

$facebook = fb_authentication();


function format_date( $published, $format = 'feed', $unixTimestamp = true ) {
	global $wp_local;
	
	// If we weren't given a unix timestamp make it one.
	if ( $unixTimestamp == false )
		$timestamp = strtotime($published);
	else
		$timestamp = true;
	
	// If we couln't make a unix timestamp
	if ( $timestamp === false )
		return $published;
	elseif ( $timestamp !== true )
		$published = $timestamp;
		
	/*
		LBTD : Make timezone based on if user is logged into facebook and use that timezone?
	*/
	
	// Convert to our wp timezone
	$published = $published + ( get_option( 'gmt_offset' ) * 3600 );
	
	switch ( $format) {
		case 'event':
			
			if ( date_i18n('Ymd', $published) == date_i18n('Ymd') )
				$published = "Today at ". date_i18n( 'g:ia', $published );
			else if ( date_i18n('Y', $published) == date_i18n('Y') )
				$published = date_i18n( 'l, F dS \a\t g:ia', $published );
			else
				$published = date_i18n( 'l, F dS, Y \a\t g:ia', $published );
			
			break;
		
		case 'feed':
		default:
			
			if ( date_i18n('Ymd', $published) == date_i18n('Ymd') )
				$published = date_i18n( 'g:ia', $published );
			else if ( date_i18n('Y', $published) == date_i18n('Y') )
				$published = date_i18n( 'M dS g:ia', $published );
			else
				$published = date_i18n( 'M dS, Y g:ia', $published );
			
			break;
	}
		
	return $published;
}


/* - - - - - -
	
	Retrieves a public page's news feed and by default echos it.
	
	$feed_id	-optional default:null
		| If you did not set a default page id then you must pass the id of the feed to the function.
		| If no feed id is set in the options and isn't passed directly to the function the it will return false.
		
	$args	-optional	default: array()
		| below are the possible arguments to change and the default values.
		| array(
			// Whether to echo or return the results.
			'echo' => true,
			
			// The container to put the results in. If it's null no container will be used.
			'container' => 'div',
			
			// The id of the container.
			'container_id' => 'fb-feed',
			
			// The class or classes of the container.
			'container_class' => 'fb-feed',
			
			// Whether to limit the display to posts posted by the page who's feed is being retrieved.
			'limit' => $options['limit'],
			
			// The maximum number of items to display.
			'maxitems' => $options['num_entries'],
			
			// Whether to show the page title before the feed.
			'show_title' => true
			)
			
	
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed( $feed_id = null, $args = array()) {
	global $facebook;
	
	if ( $facebook === false )
		return false;
	
	// Get the options.
	$options = get_option('ffg_options');
	
	// See if we're using the default feed id.
	if ( $feed_id == null )
		$feed_id = $options['default_feed'];
	
	// If we still don't have a feed id…
	if ( $feed_id == null )
		return false;
	
	// Default parameters.
	$defaults = array(
		'echo' => true,
		'container' => 'div',
		'container_id' => 'fb-feed',
		'container_class' => 'fb-feed',
		'limit' => $options['limit'],
		'maxitems' => $options['num_entries'],
		'show_title' => true
	);
	
	// If args were provided in a query style string.
	if ( is_string($args) )
		parse_str($args, $args);
		
	
	// Overwrite defaults as neccissary.
	$args = array_merge($defaults, $args);
	
	// Exract our arguments.
	extract($args);
	
	// Get the feed
	$content = $facebook->api('/'. $feed_id .'/feed?date_format=U');
	
	if( $content && count($content['data']) > 0 ){
		
		// Output string
		$output = "";
		
		// Count the items as we use them.
		$count = 0;
		
		// Open the container element?
		if ( $container != null ) {
			
			$container_id = ( $container_id != null ) ? " id='". $container_id ."'" : null;
			$container_class = ( $container_class != null ) ? " class='". $container_class ."'" : null;
			$output .= "<". $container . $container_id . $container_class .">\n";
			
		}
		
		// Get the page title ?
		if ( $show_title == true ) {
			
			// This call will always work since we are fetching public data.
			$app = $facebook->api('/'. $feed_id .'?date_format=U');

			if ( $app ) {
				$output .= "<p class='fb-page-name'><a href='". $app['feed_id'] ."' alt='". $app['name'] ."'>". $app['name'] ."</a></p>\n";
			}
			
		}
		
		foreach($content['data'] as $item) {
						
			// If we're limiting it to posts posted by the retrieved page
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
			$message = preg_replace('/\n/', '<br />', $message);
						
			// Get the description of item or the message of the one who posted the item
			$descript = isset($item['description']) ? trim($item['description']) : null;
			$descript = preg_replace('/\n/', '<br />', $descript);
			
			// If it's an event…
			if ( isset($item['properties']) ) {
				
				$properties = null;
				
				foreach( $item['properties'] as $key => $property ) {

					// If it's a date we want to change the timezone.
					// First lets remove the things strtotime doesn't like
					$find = array(',','Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'at ');
					$date = str_replace($find, '', $property['text']);
					$date = strtotime(trim($date));

					if ( $date != false )
						$date = format_date($date, 'event');

					$properties .= ( !$date ) ? $property['text'] : $date;

					// If there's another line of text
					if ( $key != (count($item['properties']) - 1) )
						$properties .= "<br />";
				}
				
			} else
				$properties = null;
						
			// Format the date
			$published = format_date($item['created_time']);
	
			// Check for comments
			if ( isset($item['comments']) ) {
				$comments = ( $item['comments']->count > 1 ) ? __(' Comments') : __(' Comment');
				$comments = ' &bull; '. $item['comments']->count . $comments;
			} else
				$comments = __(' &bull; No Comments');

			// Create a link to the item on facebook
			$item_link = preg_split('/_/', $item['id']);
			$item_link = 'http://www.facebook.com/'. $item_link[0] .'/posts/'. $item_link[1];
			
			// Item opening tag
			$item_start = "<div class='fb-feed-item fb-item-". $count ."' id='fb-feed-". $item['id'] ."'>\n";
	
			// The published date
			$date = "<p class='fb-date'>";
				$date .= "<a href='". $item_link ."' target='_blank' class='quiet' title='". __('See this post on Facebook') ."'>". $published . $comments ."</a>";
			$date .= "</p>\n";
			
			// Item closing tag
			$item_end = "</div>\n";
				
			switch ( $item['type'] ) {
				case 'link':
				
						$output .= $item_start;
						
						if ( $limit == false )
							$output .= $from;
						
						if ( $message != null  )
							$output .= "<p class='message'>". $message ."</p>";
						
						$output .= "<blockquote><p>";
						
							$output .= "<a href='". esc_attr($item['link']) ."'>". $item['name'] ."</a>\n";
							
						
							if ( $descript != null )
								$output .= "<span class='descript'>". $descript ."</span>\n";
							
							if ( $descript != null && $properties != null )
								$output .= "<br /><br />";
							
							if ( $properties != null )
								$output .= $properties;
								
						$output .= "</p></blockquote>\n";
						
						$output .= $date;
						
						$output .= $item_end;
																						
					break;
			
				case 'status':
					
					if ( $message == null && $descript == null )
						continue 2;
					
					$output .= $item_start;
						
						if ( $limit == false )
							$output .= $from;
					
						$output .= "<p class='descript'>". $message ."</p>\n";
						
						$output .= $date;
					
					$output .= $item_end;
					
					break;
					
				case 'video':
					
				$output .= $item_start;
				
				if ( $limit == false )
					$output .= $from;
				
				if ( $message != null  )
					$output .= "<p class='message'>". $message ."</p>";
				
				$output .= "<blockquote><p>";
				
					$output .= "<a href='". esc_attr($item['source']) ."'>". $item['name'] ."</a>\n";
				
					if ( $descript != null )
						$output .= "<span class='descript'>". $descript ."</span>\n";
						
				$output .= "</p></blockquote>\n";
				
				$output .= $date;
				
				$output .= $item_end;
					
					break;
				
				default:
					continue 2;
			}
								
			// Add one to our count tally
			$count++;
			
			// If we reached our limit
			if( $count == $maxitems)
				break;
			
		}// End foreach
		
		// Close the container element.
		if ( $container != null ) {
			$output .= "</". $container .">";
		}
		
		if ( $echo == true )
			echo $output;
		else
			return $output;
	
	// end if count($content['data']) > 0	
	} else
		return false;
}
// End fb_feed()

/* - - - - - -
	
	Add default style
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_add_style() {
	
	$options = get_option('ffg_options');
	
	if ( $options['style_sheet'] == false )
		return false;

	// Paths to the file.
	$style_url = plugins_url($options['style_sheet'], __FILE__);

	wp_register_style('ffg_style', $style_url);
	wp_enqueue_style( 'ffg_style');

}
add_action('wp_print_styles', 'ffg_add_style');
?>