<?php

/***************************************************************
* Functions wpiss_defaults & wpiss_delete options
* Register defaults and clean up on plugin uninstall
***************************************************************/

register_activation_hook( WPISS_FILE, 'wpiss_defaults' );
register_uninstall_hook( WPISS_FILE, 'wpiss_delete_options' );

function wpiss_defaults() {

	$tmp = get_option( 'wpiss_options' );

    if ( !is_array( $tmp ) ) {
		$arr = array(

			"wpiss_txt_content" => "#content",
			"wpiss_style" => "text",
			"wpiss_suggestion_count" => "all",
			"wpiss_chk_post_page" => "1",
			"wpiss_chk_post_post" => "1",
			"wpiss_chk_tax_category" => "1",
			"wpiss_chk_tax_post_tag" => "1"

		);
		update_option( 'wpiss_options', $arr );
	}
}

function wpiss_delete_options() {

	delete_option( 'wpiss_options' );

}

/***************************************************************
* Functions wpiss_menu, wpiss_help_screen, wpiss_tips_screen, wpiss_settings, wpiss_register_settings
* Create an administration settings page within WordPress
***************************************************************/

add_action( 'admin_menu', 'wpiss_menu' );

function wpiss_menu() {

	global $wpiss_options_page;

 	$wpiss_options_page = add_options_page( __( 'Instant Search &amp; Suggest', 'wpiss' ), __( 'Instant Search', 'wpiss' ), 'administrator', 'iss-settings', 'wpiss_settings' );

	if ( $wpiss_options_page ) add_action( "load-$wpiss_options_page", 'wpiss_help_screen' );

}

function wpiss_help_screen() {

	global $wpiss_options_page;

	$screen = get_current_screen();

    if ( $screen->id != $wpiss_options_page )
    	return;

	$screen->add_help_tab(
		array(
	        'id'      => 'wpiss-tips',
	        'title'   => __( 'Tips', 'wpiss' ),
	        'callback' => 'wpiss_tips_screen',
	    )
    );

}

function wpiss_tips_screen() {
?>

	<p><?php _e( 'To get the most out of this plugin take note of the following tips:', 'wpiss' ); ?></p>

	<ul>
		<li><?php _e( '<strong>Disable Instant Search:</strong> Leave the "Instant Search #id/.class" empty to disable instant search.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Search Everything:</strong> Improve your search results with the free <a href="http://wordpress.org/extend/plugins/search-everything/">search everything plugin</a>.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Custom CSS:</strong> Copy the iss.css file from the plugin folder (instant-search-suggest/assets/css/) to the root of your theme and edit the CSS to match your design.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Unregister the CSS:</strong> If you wish to unload the plugin CSS file and move the styles to your own CSS, use the WP function  <a href="http://codex.wordpress.org/Function_Reference/wp_deregister_style">wp_deregister_style(\'iss\')</a>.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Attach to custom forms:</strong> Add the class \'.iss\' to any form input to invoke the instant search &amp; suggest behaviour.', 'wpiss' ); ?></li>
	</ul>

<?php
}

add_action( 'admin_init', 'wpiss_register_setting' );

function wpiss_register_setting() {

	register_setting( 'wpiss-settings-group', 'wpiss_options', 'wpiss_validate' );

}

function wpiss_validate( $input ) {

	$input['wpiss_txt_content'] =  wp_filter_nohtml_kses( $input['wpiss_txt_content'] );
	$input['wpiss_style'] = esc_attr( wp_filter_nohtml_kses( $input['wpiss_style'] ) );

	return $input;

}

function wpiss_settings() {
?>
<div class="wrap">

	<div class="icon32" id="icon-options-general"></div>

	<h2><?php _e( 'Instant Search &amp; Suggest', 'wpiss' ); ?></h2>

	<form method="post" action="options.php">
	    <?php settings_fields( 'wpiss-settings-group' ); ?>
		<?php $options = get_option( 'wpiss_options' ); ?>
		<?php if ( !isset( $options['wpiss_suggestion_count'] ) ) $options['wpiss_suggestion_count'] = 'all'; ?>

		<table class="form-table">

			<tr>
				<th scope="row"><?php _e( 'Instant Search #id/.class', 'wpiss' ); ?></th>
				<td>
					<input type="text" size="57" name="wpiss_options[wpiss_txt_content]" value="<?php echo $options['wpiss_txt_content']; ?>" />
					<span class="description"><?php _e( 'The HTML #id or .class of your theme\'s content area. e.g. #content. Leave empty to disable.', 'wpiss' ); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Search Suggest', 'wpiss' ); ?></th>

				<td>

        		<?php

        		if ( function_exists( 'get_post_types' ) ) {

				$args = array(
					'public' => true,
					'show_ui' => true,
					'_builtin' => false
				);
				$output = 'objects';
				$operator = 'and';
				$post_types = get_post_types( $args, $output, $operator );

				// default post types
				$post_types['post']->labels->name = __( 'Posts', 'wpiss' );
				$post_types['post']->name = 'post';
				$post_types['page']->labels->name = __( 'Pages', 'wpiss' );
				$post_types['page']->name = 'page';

				?>

                <?php foreach ( $post_types as $post_type ): ?>

					<label><input name="wpiss_options[wpiss_chk_post_<?php echo $post_type->name; ?>]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_'.$post_type->name] ) ) { checked( '1', $options['wpiss_chk_post_'.$post_type->name] ); } ?> /> <?php echo $post_type->labels->name; ?> (<?php echo $post_type->name; ?>)</label><br />

                <?php endforeach; ?>

	            <?php } else { ?>

					<label><input name="wpiss_options[wpiss_chk_post_post]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_post'] ) ) { checked( '1', $options['wpiss_chk_post_post'] ); } ?> /> <?php _e( 'Posts', 'wpiss' ); ?></label><br />
					<label><input name="wpiss_options[wpiss_chk_post_page]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_page'] ) ) { checked( '1', $options['wpiss_chk_post_page'] ); } ?> /> <?php _e( 'Pages', 'wpiss' ); ?></label><br />

	            <?php } ?>

	            <?php if ( function_exists( 'get_taxonomies' ) ) {

	            $tax_args = array(
					'_builtin' => false
				);
				$tax_output = 'objects';
				$tax_operator = 'and';
				$taxonomies = get_taxonomies( $tax_args, $tax_output, $tax_operator );

	            // default taxonomies
	            $taxonomies['category']->labels->name = __( 'Categories', 'wpiss' );
	            $taxonomies['category']->name = 'category';
	            $taxonomies['post_tag']->labels->name = __( 'Post Tags', 'wpiss' );
	            $taxonomies['post_tag']->name = 'post_tag';

	            ?>

            	<?php foreach ( $taxonomies as $tax ): ?>

					<label><input name="wpiss_options[wpiss_chk_tax_<?php echo $tax->name; ?>]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_'.$tax->name] ) ) { checked( '1', $options['wpiss_chk_tax_'.$tax->name] ); } ?> /> <?php echo $tax->labels->name; ?> (<?php echo $tax->name; ?>)</label><br />

                <?php endforeach; ?>

				<?php } else { ?>

					<label><input name="wpiss_options[wpiss_chk_tax_category]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_category'] ) ) { checked( '1', $options['wpiss_chk_tax_category'] ); } ?> /> <?php _e( 'Categories', 'wpiss' ); ?></label><br />
					<label><input name="wpiss_options[wpiss_chk_tax_post_tag]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_post_tag'] ) ) { checked( '1', $options['wpiss_chk_tax_post_tag'] ); } ?> /> <?php _e( 'Post Tags', 'wpiss' ); ?></label><br />

				<?php } ?>
						<span class="description"><?php _e( 'Which of these would you like to auto suggest during a search? Uncheck all to disable auto suggest.', 'wpiss' ); ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e( 'Suggestion Count', 'wpiss' ); ?></th>
				<td>
					<label>
					<select name="wpiss_options[wpiss_suggestion_count]">
						<option value="5" <?php if ( $options['wpiss_suggestion_count'] == '5' ) { ?>selected="selected"<?php } ?>><?php _e( '5', 'wpiss' ); ?></option>
						<option value="10" <?php if ( $options['wpiss_suggestion_count'] == '10' ) { ?>selected="selected"<?php } ?>><?php _e( '10', 'wpiss' ); ?></option>
						<option value="15" <?php if ( $options['wpiss_suggestion_count'] == '15' ) { ?>selected="selected"<?php } ?>><?php _e( '15', 'wpiss' ); ?></option>
						<option value="20" <?php if ( $options['wpiss_suggestion_count'] == '20' ) { ?>selected="selected"<?php } ?>><?php _e( '20', 'wpiss' ); ?></option>
						<option value="all" <?php if ( $options['wpiss_suggestion_count'] == 'all' ) { ?>selected="selected"<?php } ?>><?php _e( 'All', 'wpiss' ); ?></option>
					</select>
					</label>
					<span class="description"><?php _e( 'Choose a limit for the number of suggestions returned.', 'wpiss' ); ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e( 'Suggestion Style', 'wpiss' ); ?></th>
				<td>
					<label>
					<select name="wpiss_options[wpiss_style]">
						<option value="text" <?php if ( $options['wpiss_style'] == 'text' ) { ?>selected="selected"<?php } ?>><?php _e( 'Text Only (post titles, page titles etc).', 'wpiss' ); ?></option>
						<option value="textstrap" <?php if ( $options['wpiss_style'] == 'textstrap' ) { ?>selected="selected"<?php } ?>><?php _e( 'Text + Extra (post titles, page titles with publish date/taxonomy names).', 'wpiss' ); ?></option>
						<?php if ( current_theme_supports( 'post-thumbnails' ) ) { ?>
						<option value="image" <?php if ( $options['wpiss_style'] == 'image' ) { ?>selected="selected"<?php } ?>><?php _e( 'Images (featured images with extra details as above).', 'wpiss' ); ?></option>
						<?php } ?>
					</select>
					</label>
					<span class="description"><?php _e( 'Choose the look and feel of your suggestion list.', 'wpiss' ); ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e( 'Magic Mode', 'wpiss' ); ?></th>
				<td>
					<label><input name="wpiss_options[wpiss_magic]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_magic'] ) ) { checked( '1', $options['wpiss_magic'] ); } ?> /></label>
					<span class="description"><?php _e( 'With magic mode enabled users will be automatically transported to the selected post, page, custom post type, category/taxonomy.', 'wpiss' ); ?></span>
				</td>
			</tr>

		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wpiss' ) ?>" />
		</p>
	</form>
</div>
<?php
}
