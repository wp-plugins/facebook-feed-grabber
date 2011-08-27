<?php
/*
Plugin Name: Facebook Feed Grabber
Plugin URI: http://wordpress.org/extend/plugins/facebook-feed-grabber/
Description: Lets you display a facebook feed from a public profile. Requires a facebook App Id andSecret key. Only works with profiles that have public content at this time. To adjust the default number of entries it displays then go to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a>.
Version: 0.5
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
		$version = '0.5';
	
		// Get stored plugin options
		$options = get_option('ffg_options');
	
		$defaults = array(
			// Facebook App ID & Secret
			"app_id" => null,
			"secret" => null,
			
			// Misc Settings
			"default_feed" => null,
			"num_entries" => "3",
			"limit" => 1,
			"default_style" => 1,
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
		TODO : Make timezone based on if user is logged into facebook and use that timezone?
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
	
	Retrieves a public pages newsfeed
	
	$id	-required
		| The id of the page whoms newsfeed to retrieve. 
		| If if you don't wish to limit the posts you can use the nickname
	$maxitems	-optional	default: null
		| Number of entries to retrieve.
		| If null it will revert to what's set in the options.
	$limit 	-optional	default: true
		| Whether to limit posts to ones posted by the page that's being retrieved.
	
	$echo	-optional	default: true
		| If true then it will echo the results or return them other wise.
	
	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed( $id = null, $args = array()) {
	global $facebook;
	
	if ( $facebook === false )
		return false;
	
	// Get the options.
	$options = get_option('ffg_options');
	
	// See if we're using the default feed id.
	if ( $id == null )
		$id = $options['default_feed'];
	
	// If we still don't have a feed id…
	if ( $id == null )
		return false;
	
	// Default parameters.
	$defaults = array(
		'echo' => true,
		'container' => 'div',
		'container_id' => 'fb-feed',
		'container_class' => null,
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
	$content = $facebook->api('/'. $id .'/feed?date_format=U');
	
	if( $content && count($content['data']) > 0 ){
		
		// Output string
		$output = "";
		
		// Count the items as we use them.
		$count = 0;
		
		// Open the container element.
		if ( $container != null ) {
			
			$container_id = ( $container_id != null ) ? " id='". $container_id ."'" : null;
			$container_class = ( $container_class != null ) ? " class='". $container_class ."'" : null;
			$output .= "<". $container . $container_id . $container_class .">\n";
			
		}
		
		// Get the page title.
		if ( $show_title == true ) {
			
			// This call will always work since we are fetching public data.
			$app = $facebook->api('/'. $id .'?date_format=U');

			if ( $app ) {
				$output .= "<p class='fb-page-name'>". $app['name'] ."</p>\n";
			}
			
		}
		
		foreach($content['data'] as $item) {
						
			// If we're limiting it to posts posted by the retrieved page
			if ( $limit == true && $id != $item['from']['id'] )
				continue;
		
			// Get the description of item or the message of the one who posted the item
			$message = isset($item['message']) ? $item['message'] : null;
			$message = preg_replace('/\n/', '<br />', $message);
						
			// Get the description of item or the message of the one who posted the item
			$descript = isset($item['description']) ? $item['description'] : null;
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
			
			// 
			// Start creating the output
			// 
			
			$item_start = "<div class='fb-feed-item fb-item-". $count ."' id='fb-feed-". $item['id'] ."'>\n";
	
			$date = "<p class='fb-date'>";
				$date .= "<a href='". $item_link ."' target='_blank' class='quiet' title='". __('See this post on Facebook') ."'>". $published . $comments ."</a>";
			$date .= "</p>\n";
			
			$item_end = "</div>\n";// End .fb_feed_item
				
					switch ( $item['type'] ) {
						case 'link':
						
								$output .= $item_start;
								
								if ( $message != null  )
									$output .= "<p class='descript'>". $message ."</p>";
								
								$output .= "<blockquote><p>";
								
									$output .= "<a href='". esc_attr($item['link']) ."'>". $item['name'] ."</a>\n";
									
									if ( $message != null || $descript != null || $properties != null )
										$output .= "<br />";
								
									if ( $descript != null )
										$output .= $descript;
									
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
								continue;
							
							$output .= $item_start;
							
								$output .= "<p class='descript'>". $message ."</p>\n";
								
								$output .= $date;
							
							$output .= $item_end;
							
							break;
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

/* - - - - - -
	
	Add default style
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_add_style() {
	
	$options = get_option('ffg_options');
	
	if ( $options['default_style'] == false )
		return false;

	// Paths to the file.
	$style_url = plugins_url('style.css', __FILE__);
	$style_file = WP_PLUGIN_DIR . '/facebook-feed-grabber/style.css';

	wp_register_style('ffg_style', $style_url);
	wp_enqueue_style( 'ffg_style');

}
add_action('wp_print_styles', 'ffg_add_style');
?>