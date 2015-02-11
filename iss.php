<?php
/*
Plugin Name: Instant Search & Suggest
Plugin URI: https://github.com/scottsweb/instant-search-suggest
Description: Instant WordPress search with search term suggestions for tags, categories and titles.
Version: 2.0
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