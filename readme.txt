=== Facebook Feed Grabber ===
Contributors: bonnerl
Donate link: 
Tags: Facebook, Social Networking
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 0.5.2
 
Display the feed of a public page or profile. Requires you create a Facebook Application
== Description ==

Retrieve the feed of a *public* Facebook page or profile using the Facebook Graph API and the Facebook PHP SDK. You will need to have or create a Facebook application to use this plugin. Facebook requires it to use their graph api.

At this time it only displays things marked by Facebook as a status, link or video.

Basic usage to display a Facebook feed is to add `<?php fb_feed() ?>` to a template file. That will use the default feed and the other default arguments set on the Facebook Feed Grabber options page.

== Installation ==

1. Upload `facebook-feed-grabber/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret.
4. Set your default page id. (optional).
5. Place `<?php fb_feed( $feed_id (optional), $args (optional)  ); ?>` in your templates. If you did not set a default page id then you must pass the $feed_id to the function.

The arguments for fb_feed are as follows.
* $feed_id (optional) Default: NULL ~ The id of the Facebook page who's feed you want to display. When set to null it retrieves the feed id set on the options page. If no feed_id is set in the options and you didn't pass it as a parameter then fb_feed() returns FALSE.
* $args (optional) Default: NULL ~ An array of arguments.

The arguments that can be passed through $args are as follows.
* 'echo' => true // boolean ~ Echos the results when true else it returns the results.
* 'container' => 'div' // string ~ The element to wrap the feed items in. If NULL then no container is used.
* 'container_id' => 'fb-feed' // string ~ The id of the container element.
* 'container_class' => 'fb-feed' // string ~ The class of the container element.
* 'limit' => $options['limit'] // boolean ~ Defaults to value of "Limit to Posts From Feed" on the plugins options page.
* 'maxitems' => $options['num_entries'] // int|string ~ Defaults to value of "Number of Entries" on the options page.
* 'show_title' => true // boolean ~ Whether to show the Facebook page title before the feed.

== Frequently Asked Questions ==

= How do I get a Facebook App Id & Secret? =

First you will need a Facebook account, then you must register as a Facebook developer at https://www.facebook.com/developers/apps.php where you can then create your Facebook application.

= How do I find the id to access my page or profile? =

One way is to go to one of the photo albums from your Facebook page or profile and look at the URL. For example here is the profile pictures album for Rehema Ministries Facebook page.
* [Wordpress](https://www.facebook.com/photo.php?fbid=101360063267784&set=a.101360059934451.1955.101359869934470&type=1&theater "Rehema Ministries dba/In Step Foundation, Kenya")
Notice in the 'set' variable of that link the last set of numbers after the last period. In this case those numbers are '101359869934470'. That should be the id of your page or profile.

= Why isn't fb_feed() displaying anything? =

My first guess is that your content isn't set to be viewable by everyone or you haven't provided a Facebook page id.

= Why do I get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." =

Because you have either supplied an invalid App Id & Secret combo or you're trying to access something you don't have permissions for.

== Screenshots ==

1. The options page.

== Changelog ==

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

= 0.5.2 =
* Fixes bug in both 0.5 releases where the page link displayed before the feed had an invalid link due to getting the page name instead of the page id.

= 0.5.1 =
* Fixes bug in 0.5 where the default page wouldn't save correctly from the options panel correctly.

My apologies to those who jumped on 0.5 only to find see bug and upgrade again very soon after. I thought I had already fixed it…

= 0.5 =
* Improved the output of statuses. When you upgrade please be sure to visit the options page and review/update it.

== Next Version ==

In no particular order,
* Improve the html output by adding support for shared photos and notes.
* Change fb_feed() to be in class? (will leave an alias for fb_feed() for the next few versions when I do)
* Add Shortcode access.
