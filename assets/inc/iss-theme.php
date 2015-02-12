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
	wp_enqueue_script( 'iss-suggest', WPISS_JS_URL.'/jquery.suggest.js', array( 'jquery' ), filemtime( WPISS_PATH.'/assets/js/jquery.suggest.js' ), false );
	wp_enqueue_script( 'iss', WPISS_JS_URL.'/iss.js', array( 'iss-suggest' ), filemtime( WPISS_PATH.'/assets/js/iss.js' ), false );
	wp_localize_script( 'iss', 'iss_options', array(
		'iss_suggest_url' 	=> add_query_arg( array( 'action' => 'iss_suggest', '_wpnonce' => wp_create_nonce( 'iss_suggest' ) ), untrailingslashit( admin_url( 'admin-ajax.php' ) ) ),
		'iss_instant_url' 	=> add_query_arg( array( 'action' => 'iss_instant', '_wpnonce' => wp_create_nonce( 'iss_instant' ) ), untrailingslashit( admin_url( 'admin-ajax.php' ) ) ),
		'iss_style' 		=> $options['wpiss_style'],
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
 * Output a filterable jQuery template for rendering search suggestions
 *
 * @return string jQuery template
 */
function wpiss_suggest_template() {

	$template  = '';
	$template .= '<script type="text/html" id="template">';
	$template .= '<li class="iss-result">';
	$template .= '<div data-content="author"></div>';

	/*	<div data-content="date"></div>
		<img data-src="authorPicture" data-alt="author"/>
		<div data-content="post"></div>
	</script>*/

	$template .= '</li>';

	return apply_filters( 'wpiss_tempalte', $template );
}
add_action( 'wp_footer', 'wpiss_suggest_template');
