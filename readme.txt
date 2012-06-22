=== Facebook Feed Grabber ===
Contributors: bonnerl
Donate link: http://www.lucasbonner.com/redirect/donate/facebook-feed-grabber/
Tags: Facebook, Social Networking
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 0.7

Display the feed of a public page or profile. Requires you create a Facebook Application

== Description ==

Retrieve the feed of a *public* Facebook page or profile using the Facebook Graph API and the Facebook PHP SDK. You will need to have or create a Facebook application to use this plugin. Facebook requires it to use their graph api.

Basic usage to display a Facebook feed is to add `[fb_feed]` to a post or page, or add `<?php fb_feed() ?>` to a template file. That will use the default feed and the other default arguments set on the Facebook Feed Grabber options page.

== Installation ==

1. Upload `facebook-feed-grabber/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret.
4. Set your default page id.
5. Place `[fb_feed]` in a post or page or add `<?php fb_feed(); ?>` to your templates. This will use the settings set on the options page.

If you wish to display a feed that isn't set on the options page then use `[fb_feed]101359869934470[/fb_feed]` in a post or page, or use `<?php fb_feed('101359869934470') ?>` in a template file.

Arguments can be passed to fb_feed($feed_id, $args) as an array in $args or as key=value pairs in [fb_feed]. The following are the possible arguments and their default values.

* **echo = true** - boolean ~ Echo the results when true else it returns the results. Only works for `fb_feed()`
* **container = 'div'** - string ~ The element to wrap the feed items in. If NULL then no container is used.
* **container_id => 'fb-feed'** - string ~ The id of the container element. Pass 0 or an empty string to it to not use a container.
* **container_class => 'fb-feed'** - string ~ The class of the container element. Pass 0 or an empty string to it to not use a container.
* **limit => $options['limit']** - boolean ~ Defaults to value of "Limit to Posts From Feed" on the plugins options page.
* **maxitems => $options['num_entries']** - int|string ~ Defaults to value of "Number of Entries" on the options page.
* **show_title => true** - boolean ~ Whether to show the Facebook page title before the feed. Pass 1 for true or 0 for false when using `[fb_feed]`.

= Examples =

To display the feed defined on the options page in a post or page without the page title use,
`[fb_feed show_title=1]`

To do the same in a template file use,
`<?php fb_feed( null, array('show_title' => false) ); ?>`

To display a feed not defined on the options page and not limiting the posts to those from the page being retrieved use this in your post or page,
`[fb_feed container_id='facebook-feed']101359869934470[/fb_feed]`

To do the same in a template file use,
`<?php fb_feed( '101359869934470', array('container_id' => 'facebook-feed') ); ?>`

If you are going to show more that one feed in a template file I suggest doing something like the following,
`<?php
// Call the class to make the initial connection.
$facebook = new ffg();

// Display the first feed using all default settings
$facebook->feed();

// Display a second feed with the id 101359869934470. You should also change the id of the container.
// If you use a different container id you can't use the more specefic second default stylesheet.
$facebook->feed('101359869934470', array('container_id'=>'fb-feed-2'));
?>`

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
* Add ability to load the feed via javascript.
* Add oEmbed support maybe?