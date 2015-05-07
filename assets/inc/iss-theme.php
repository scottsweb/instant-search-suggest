<?php

/**
 * Enqueue required JavaScript and CSS for Instant Search & Suggest
 *
 * @return void
 */
function wpiss_theme_init() {

	// get plugin options
	$options = get_option( 'wpiss_options' );
	$content = $options['wpiss_txt_content'];

	// javascript
	wp_enqueue_script( 'iss-suggest', WPISS_JS_URL.'/jquery.suggest.js', array( 'jquery' ), filemtime( WPISS_PATH.'/assets/js/jquery.suggest.js' ), true );
	wp_enqueue_script( 'mustache', WPISS_JS_URL.'/mustache.js', array( 'jquery' ), filemtime( WPISS_PATH.'/assets/js/mustache.js' ), true );
	wp_enqueue_script( 'iss', WPISS_JS_URL.'/iss.js', array( 'iss-suggest' ), filemtime( WPISS_PATH.'/assets/js/iss.js' ), true );
	wp_localize_script( 'iss', 'iss_options', array(
		'iss_suggest_url' 	=> add_query_arg( array( 'action' => 'iss_suggest', '_wpnonce' => wp_create_nonce( 'iss_suggest' ) ), untrailingslashit( admin_url( 'admin-ajax.php' ) ) ),
		'iss_instant_url' 	=> add_query_arg( array( 'action' => 'iss_instant', '_wpnonce' => wp_create_nonce( 'iss_instant' ) ), untrailingslashit( admin_url( 'admin-ajax.php' ) ) ),
		'iss_content' 		=> ( $content ? $content : 0 ),
		'iss_magic' 		=> ( isset( $options['wpiss_magic'] ) ? 1 : 0 ),
	) );

	// css
	// if style is duplicated in the theme load the them version, else load the default plugin style
	if ( file_exists( STYLESHEETPATH.'/iss.css' ) ) {
		wp_enqueue_style( 'iss', get_stylesheet_directory_uri() . '/iss.css', array(), filemtime( STYLESHEETPATH . '/iss.css' ), 'all' );
	} else {
		wp_enqueue_style( 'iss', WPISS_CSS_URL . '/iss.css', array(), filemtime( WPISS_PATH . '/assets/css/iss.css' ), 'all' );
	}
}
add_action( 'wp_loaded', 'wpiss_theme_init' );

/**
 * Output a filterable jQuery template for rendering post type search suggestions
 *
 * @return string jQuery template
 */
function wpiss_post_template() {

	$template  = '
	<script type="x-tmpl-mustache" id="wpiss-post-template">
		<li class="iss-result">
			{{#image}}
				<img src="{{image}}" width="50" height="50" />
			{{/image}}
			{{{title}}}
			<span class="iss-sub">{{postdate}}</span>
			<span class="iss-sub">{{posttype}}</span>
		</li>
	</script>';

	echo apply_filters( 'wpiss_post_tempalte', $template );
}
add_action( 'wp_footer', 'wpiss_post_template');

/**
 * Output a filterable jQuery template for rendering taxonomy search suggestions
 *
 * @return string jQuery template
 */
function wpiss_taxonomy_template() {

	$template  = '
	<script type="x-tmpl-mustache" id="wpiss-taxonomy-template">
		<li class="iss-result">
			{{{title}}}
			<span class="iss-sub">{{taxonomy}} ({{count}})</span>
		</li>
	</script>';

	echo apply_filters( 'wpiss_taxonomy_template', $template );
}
add_action( 'wp_footer', 'wpiss_taxonomy_template');

/**
 * Filter search queries to match settings
 *
 * @return object
 */
function wpiss_pre_get_posts( $query ) {

	if ( $query->is_main_query() && is_search() ) {

		// grab settings and build post type array
		$options = get_option( 'wpiss_options' );
		$post_query = array();
		$args = array(
			'public' => true,
			'show_ui' => true
		);
		$output = 'objects';
		$operator = 'and';
		$post_types = get_post_types( $args, $output, $operator );

		if ( !empty( $post_types ) ) {

			foreach ( $post_types as $post_type ) {

				if ( isset( $options['wpiss_chk_post_' . $post_type->name] ) ) {
					$post_query[] = $post_type->name;
				}
			}
			$query->set( 'post_type', $post_query );
		}
	}

	return $query;
}
add_filter( 'pre_get_posts', 'wpiss_pre_get_posts' );
