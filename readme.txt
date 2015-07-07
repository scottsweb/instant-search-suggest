=== Instant Search & Suggest ===
Contributors: scottsweb
Donate link: https://flattr.com/profile/scottsweb
Tags: search, suggestions, instant, google, recommendations, query, find, searching, suggest, ajax, instant-search, search-suggest, speed
Requires at least: 4.0
Tested up to: 4.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Instant WordPress search with search suggestions for tags, categories and titles.

== Description ==

Add Google style instant search and search suggest to your WordPress powered site. This plugin will begin displaying search results as soon as your site visitor starts typing into the search box.

The plugin requires very little configuration and should work out of the box with most themes. The plugin has its own options page to configure the plugin to work with your theme if you require it.

[a plugin by Scott Evans](http://scott.ee/ "WordPress designer and developer")

== Installation ==

To install this plugin:

1. Upload the `instant-search-suggest` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Tweak the settings in `Settings -> Instant Search` if required

Visit [WordPress.org for a comprehensive guide](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation) on in how to install WordPress plugins.

== Frequently Asked Questions ==

= Hooks & Filters =

The plugin has two filters, both of which allow you to customise the search suggestion results. The filters are: `wpiss_post_tempalte` and `wpiss_taxonmy_tempalte`.

You should return a valid [mustache template](https://github.com/janl/mustache.js) to both of these. You can see an example in `assets/inc/iss-theme.php`.

Variables available in your mustache template for posts/cpts are: `title`, `permalink`, `postdate`, `posttype`, `categories` and `image`.

Variables available in your mustache template for taxonomies are: `title`, `permalink`, `taxonomy` and `count`.

= How do I setup Instant Search? =

For around 70% of WordPress themes instant search will just work. If you find that your instant search feature is not working then you will need to tweak the settings from within WordPress.

Head to the administration menu for the plugin within your WordPress admin panel. You will find it in `Settings » Instant Search`.

HTML pages are made up of various sections (usually DIVs) and these sections have unique names to describe what they do. For most WordPress themes the main content area is wrapped in a DIV with an ID of 'content'. Therefore the default setting for Instant Search is "#content". If instant search is not working then we need to change this value to match your current theme layout.

= How do I turn off Instant Search? =

Leave the `Instant Search #id/.class` setting empty to disable instant search.

= What is Magic Mode? =

With Magic Mode enabled the user is automatically transported to the post, page, custom post type or taxonomy they have selected in the suggestions list. With Magic Mode enabled the user is automatically transported to the post, page, custom post type or taxonomy they have selected in the suggestions list.

= How do I turn off search suggestions? =

Uncheck all of the taxonomies and post types in the `Search Suggest` setting.

= Does the plugin support custom taxonomies and post types? =

Yes. You can control which of these appear in the suggestion list from the setting screen.

= How do I customise the look and feel of the suggestions? =

We have purposely left the styles on the suggestion dropdown simple to fit many themes. If you wish to customise the CSS copy the `iss.css` file from the plugin folder (/assets/css/) to the root of your theme and edit the CSS to match your design.

If you wish to change the output of the suggestions please see the hooks and filters section above.

You can also dequeue `iss.css` and move the styles to your own CSS file using: `wp_deregister_style('iss');`

== Screenshots ==

1. Admin interface with settings for Instant Search & Suggest
2. Search suggestions on twentyfifteen

== Changelog ==

= 2.1 =
* Correctly filter search results to match settings


= 2.0 =
* Inital release on WordPress.org