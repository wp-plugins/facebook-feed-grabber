<?php
/*
Plugin Name: Facebook Feed Grabber
Plugin URI: http://wordpress.org/extend/plugins/facebook-feed-grabber/
Description: Lets you display a facebook feed from a public profile. Requires a facebook appId and secret key. Only works with profiles that have public content at this time. To adjust the number of entries it displays then go to <a href="options-general.php?page=facebook-feed-grabber/ffg-options.php">Settings &rarr; Facebook Feed Grabber</a>.
Version: 0.4
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
	
		$opt = get_option('plugin_options');
	
	    if( ( !is_array($opt) || $opt['restore_defaults']) ) {
			$opt = array(
				"app_id" => null,
				"secret" => null,
				"num_entries" => "3",
				"restore_defaults" => 0
				);
		}
		
		$opt['version'] = 0.4;
	
		update_option('ffg_options', $opt);
	}

	// 
	// Delete ffg options if 'restore_defaults' is true
	function deactivate(  ) {
	
		$opt = get_option('ffg_options');
	
		if ( !$opt['restore_defaults'] || $opt['restore_defaults'] )
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
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_authentication(  ) {
	
	$options = get_option('ffg_options');
	
	if ( $options['app_id'] == null)
		return false;
	
	if ( $options['secret'] == null )
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
 * 
 * Retrieves a public pages newsfeed
 * 
 * $id	-required
 * 		| The id of the page whoms newsfeed to retrieve. 
 * 		| If if you don't wish to limit the posts you can use the nickname
 * $maxitems	-optional	default: null
 * 		| Number of entries to retrieve.
 * 		| If null it will revert to what's set in the options.
 * $limit 	-optional	default: true
 * 		| Whether to limit posts to ones posted by the page that's being retrieved.
 * $echo	-optional	default: true
 * 		| If true then it will echo the results or return them other wise.
 * 
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed( $id, $maxitems = null, $limit = true, $echo = true ) {
	global $facebook;
	
	if ( $facebook === false )
		return false;
	
	// Get the feed
	$content = $facebook->api('/'. $id .'/feed?date_format=U');
	
	if( $content ){
		$output = "";
		$count = 0;
		
		if ( $maxitems == null ) {
			$opt = get_option('ffg_options');
			$maxitems = $opt['num_entries'];
		} else {
			$maxitems = intval($maxitems);
		}
				
		if( count($content['data']) > 0 ) {
			
			foreach($content['data'] as $item) {
								
				// If we're limiting it to posts posted by the retrieved page
				if ( $limit == true && $id != $item['from']['id'] )
					continue;
				
				// Get the description of item or the message of the one who posted the item
				$desc = $item['message'] ? $item['message'] : $item['description'];
				$desc = preg_replace('/\n/', '<br />', $desc);
				
				// Format the date
				$published = format_date($item['created_time']);
			
				// Check for comments
				if ( isset($item['comments']) ) {
					$comments = ( $item['comments']->count > 1 ) ? ' Comments' : ' Comment';
					$comments = ' &bull; '. $item['comments']->count . $comments;
				} else
					$comments = ' &bull; No Comments';

				// Create a linke to the item on facebook
				$item_link = preg_split('/_/', $item['id']);
				$item_link = 'http://www.facebook.com/'. $item_link[0] .'/posts/'. $item_link[1];

				// Start creating the output
				
				$output .= '<div class="fb_feed" id="fb_feed_'.$item['id'].'_'.$count.'">';
					
					$output .="<div class='fb_feed_desc'>\n";
						
						switch ( $item['type'] ) {
							case 'link':
							
								$output .= "<p><a href='". htmlentities($item['link']) ."'>". $item['name'] ."</a></p>\n";
								if ( $desc != null ) 
								
									$output .= "<blockquote><p>$desc</p></blockquote>";
									
								// Used on an event link and contains when/where
								else if ( is_array($item['properties']) ) {
									
									$output .= "<blockquote><p>";
									
										foreach( $item['properties'] as $key => $property ) {
											
											// If it's a date we want to change the timezone.
											// First lets remove the things strtotime doesn't like
											$find = array(',','Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'at ');
											$date = str_replace($find, '', $property['text']);
											$date = strtotime(trim($date));
											
											if ( $date != false )
												$date = format_date($date, 'event');

											$output .= ( !$date ) ? $property['text'] : $date;
											
											// If there's another line of text
											if ( $key != (count($item['properties']) - 1) )
												$output .= "<br />";
										}
									
									$output .= "</p></blockquote>\n";
									
								}
								
								break;
							
							case 'status':
							default:
								$output .= "<p>$desc</p>\n";
								break;
						}
						
						$output .= "<p class='fbdate'>";
							$output .= "<a href='". $item_link ."' target='_blank' class='quiet' title='See this post on Facebook'>". $published . $comments ."</a>";
						$output .= "</p>\n";
					$output .= "</div>\n";
				$output .= "</div>\n";
			
				if( $count == $maxitems-1)
					break;
				$count++;
			}
		}// End foreach
			
		if ( $echo == true )
			echo $output;
		else
			return $output;
		
	// end if count($content['data']) > 0	
	} else
		return false;
}
?>