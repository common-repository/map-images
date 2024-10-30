=== Map Images ===
Contributors: Danmorella
Donate link: http://www.danmorella.com/wordpress/?p=749
Tags: map, images, in, posts, with, google, maps, api
Requires at least: 3.0.1
Tested up to: 4.0.1 
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Maps GPS tagged images on a Google map for each post.

== Description ==

This plugin parses one post or a set number of posts looking for all image files with GPS exif data.  It can then append a Google Map to each post or to the page with a marker for each image found.  It can include both locally stored images and remotely stored images.  In order for Google Maps to work properly, a Google Maps API Key must be obtained.  The key is free for a limited number of map generations, 25,000 requests/day at the time of this writing (March 19, 2013).  For more information on obtaining an API Key, visit Google's developer resources (https://developers.google.com/maps/documentation/javascript/tutorial).

== Installation ==

1. Create a `mapimages` directory in the `/wp-content/plugins/` folder.
2. Copy the `mapimages.php` and `admin.php` file to the directory.
3. Activate the plugin through the `Plugins` menu in WordPress.
4. Access the settings from your WordPress dashboard for Map Images.
5. Ensure all fields are completed, and then submit the form.


== Frequently Asked Questions ==

= Why do I need a google maps API key? =

The google maps API limits the number of requests per key for free access.

= The plugin is not working on my server, are there any resources? =

Unfortunately no, there are no resources at this time.  Please email me or leave a comment on the plugin webpage for further assistance.

= The plugin creates a google maps marker for locally hosted images, but not for remotely hosted images? =

Ensure allow_url_fopen is enabled in your webserver's php.ini file.  More information can be obtained at the PHP website (http://php.net/manual/en/features.remote-files.php).

== Screenshots ==

1. screenshot-1.png is an example of the options page.
2. screenshot-2.png is an example of a post with two images taken in different locations and the Google map they generate.

== Changelog ==

= 1.4.2 =
* Bug fix for error "Undefined variable: mapimages_options on line 374".

= 1.4.1 =
* Bug fix for error "wp_enqueue_script was called incorrectly".

= 1.4.0 =
* Bug fix for themes that use the post excerpt instead of the full content.  This fix forces the full content into the excerpt field.

= 1.3.0 =
* Added ability to have one map on a page that shows markers for images in many posts.
* Added click functionality to markers on the map, so now a thumbnail is display as well as a link to the post with the image.

= 1.2.0 =
* Bug fix for GPS calculation that may have resulted in locations up to a kilometer off (special thanks to Mattijn for discovering the bug).

= 1.1.0 =
* Added ability to use shortcodes so maps only appear where author intends.
* Added ability to center and zoom map independent of image locations.

= 1.0.1 =
* Bug fix for pages with multiple posts.
* No maps are added to posts that have no pictures with exif data.

= 1.0 =
* Initial Release.

== Upgrade Notice ==

= 1.4.2 =
This version fixes an issue with missing mapimages_options variable.

= 1.4.1 =
This version fixes an issue with some themes reporting that wp_enqueue_script was called incorrectly.

= 1.4.0 =
This version fixes an issue with themes that use the excerpt rather than the full content of a post by forcing the excerpt to equal the full content.

= 1.3.0 =
This version add some new mapping per page functionality as well as adding a click ability to markers that displays a thumbnail of the mapped image and a link to the specific post.

= 1.2.0 =
This version fixes a bug with GPS calculation that could result in incorrectly mapped locations.

= 1.1.0 =
This version adds some additional customization and placement options for the map.

= 1.0 =
Initial release.

