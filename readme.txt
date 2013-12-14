=== Facebook Feed Grabber ===
Contributors: bonnerl
Donate link: http://www.lucasbonner.com/redirect/donate/facebook-feed-grabber/
Tags: Facebook, Social Networking
Requires at least: 3.3
Tested up to: 3.8
Stable tag: 0.8.4
License: GPLv2 or Later

Allows you to display the feed of a public page or profile on your website. Requires that you create a Facebook Application.

== Description ==

Retrieve the feed of a *public* Facebook page or profile using the Facebook Graph API and the Facebook PHP SDK. You will need to have or create a Facebook application to use this plugin as it is required to use their graph api.

The options let you define a default feed that is used anywhere you call the plugin. You may also specify specific feeds to display in different areas.

= Ways to Use =

* **Widget** Display a feed using a widget.
* **Shortcode** Display a feed in a post or page using the shortcode tag `[fb_feed]`.
* **PHP Direct Use** Display a feed anywhere in your theme by adding `<?php fb_feed() ?>` where you wish the feed to be displayed.

== Installation ==

1. Upload `facebook-feed-grabber/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret. If don't have a Facebook App then head on over to the [Facebook Developers App](https://developers.facebook.com/apps "Facebook Developers") page and create one.
4. Set your default page id and any other settings you wish to adjust.

= Ways to Display the Feed =

To display the default feed using the other default settings do any of the following.

* **Widget** Visit the widgets area and add the Facebook Feed Grabber Widget where you wish to display the feed.
* **Shortcode** Add the shortcode tag `[fb_feed]` to any page or post to display the feed from within that page/post.
* **PHP Direct Use** Display a feed anywhere in your theme by adding `<?php fb_feed() ?>` where you wish the feed to be displayed.

= Advanced Usage =

If you need to display one or more feeds in different locations and you need to vary the settings for each instance this section is for you. Don't let the term "Advanced Usage" scare you.

The following are settings that can be changed for each feed you display. (Currently not available for the widgets)

* **cache_feed** - *int* ~ The number of minutes to cache the feed for. 
	>Defaults to the value of "Cache Feed" from the plugin's options page.
	
* **container** - *string* ~ The element to wrap the feed items in. If NULL then no container is used.
	>Defaults to *'div'*.
	
* **container_id** - *string* ~ The ID of the container element. If left empty or contains 0 then no container ID will be set.
	>Defaults to *'fb-feed'*.
	
* **container_class** - *string* ~ The class of the container element. If left empty or contains 0 then container class will be set. 
	>Defaults to *'fb-feed'*.
	
* **limit** - *boolean* ~ Whether to limit the feed to posts by the feed author. Pass 1 for true or 0 for false when using the `[fb_feed]` shortcode. 
	>Defaults to the value of "Limit to Posts From Feed" from the plugin's options page.
	
* **echo** - *boolean* ~ Echo the results when true else it returns the results. Only works when calling `fb_feed()` in PHP.
	>Defaults to *true*.
	
* **maxitems => $options['num_entries']** - *int* ~ Limits the number of entries displayed. 
	>Defaults to the value of "Number of Entries" from the plugin's options page.
	
* **show_title** - *boolean* ~ Whether to show the Facebook page title before the feed. Pass 1 for true or 0 for false when using the `[fb_feed]` shortcode tag.
	>Defaults to *true*.

Arguments can be passed to `fb_feed($feed_id, $args)` as an array in $args or as key=value pairs in the [fb_feed] shortcode tag. For examples keep reading.

= Examples =

To display the feed defined on the options page in a post or page without the page title use,
`[fb_feed show_title=1]`

To do the same in a template file use,
`<?php fb_feed( null, array('show_title' => false) ); ?>`

To display a feed not defined on the options page, change the max number of entries to show to 6 and change the container ID use the following,
`[fb_feed container_id='facebook-feed' maxitems=6]101359869934470[/fb_feed]`

To do the same in a template file use,
`<?php fb_feed( '101359869934470', array('container_id' => 'facebook-feed', 'maxitems' => 6) ); ?>`

If you are going to show more that one feed in a template file I suggest doing something like the following,
`<?php
// Call the class to make the initial connection.
$facebook = new ffg();

// Display the first feed using all default settings
$facebook->feed();

// Display a second feed with the id 101359869934470. You should also change the id of the container for one of the feeds.
// If you use a different container id you can't use the more specefic second default stylesheet.
$facebook->feed('101359869934470', array('container_id'=>'fb-feed-2'));
?>`

== Frequently Asked Questions ==

= How do I get a Facebook App ID & Secret? =

First you will need a [Facebook](http://www.facebook.com) account, then you must register as a Facebook Developer at [www.facebook.com/developers/apps.php](https://www.facebook.com/developers/apps.php "Facebook Developer Apps") where you can then create your Facebook application. After you create your application you will be given and App ID and Secret.

= How do I find the ID to access my page or profile? =

One way is to go to one of the photo albums from your Facebook page or profile and look at the URL. For example here is the profile pictures album for Rehema Ministries Facebook page.
* [Wordpress](https://www.facebook.com/photo.php?fbid=101360063267784&set=a.101360059934451.1955.101359869934470&type=1&theater "Rehema Ministries dba/In Step Foundation, Kenya")
Notice in the 'set' variable of that link the last set of numbers after the last period. In this case those numbers are '101359869934470'. That should be the id of your page or profile.

= Why isn't my feed displaying anything? =

My first guess is that your content isn't set to be public or you haven't provided a valid Facebook page id.

= Why do I get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." =

Because you have either supplied an invalid App Id & Secret combo or you're trying to access something you don't have permissions for.

== Screenshots ==

1. The options page.

== Changelog ==

= 0.8.4 =
* Fixed the display of shared events to show the date/time and location.

= 0.8.3 =
* Fixed the displayed comment count.

= 0.8.2 =
* Removed call time pass-by-reference for compatibility with PHP 5.4.
* Add Localization. (Beta)

= 0.8.1 =
* Filter out statuses saying you are now friends with Jane Doe.
* Bug Fix: Adds http:// when needed to shared links.
* Bug Fix: You can now specify a different feed for widgets from the default feed.

= 0.8 =
* Added widget to display feed.
* Moved php session start to be run during WP's init action. Makes plugin more proper and compatibile with other plugins.

= 0.7.2 =
* Fixed admin side bug where it didn't load the SDK's after a change in the last version.

= 0.7.1 =
* Fixed bug in cache.php (bad variable reference)
* Updated to latest Facebook SDK
* Added Proxy Support (untested)

= 0.7 =
* Added thumbnail support for links, videos and photo albums.
* Removed the status, link and video post types restriction.
* Updated how it checks to see if there are comments.
* Fixed the plugin so it plays nice with other plugins that are based on the Facebook PHP SDK.
* Updated the Facebook PHP SDK

= 0.6 =
* Changed the functions used to display a feed to be in the class 'ffg'. I will likely leave fb_feed() indefinitely for the bulk of people who are just displaying one feed. Feed back on this would be welcomed.
* Improved the handling of event dates.
* Added [fb_feed] shortcode.
* Added support for shared video links.

= 0.5.2 =
* Fixed bug. The page link displayed before the feed had an invalid link due to getting the page name instead of the page id.
* Changed some varible name to make more sense for those looking at the code.
* Improved the documentation a little.
* Special thanks to Randy Martinsen for bringing the bug and documentation issues to light.

= 0.5.1 =
* Fixed bug. Default page id would not save properly due to using intval()…

= 0.5 =
* Fixed type-o on the options page.
* Changed 'Restore Defaults Upon Reactivation?' to 'Delete Options on Deactivation'.
* Changed `fb_feed()` argument scheme to be `fb_feed( $feed_id, $args )`.
* Moved the options page functions to be in the class ffg_admin().
* Changed HTML output of `fb_feed()` to make more sense.
* Changed `fb_feed()` to show who shared a post when not limited to posts from page.
* Changed `fb_feed()` to show the name of the page feed being retrieved. Can disable this by calling `fb_feed($feed_id, array( 'show_title' => false ))`.
* Secured options by adding esc_attr() to fields on options page.
* Added 'Default Feed' field to options.
* Added a default style sheet.
* Added 'Style Sheet' choice to options.

= 0.4.1 =
* Fixed/Improved the plugin description. 

= 0.4 =
* Has an options page and `fb_feed($feed_id)` function. 

== Upgrade Notice ==

= 0.8.4 =
* Fixed the display of shared events to show the date/time and location.

= 0.8.3 =
* Fixed the displayed comment count.

= 0.8.2 =
* Removed call time pass-by-reference for compatibility with PHP 5.4.
* Add Localization. (Beta)

= 0.8 =
* Adds a widget for displaying a feed.
* Moved php session start to be run during WP's init action. Makes plugin more proper and compatibile with other plugins.

= 0.7.2 =
* Fixed admin side bug where it didn't load the SDK's after a change in the last version. (If you're not going to be verifying your app credentials you could skip this version.)

= 0.7.1 =
* Fixed bug in cache.php (bad variable reference)
* Added Proxy Support (untested)

= 0.7 =
* Added thumbnail support for links, videos and photo albums.
* Removed the status, link and video post types restriction.
* Updated how it checks to see if there are comments.

= 0.6 =
* Added support for shared video links.
* Added [fb_feed] short code.

= 0.5.2 =
* Fixes bug in both 0.5 releases where the page link displayed before the feed had an invalid link due to getting the page name instead of the page id.

= 0.5.1 =
* Fixes bug in 0.5 where the default page wouldn't save correctly from the options panel correctly.

My apologies to those who jumped on 0.5 only to find see bug and upgrade again very soon after. I thought I had already fixed it…

= 0.5 =
* Improved the output of statuses. When you upgrade please be sure to visit the options page and review/update it.

== Known Issues ==
* In some cases the time for a shared event displays in the users timezone but does not account for daylight savings time.

== Next Version ==

In no particular order,

* Add a "like" button.
* Add ability to load the feed via javascript.
* Add oEmbed support maybe?
* Add more styling support.