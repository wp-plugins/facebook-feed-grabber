=== Facebook Feed Grabber ===
Contributors: bonnerl
Donate link: 
Tags: Facebook, Social Networking
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 0.5
 
Retrieve the feed of a public Facebook page. You will need to have have or create a Facebook application as this plugin uses facebook's graph API.

== Description ==

Retrieve the feed of a public Facebook page using the Facebook Graph API and the Facebook PHP SDK. You will need to have have or create a Facebook application to use this plugin. Facebook requires it to use their graph api.

At this time it only displays things marked as a status, link or video.

If you do not set a valid facebook App Id & Secret you will get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." when you try to display a feed. You can verify your App Id & Secret from the options page!

== Installation ==

1. Upload `facebook-feed-grabber/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret.
4. Set your default page id. (optional).
4. Place `<?php fb_feed( $page_id (optional), $args (optional)  ); ?>` in your templates. If you did not set a default page id then you must pass the $page_id to the function.

== Frequently Asked Questions ==

= How do I get a facebook App Id & Secret? =

First you will need a facebook account, then you must register as a facebook developer at https://www.facebook.com/developers/apps.php where you can then create your facebook application.

= Why do I get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." =

Because you have either supplied an invalid App Id & Secret combo or you're to access a private feed.

== Screenshots ==

1. The options page.

== Changelog ==

= 0.5 =
* Fixed type-o on the options page.
* Changed 'Restore Defaults Upon Reactivation?' to 'Delete Options on Deactivation'.
* Changed `fb_feed()` argument scheme to be `fb_feed( $page_id, $args )`.
* Moved the options page functions to be in the class ffg_admin().
* Changed HTML output of `fb_feed()` to make more sense.
* Changed `fb_feed()` to show who shared a post when not limited to posts from page.
* Changed `fb_feed()` to show the name of the page feed being retrieved. Can disable this by calling `fb_feed($page_id, array( 'show_title' => false ))`.
* Secured options by adding esc_attr() to fields on options page.
* Added 'Default Feed' field to options.
* Added a default style sheet.
* Added 'Style Sheet' choice to options.

= 0.4.1 =
* Fixed/Improved the plugin description. 

= 0.4 =
* Has an options page and `fb_feed($page_id)` function. 

== Upgrade Notice ==

= 0.5 =
* Improved the output of statuses. When you upgrade please be sure to visit the options page and review/update it.

== Next Version ==

* Improve the html output by adding support for shared photos and notes.
* Change fb_feed to be in class? (will leave an alias for a versions when I do)
* Add Shortcode access.

== Known Bugs ==

* Options page will not properly save the default page id. Thought i fixed it but still have have it. Of the three of four wordpress instalations I've tested on only one of the them has dont it. Temporary solution is to hard code the page id when calling `fb_feed()`.