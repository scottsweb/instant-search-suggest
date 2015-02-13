<?php

/**
 * Create an administration settings page within WordPress
 *
 * @return void
 */
function wpiss_menu() {

	global $wpiss_options_page;

 	$wpiss_options_page = add_options_page( __( 'Instant Search &amp; Suggest', 'wpiss' ), __( 'Instant Search', 'wpiss' ), 'administrator', 'iss-settings', 'wpiss_settings' );

	if ( $wpiss_options_page ) add_action( "load-$wpiss_options_page", 'wpiss_help_screen' );
}
add_action( 'admin_menu', 'wpiss_menu' );

/**
 * Setup the help tab
 *
 * @return void
 */
function wpiss_help_screen() {

	global $wpiss_options_page;

	$screen = get_current_screen();

	if ( $screen->id != $wpiss_options_page )
		return;

	$screen->add_help_tab(
		array(
			'id'	  => 'wpiss-help',
			'title'   => __( 'Help', 'wpiss' ),
			'callback' => 'wpiss_help_screen_callback',
		)
	);
}

/**
 * Output the help screen
 *
 * @return void
 */
function wpiss_help_screen_callback() {
?>
	<p><?php _e( 'To get the most out of this plugin take note of the following tips:', 'wpiss' ); ?></p>

	<ul>
		<li><?php _e( '<strong>Disable Instant Search:</strong> Leave the "Instant Search #id/.class" empty to disable instant search.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Custom CSS:</strong> Copy the iss.css file from the plugin folder (instant-search-suggest/assets/css/) to the root of your theme and edit the CSS to match your design.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Unregister the CSS:</strong> If you wish to unload the plugin CSS file and move the styles to your own CSS, use the WP function <a href="http://codex.wordpress.org/Function_Reference/wp_deregister_style">wp_deregister_style(\'iss\')</a>.', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Create custom suggestion layouts:</strong> The plugin has two filters for this (see the readme documentation).', 'wpiss' ); ?></li>
		<li><?php _e( '<strong>Attach to custom forms:</strong> Add the class \'.iss\' to any form input to invoke the instant search &amp; suggest behaviour.', 'wpiss' ); ?></li>
	</ul>
<?php
}

/**
 * Register setting array for Instant Search & Suggest
 *
 * @return void
 */
function wpiss_register_setting() {
	register_setting( 'wpiss-settings-group', 'wpiss_options', 'wpiss_validate' );
}
add_action( 'admin_init', 'wpiss_register_setting' );

/**
 * Validate saved settings for Instant Search & Suggest
 *
 * @param  array $input
 * @return array
 */
function wpiss_validate( $input ) {

	foreach ( $input as $key => $value ) {

		switch ( $key ) {

			case 'wpiss_suggestion_count':
				$input['wpiss_suggestion_count'] = 	( ( $input['wpiss_suggestion_count'] == '5' || $input['wpiss_suggestion_count'] == '10' || $input['wpiss_suggestion_count'] == '15' || $input['wpiss_suggestion_count'] == '20' ) ? $input['wpiss_suggestion_count'] : 'all' );
			break;

			case 'wpiss_txt_content':
				$input['wpiss_text_content'] = esc_attr( wp_filter_nohtml_kses( $input['wpiss_txt_content'] ) );
			break;

			default:
				$input[$key] = wp_validate_boolean( $input[$key] );
			break;

		}
	}

	return $input;
}

/**
 * Output the settings screen for Instant Search & Suggest
 *
 * @return void
 */
function wpiss_settings() {
?>
<div class="wrap">

	<h2><?php _e( 'Instant Search &amp; Suggest', 'wpiss' ); ?></h2>

	<form method="post" action="options.php">

		<?php settings_fields( 'wpiss-settings-group' ); ?>
		<?php $options = get_option( 'wpiss_options' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Instant Search #id/.class', 'wpiss' ); ?></th>
				<td>
					<input type="text" size="57" name="wpiss_options[wpiss_txt_content]" value="<?php echo esc_attr( $options['wpiss_txt_content'] ); ?>" />
					<span class="description"><?php _e( "The HTML #id or .class of your theme's content area. e.g. #content. Leave empty to disable.", 'wpiss' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Search Suggest', 'wpiss' ); ?></th>
				<td>
					<?php

					$args = array(
						'public' => true,
						'show_ui' => true,
						'_builtin' => false
					);

					$output = 'objects';
					$operator = 'and';
					$post_types = get_post_types( $args, $output, $operator );

					if ( ! empty( $post_types ) ) {

						foreach ( $post_types as $post_type ):

						?>
							<label><input name="wpiss_options[wpiss_chk_post_<?php echo $post_type->name; ?>]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_'.$post_type->name] ) ) { checked( '1', $options['wpiss_chk_post_'.$post_type->name] ); } ?> /> <?php echo $post_type->labels->name; ?> (<?php echo $post_type->name; ?>)</label><br />
						<?php

						endforeach;
					}
					?>
						<label><input name="wpiss_options[wpiss_chk_post_post]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_post'] ) ) { checked( '1', $options['wpiss_chk_post_post'] ); } ?> /> <?php _e( 'Posts (post)', 'wpiss' ); ?></label><br />
						<label><input name="wpiss_options[wpiss_chk_post_page]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_post_page'] ) ) { checked( '1', $options['wpiss_chk_post_page'] ); } ?> /> <?php _e( 'Pages (page)', 'wpiss' ); ?></label><br />
					<?php

					$tax_args = array(
						'_builtin' => false
					);
					$tax_output = 'objects';
					$tax_operator = 'and';
					$taxonomies = get_taxonomies( $tax_args, $tax_output, $tax_operator );

					if ( ! empty( $taxonomies ) ) {

						foreach ( $taxonomies as $tax ):

						?>
							<label><input name="wpiss_options[wpiss_chk_tax_<?php echo $tax->name; ?>]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_'.$tax->name] ) ) { checked( '1', $options['wpiss_chk_tax_'.$tax->name] ); } ?> /> <?php echo $tax->labels->name; ?> (<?php echo $tax->name; ?>)</label><br />
						<?php

						endforeach;
					}
					?>
					<label><input name="wpiss_options[wpiss_chk_tax_category]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_category'] ) ) { checked( '1', $options['wpiss_chk_tax_category'] ); } ?> /> <?php _e( 'Categories (category)', 'wpiss' ); ?></label><br />
					<label><input name="wpiss_options[wpiss_chk_tax_post_tag]" type="checkbox" value="1" <?php if ( isset( $options['wpiss_chk_tax_post_tag'] ) ) { checked( '1', $options['wpiss_chk_tax_post_tag'] ); } ?> /> <?php _e( 'Post Tags (post_tag)', 'wpiss' ); ?></label><br />
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
