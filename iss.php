<?php

/*
	Instant Search & Suggest
	------------------------
	
	Plugin Name: Instant Search & Suggest
	Plugin URI: http://codecanyon.net/item/wordpress-instant-search-and-suggest/308665?ref=scottsweb
	Description: Instant WordPress search with search term suggestions for tags, categories and titles.
	Author: Scott Evans
	Version: 1.8
	Author URI: http://scott.ee

*/
	
	/** define some constants **/
	define('WPISS_JS_URL',plugins_url('/assets/js',__FILE__));
	define('WPISS_CSS_URL',plugins_url('/assets/css',__FILE__));
	define('WPISS_IMAGES_URL',plugins_url('/assets/images',__FILE__));
	define('WPISS_PATH', dirname(__FILE__));
	define('WPISS_BASE', plugin_basename(__FILE__));
	define('WPISS_FILE', __FILE__);
	
	/** load language files **/
	load_plugin_textdomain( 'wpiss', false, dirname(WPISS_BASE) . '/assets/languages' );

	/** load ajax goodness **/
	include(WPISS_PATH . '/assets/inc/iss-ajax.php');
	
	/** load the correct part of the plugin **/
	if (!is_admin()) { include(WPISS_PATH . '/assets/inc/iss-theme.php'); }
	if (is_admin()) { include(WPISS_PATH . '/assets/inc/iss-admin.php'); }
		
?>