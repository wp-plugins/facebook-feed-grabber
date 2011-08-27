=== Facebook Feed Grabber ===
Contributors: 
Donate link: 
Tags: Facebook, Social Networking
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 0.4.1
 
Retrieve the feed of a public Facebook page. You will need to have have or create a Facebook application as this plugin uses facebook's graph API.

== Description ==

Retrieve the feed of a public Facebook page using the Facebook Graph API and the Facebook PHP SDK. You will need to have have or create a Facebook application to use this plugin. Facebook requires it to use their graph api.

At this time no default styles are provided for the output of this plugin. I hope to add that in the next version.

If you do not set a valid facebook App Id & Secret you will get a "PHP Fatal error:  Uncaught OAuthException: Invalid OAuth access token signature." when you try to display a feed. You can verify your App Id & Secret from the options page!

== Installation ==

1. Upload `facebook-feed-grabber/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the Facebook Feed Grabber options page and enter your Facebook App Id and Secret.
4. Place `<?php fb_feed( $page_id ); ?>` in your templates.

== Frequently Asked Questions ==

= How do I get a facebook App Id & Secret? =

First you will need a facebook account, then you must register as a facebook developer at https://www.facebook.com/developers/apps.php where you can then create your facebook application.

== Screenshots ==


== Changelog ==

0.4 Has an options page and fb_feed($page_id) function. 

== Upgrade Notice ==


== Next Version ==

In the next version I hope to improve the output html and add a default css style sheet.
