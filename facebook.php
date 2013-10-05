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
	function feed( $feed_id = null, $args = array() )
	{

		if ( $this->facebook === false )
			return false;

		// See if we're using the default feed id.
		if ( empty($feed_id) )
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
			/**
			 * @see $this->container() param $args for container values.
			 */
			'container' => array(), 
			'echo' => true,
			'limit' => $this->options['limit'],
			'show_title' => $this->options['show_title'],
			'show_thumbnails' => $this->options['show_thumbnails'],
			'num_entries' => $this->options['num_entries'],
		);
		
		// Overwrite the defaults and exract our arguments.
		extract( array_merge($defaults, $args) );

		// The "path" for the fb content.
		$path = '/'. $feed_id .'/feed?date_format=U&locale='. $locale;

		// Get the content and use caching if enabled.
		$content = $this->fb_content($path, $cache_feed);

		// Our output string.
		// The container will be added at the end.
		$output = NULL;


		// Count the items as we use them.
		$count = 0;


		// Get the feed title.
		$output .= $this->the_title($feed_id, $show_title, $cache_feed);

		foreach($content['data'] as $item) {

			if ( $this->skip($item, $limit) )
				continue;

			// Who posted this status.
			$from = $this->from($item);

			// Get the message
			$message = $this->story($item, 'message');

			// Get the description.
			$descript = $this->story($item, 'description');

			// Get the 'story'
			$story = $this->story($item, 'story');

			// Get event properties
			$properties = $this->event_properties($item);

			// Format the date
			$published = $this->format_date($item['created_time']);

			// Get comment count.
			$comments = $this->count_comments($item);

			// Get the item url.
			$item_url = $this->item_url($item);

			// Get the meta paragraph
			$meta = $this->meta($item_url, $published, $comments);

			$item_output = null;

			// See if we should display who posted it
			if ( $limit == false )
				$item_output .= $from;

			// The actual users status
			if ( $message != null  )
				$item_output .= $this->container($message, array('name' => 'div', 'class' => 'fb-content'));

			else if ( $story != null )
				$item_output .= $this->container($story, array('name' => 'div', 'class' => 'fb-content'));

			$item_output .= $this->shared_link($item, $show_thumbnails, $descript, $properties);

			$item_output .= $meta;

			$output .= $this->container($item_output, array('name' => 'article', 'class' => 'fb-feed-item'));

			// Add one to our count tally
			$count++;

			// If we reached our limit
			if( $count == $num_entries)
				break;

		}

		// Set the default container id.
		$container = array_merge( array('id' => 'fb-feed'), $container );

		// Add a class of our own to the container.
		$container['class'] = " fb-feed ". $container['class'];

		// Wrap the content with the container.
		$output = $this->container($output, $container);

		if ( $echo == true ) {
			echo $output;
			return true;
		} else {
			return $output;
		}

	}
	// End fb_feed()

	/**
	 * Looks to see if we should skip this item.
	 * 
	 * @since 0.9.0
	 * 
	 * @param array $item The item to check.
	 */
	public function skip($item, $limit)
	{
		// If there's nothing there.
		if ( empty($item) )
			return true;
		
		// If it's just an entry telling about a new friend.
		if ( isset($item['status_type']) && $item['status_type'] == 'approved_friend' )
			return true;

		// If it's a photo but not an added photo
		if ( $item['type'] == 'photo' && ! isset($item['status_type']) )
			return true;

		// If we're limiting it to posts from the retrieved page
		if ( $limit == true ) {
			// If the post isn't posted by the feed author
			if ( $feed_id != $item['from']['id'] )
				return true;
		}

		
		return false;
	}

	/**
	 * Gets the name/title of the user/page the feed belongs to.
	 * 
	 * Check to see if we're displaying the name/title of the 
	 * user/page the feed belongs to gets it.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $feed_id ID of the user/page.
	 * @param boolean $show_title Are we going to display the title?
	 * @param int $cache_feed The number of minutes to cache the feed.
	 * 
	 * @return string The HTML to output.
	 */
	public function the_title( $feed_id, $show_title, $cache_feed )
	{

		if ( ! $show_title )
			return null;

		// The "path" for the fb content.
		$path = '/'. $feed_id .'?date_format=U&locale='. $locale;

		// Get the title and cache it if enabled.
		// This will be public data.
		$content = $this->fb_content($path, $cache_feed);

		if ( ! $content )
			return false;

		// The title in a link.
		$output = "<a href='". $content['link'] ."' title='". $content['name'] ."'>". $content['name'] ."</a>";

		// Container arguments.
		$container = array(
			'name' => 'header', 
			'class' => 'fb-page-name',
			);

		$output = $this->container($output, $container);
		// "<p class='fb-page-name'></p>\n";

		return $output;
	}

	/**
	 * Creates a link to author of the status.
	 *	
	 * @since 0.9.0
	 * 
	 * @param array $item The items to get the author of.
	 * 
	 * @return string The HTML output.
	 */
	public function from($item)
	{
		$container = array(
			'name' => 'p',
			'class' => 'fb-from',
			);

		// The link. 
		$output = "<a href='http://www.facebook.com/". $item['from']['id'] ."'>". $item['from']['name'] ."</a>";

		// In a paragraph.
		$output = $this->container($output, $container);


		return $output;
	}

	/**
	 * Get item story, message, desciption.
	 * 
	 * Facebook gives us featured text in either a message, 
	 * description or story element depending on the type of post.
	 * This will get the story if it's there find the links and make
	 * paragraphs and line breaks.
	 * 
	 * @since 0.9.0
	 * 
	 * @param array $item The item to get the element out of.
	 * @param string $key The key for the story to get.
	 * 
	 * @return string The HTML to output.
	 */
	public function story( $item, $key )
	{
		// Get the story text if it's there.
		$output = isset($item[$key]) ? trim($item[$key]) : null;

		if ( $output == null )
			return null;

		// Find the URLs.
		$output = $this->find_links($output);

		// Paragraphs and breaks.
		$output = wpautop( $output );

		return $output;
	}

	/**
	 * 
	 */
	public function shared_link( $item, $show_thumbnails, $descript, $properties )
	{
		// See if there's something like a link or video to show.
		if ( ! isset($item['link']) && $descript == null && $properties == null )
			return null;

		$output = null;
		
		// The item link
		if ( isset($item['link']) && isset($item['name']) )
			$output .= "<a href='". esc_attr($item['link']) ."' class='block-link' target='_blank'>". esc_attr($item['name']) ."</a>\n";
									
		$output .= $this->thumbnail($item, $show_thumbnails);

		// The item caption
		if ( isset($item['caption']) ) {
			echo "caption";

			if ( preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $item['caption']) ) {
				$caption = preg_replace('/^(?!https?:\/\/)/', 'http://', $item['caption']);
				$caption = "<a href='". esc_attr($caption) ."'>". $item['caption'] ."</a>\n";
				
			} else
				$caption = $item['caption'];
				
			$output .= $this->container($caption, array('name' => 'p', 'class' => 'caption'));
		}							
		
		if ( $descript != null || $properties != null ) {
												
			if ( $descript != null )
				$output .= $descript;
	
			if ( $properties != null )
				$output .= $properties;
						
		}

		$container = array(
			'name' => 'blockquote',
			'class' => null,
			);

		return $this->container($output, $container);
	}
		
	/**
	 * Searches text to find urls and make them into hyperlinks.
	 * 
	 * @since 0.9.0
	 * 
	 * @param string $haystack The text to search through.
	 * 
	 * @return string The HTML to output.
	 */
	public function find_links( $haystack )
	{
		// Our pattern to find links.
		$pattern = '{\b((https?|ftp)://[-a-zA-Z0-9+&@#/%?=~_|!:,.;]*[a-zA-Z0-9+&@#/%=~_|])}';

		// The hyperlink to be.
		$replacement = "<a href='$1'>\\1</a>";

		// Find the links.
		$output = preg_replace($pattern, $replacement, $haystack);

		return $output;
	}

	/**
	 * 
	 */
	public function thumbnail( $item, $show_thumbnails )
	{
		if ( ! $show_thumbnails || ! isset($item['picture']) )
			return null;

		$output = "<img src='". esc_attr($item['picture']) ."' class='thumbnail alignleft' />\n";

		if ( isset($item['link']) )
			$output = "<a href='". esc_attr($item['link']) ."' class='block-link' target='_blank'>$output</a>\n";

		return $output;
	}

	/**
	 * Gets event properties ready to output.
	 * 
	 * @since 0.9.0
	 * 
	 * @param array $item The items array.
	 * 
	 * @return string The HTML output.
	 */
	public function event_properties( $item )
	{

		if ( ! isset($item['properties']) ) 
			return null;

		$count = count($item['properties']);
		
		$output = null;
		
		foreach( $item['properties'] as $key => $property ) {
			
			if ( ! $this->is_date($property['text']) ) {
				
				$date = $this->format_date($date, 'event');

				$output .= ( $date != false ) ? $date : $property['text'];
				
			} else
				$output .= $property['text'];

			// If there's another line of text
			if ( $key != ($count - 1) )
				$output .= "<br />\n";
								
		}

		return $output;
	}

	/**
	 * Count the number of comments
	 * 
	 * @since 0.9.0
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
	 * Create the URL to see the item on facebook
	 * 
	 * @since 0.9.0
	 * 
	 * @param array $item The item's array.
	 * 
	 * @return string The URL.
	 */
	public function item_url( $item )
	{
		// Split the user/page id from the item id.
		$id = preg_split('/_/', $item['id']);

		// The URL.
		$output = 'http://www.facebook.com/'. $id[0] .'/posts/'. $id[1];

		return $output;
	}

	/**
	 * 
	 */
	public function meta( $item_url, $published, $comments )
	{

		$container = array(
			'name' => 'p',
			'class' => 'fb-date fb-comments',
			);

		// Link to the item on Facebook.
		// Contains the published date and the # of comments.
		$output = "<a href='". $item_url ."' target='_blank' class='quiet' title='". __('See this post on Facebook') ."'>". $published ." &bull; ". $comments ."</a>";

		$output = $this->container($output, $container);

		return $output;
	}
}


/* - - - - - -
	
	Used to display a feed without you having to mess with the class.
	If you're displaying more than one feed I suggest using the class 
	and not this function.
		
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed( $feed_id = null, $args = array() )
{
	
	$facebook = new ffg();
	
	$facebook = $facebook->feed($feed_id, $args);
	
	return $facebook;
	
}


/* - - - - - -
	
	Add Shortcode tag.
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function fb_feed_shortcode( $args, $feed_id = null )
{
	
	$args['echo'] = false;
	
	$facebook = new ffg();
	$facebook = $facebook->feed($feed_id, $args);
	
	return $facebook;
	
}
add_shortcode('fb_feed', 'fb_feed_shortcode');


/* - - - - - -
	
	Add default style
	
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
function ffg_add_style()
{
	
	$options = get_option('ffg_options');
	
	// See if we should show a style sheet.
	if ( $options['style_sheet'] == false )
		return false;

	// Get the stylesheet we should use and make it's url.
	$style_url = plugins_url($options['style_sheet'], __FILE__);

	// Tell wp to use the stylesheet.
	wp_register_style('ffg_style', $style_url, array(), FFG_VERSION);
	wp_enqueue_style( 'ffg_style');

}
add_action('wp_print_styles', 'ffg_add_style');
?>