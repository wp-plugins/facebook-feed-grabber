=== Facebook Feed Grabber ===
* changed format_date() to use WP's function human_time_diff().
Contributors: bonnerl
Donate link: http://www.lucasbonner.com/redirect/donate/facebook-feed-grabber/
Tags: Facebook, Social Networking
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: 0.8.2
License: GPLv2 or Later

Allows you to display the feed of a public page or profile on your website. Requires that you create a Facebook Application.

== Description ==

Retrieve the feed of a *public* Facebook page or profile using the Facebook Graph API and the Facebook PHP SDK. You will need to have or create a Facebook application to use this plugin as it is required to use their graph api.

= Features =

* Display 1 or more public Facebook feed from any combination of widgets, shortcode or directly in your theme's PHP.
* Control the number of entries to display.
* Lets you choose to show the feed title.
* Lets you limit to items posted by the feed owner.
* Enable or disable the display of thumbnails.
* Lets you cache the feed to reduce server load.
* Provides a basic style sheet that uses your font/color settings; you can also make your own custom style sheet.
* Allows you to access FB through a Proxy.

= Ways to Use =

* **Widget** | Display a feed using a widget.
* **Shortcode** | Display a feed in a post or page using the shortcode tag `[fb_feed]`.
* **In Your Theme's PHP** | Display a feed anywhere in your theme by adding `<?php fb_feed() ?>` where you wish the feed to be displayed.

== Installation ==

1. Install the Custom Facebook Feed either via the WordPress plugin directory, or by uploading the files to your web server (in the `/wp-content/plugins/` directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret. If don't have a Facebook App then refer to the plugin's [FAQ](http://wordpress.org/plugins/facebook-feed-grabber/faq/) page for more help.
4. Set your default feed id and any other settings you wish to adjust. If you don't know your feed's id then refer to the plugin's [FAQ](http://wordpress.org/plugins/facebook-feed-grabber/faq/) page for more help.

= Ways to Display the Feed =

To display the default feed using the other default settings do any of the following.

* **Widget** Visit the widgets area and add the Facebook Feed Grabber Widget where you wish to display the feed.
* **Shortcode** Add the shortcode tag `[fb_feed]` to any page or post to display the feed from within that page/post.
* **PHP Direct Use** Display a feed anywhere in your theme by adding `<?php fb_feed() ?>` where you wish the feed to be displayed.

= Feed Settings =

If you are displaying more then one feed and want different settings for each feed then the following settings can be adjusted for widgets and shortcode instances.

* **feed_id**=*string* ~ The feed username or id.
* **limit**=*boolean* ~ Whether to limit the feed to posts by the feed author. Pass 1 for true or 0 for false.
* **num_entries**=*int* ~ Limits the number of entries displayed. 
* **show_title**=*boolean* ~ Whether to show the Facebook page title before the feed. Pass 1 for true or 0 for false when using the `[fb_feed]` shortcode tag.
* **show_thumbnails**=*boolean* ~ Show thumbnails when available. Pass 1 for true or 0 for false when using the `[fb_feed]` shortcode tag.

See below for examples.

= Shortcode Examples =

To display a feed with the default settings in a post or page,
`[fb_feed]`

To display a feed changing the Show Title option,
`[fb_feed show_title=1]`

To display a feed not defined on the options page and to change the number of entries to show to 6 use the following,
`[fb_feed num_entries=6]101359869934470[/fb_feed]`

= PHP Example =

You can use the PHP function `fb_feed()` in a theme file to display a feed.

To display a feed with the default settings,
`<?php fb_feed(); ?>`

To display a feed changing the Show Title option,
`<?php fb_feed(null, array('show_title' => '1')); ?>`

To display a feed not defined on the options page and to change the number of entries to show to 6 use the following,
`<?php fb_feed( '101359869934470', array('num_entries' => 6) ); ?>`

If you are going to show more that one feed in a template file and have some PHP knowledge I suggest doing something more like the following,
`<?php
// Call the ffg class to make the initial connection.
$facebook = new ffg();

// Display the first feed using all default settings
$facebook->feed();

// Display a second feed with the id 101359869934470. You should also change the id of the container for one of the feeds.
// If you use a different container id you can't use the more specefic second default stylesheet.
$facebook->feed('101359869934470', array('container_id'=>'fb-feed-2'));
?>`

== Frequently Asked Questions ==

= How do I get a Facebook App ID & Secret? =

To get a Facebook App ID & Secret you will need to,

* Have an active [Facebook](http://www.facebook.com) account.
* Register as as a [Facebook Developer](https://www.facebook.com/developers/apps.php "Facebook Developer Apps").
* [Create](https://developers.facebook.com/apps) your Facebook application using the "Create New App" button in the upper right area.

After you create your application you will be given an App ID and Secret.

= What settings should I use for my new Facebook App? =

Below are some suggested settings for when you create your Facebook App. I (the plugin developer) can't guaranty these for all cases.

Use an App Name and Category that best fits your case. 

* **App Namespace** = This isn't required except in special circumstances beyond this plugin.
* **Sandbox Mode** = Off/Disabled
* **App Domains** = This should be the domain name or subdomain you will be using the plugin on. You can specify more then one if needed.

Those are the settings that you must adjust to use the plugin. Below are some suggested aditional changes.

* Settings > Advanced > **Server IP Whitelist** = If your server has one or more static IP addresses I suggest listing them here.
* Settings > Advanced > **Update Settings IP Whitelist** = If your server has one or more static IP addresses I suggest listing them here.
* Settings > Advanced > **Install Insights** = I suggest disabling this to tighten up on Privacy *(does that exist with the NSA's current activity?)* unless you know what it is and will be using it.
* Settings > Advanced > **Mobile SDK Insights** = I suggest disabling this unless you know what it is and will be using it.

= How do I find my feed's ID? =

**Page Feed**

If you're the admin of the page then you can find the page id by,

* Going to the page.
* Click "Edit Page" followed by "Update Page Info" from the drop down.
* Scroll to the bottom and locate "Facebook Page ID".
* The feed ID is the grayed out number to the right.

Alternatively if you're on the page or users profile turn your attention to the url. If the URL looks like this: *www.facebook.com/Your_Page_Name* then the Page ID is just Your_Page_Name. If your page URL is structured like this: *www.facebook.com/pages/Your_Page_Name/123654123654123*) then the Page ID is actually the number at the end, so in this case *123654123654123*.

= Why isn't my feed displaying anything? =

My first guess is that your content isn't set to be public or you haven't provided a valid Facebook ID.

= Why do I get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." =

Because you have either supplied an invalid App Id & Secret combo or you're trying to access something (like a feed) that you don't have permissions for.

== Screenshots ==

1. The options page.

== Changelog ==

= 0.9.0 =
* Improved flexibility of the default feed field to accept usernames and feed urls. I also now displays the feed name on the options page.
* Updated HTML markup to better utilize HTML5 semantics. 
* Improved Widget feed control. 
* Improved the organization of the options.
* Fixed failure to turn urls into clickable links in post message.
* Changed the locale option to retrieve a list of locales via CURL instead of allow_url_fopen.
* Improved App credential validation. (Added validation when the options page is loaded).
* Improved options upgradability.
* Changed the options page javascript to use Backbone.js and jQuery.
* Moved ffg setup class and hooks to it's own file (ffg-setup.php).
* Moved admin files to an 'admin' folder.
* Improved inline documentation to better match the [WP PHP Documentation Standards](http://make.wordpress.org/core/handbook/inline-documentation-standards/php-documentation-standards/#5-inline-comments).
* Improved text localization.
* Changed the ffg_cache class to be based on static methods and properties.
* changed format_date() to use WP's function human_time_diff().

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
* Changed some variable name to make more sense for those looking at the code.
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

= 0.9.0 =
* Notice: Revised how container arguments are passed through. Please see the inline documentation in facebook-feed-grabber/facebook.php if you custimized the container.
* Notice: Updated the HTML to better utilize HTML5 sementics. This may effect any custom stylesheets.
* Improved the amount of control a widget has over the feed it displays.
* Fixed failure to turn urls into clickable links in post message.
* Changed the locale option to retrieve a list of Facebook locales via CURL instead of allow_url_fopen.
* Improved the organization of the options.
* Improved plugin text localization. (Feedback desired if you're utilizing localization.)
* Improved flexibility of the default feed field to accept usernames and feed urls. I also now displays the feed name on the options page.


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