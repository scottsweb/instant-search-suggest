<?php
/*
Plugin Name: Instant Search & Suggest
Plugin URI: https://github.com/scottsweb/instant-search-suggest
Description: Instant WordPress search with search term suggestions for tags, categories and titles.
Version: 2.1
Author: Scott Evans
Author URI: http://scott.ee
Text Domain: wpiss
Domain Path: /assets/languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

*/

// define plugin contstants
define( 'WPISS_JS_URL', plugins_url( '/assets/js', __FILE__ ) );
define( 'WPISS_CSS_URL', plugins_url( '/assets/css', __FILE__ ) );
define( 'WPISS_IMAGES_URL', plugins_url( '/assets/images', __FILE__ ) );
define( 'WPISS_PATH', dirname( __FILE__ ) );
define( 'WPISS_BASE', plugin_basename( __FILE__ ) );
define( 'WPISS_FILE', __FILE__ );

/**
 * Boot the plugin
 *
 * @return void
 */
function wpiss_init() {

	// load translations
	load_plugin_textdomain( 'wpiss', false, dirname( WPISS_BASE ) . '/assets/languages' );

	// handle ajax requests
	include WPISS_PATH . '/assets/inc/iss-ajax.php';

	// admin and theme functionality
	if ( !is_admin() ) { include WPISS_PATH . '/assets/inc/iss-theme.php'; }
	if ( is_admin() ) { include WPISS_PATH . '/assets/inc/iss-admin.php'; }
}
add_action( 'init', 'wpiss_init' );

/**
 * Setup default plugin settings on activation
 *
 * @return void
 */
function wpiss_defaults() {

	$tmp = get_option( 'wpiss_options' );

	if ( !is_array( $tmp ) ) {
		$arr = array(
			"wpiss_txt_content" 		=> "#content",
			"wpiss_suggestion_count" 	=> "all",
			"wpiss_chk_post_page"	 	=> "1",
			"wpiss_chk_post_post" 		=> "1",
			"wpiss_chk_tax_category" 	=> "1",
			"wpiss_chk_tax_post_tag" 	=> "1",
		);
		update_option( 'wpiss_options', $arr );
	}
}
register_activation_hook( WPISS_FILE, 'wpiss_defaults' );

/**
 * Delete plugin option on deactivation
 *
 * @return void
 */
function wpiss_delete_options() {
	delete_option( 'wpiss_options' );
}
register_uninstall_hook( WPISS_FILE, 'wpiss_delete_options' );
