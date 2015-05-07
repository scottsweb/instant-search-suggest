<?php

/**
 * Catch ajax action for instant search.
 *
 * @return string HTML content
 */
function iss_instant() {

	// grab _wpnonce value
	$nonce = $_REQUEST['_wpnonce'];

	if ( wp_verify_nonce( $nonce, 'iss_instant' ) ) {

		// grab our settings
		$options = get_option( 'wpiss_options' );

		// load the phpQuery library if not already loaded
		if ( !class_exists( 'phpQueryObject' ) ) {
			include WPISS_PATH . '/assets/lib/class-phpquery.php';
		}

		// query the results page and return for instant search - use curl if available
		$remote = wp_remote_get( home_url() . '/?s='.urlencode( sanitize_text_field ( stripslashes( $_GET['s'] ) ) ) );

		if ( !is_wp_error( $remote ) ) {

			$html = $remote['body'];
			phpQuery::newDocument( $html );
			$tempcontent = pq( $options['wpiss_txt_content'] );
			$content = $tempcontent->html();

			echo $content;

			// clean up
			unset( $html );
			unset( $content );
		}
	}

	die();
}
add_action( 'wp_ajax_iss_instant', 'iss_instant' );
add_action( 'wp_ajax_nopriv_iss_instant', 'iss_instant' );

/**
 * Catch ajax action for search suggestions.
 *
 * @return string JSON content
 */
function iss_suggest() {

	// grab _wpnonce value
	$nonce = $_REQUEST['_wpnonce'];

	if ( wp_verify_nonce( $nonce, 'iss_suggest' ) ) {

		// clean up the query
		$s = sanitize_text_field( stripslashes( $_GET['q'] ) );

		// check for the results in cache
		$results = wp_cache_get( 'wpiss_' . sanitize_title_with_dashes( $s ) );

		// no cache so lets create some suggestions
		if ( $results == false ) {

			$results = array();

			// grab our settings
			$options = get_option( 'wpiss_options' );

			// post types
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

			} else {

				if ( $options['wpiss_chk_post_post'] ) { $post_query[] = 'post'; }
				if ( $options['wpiss_chk_post_page'] ) { $post_query[] = 'page'; }

			}

			if ( !empty( $post_query ) ) {

				$query_args = array(
					's' => $s,
					'post_status' => 'publish',
					'post_type' => $post_query
				);
				$query = new WP_Query( $query_args );

				if ( ! empty( $query->posts ) ) {

					foreach ( $query->posts as $post ) {

						if ( function_exists( 'has_post_thumbnail' ) ) {
							if ( has_post_thumbnail( $post->ID ) ) {
								$post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID, 'thumbnail' ) );
							} else {
								unset( $post_image );
							}
						}

						// get the categories
						$categories = '';
						foreach ( get_the_category( $post->ID ) as $category ) {
							$categories .= $category->cat_name . ', ';
						}
						$categories = rtrim( $categories, ', ' );

						$results[] = array(
							'title' => strip_tags( $post->post_title ),
							'permalink' => get_permalink( $post->ID ),
							'postdate' => get_the_time( get_option( 'date_format' ), $post->ID),
							'posttype' => $post_types[$post->post_type]->labels->singular_name,
							'categories' => $categories,
							'type' => 'post',
							'image' => ( isset( $post_image ) ? $post_image[0] : 0 )
						);
					}
				}
			}

			// taxononomies
			$tax_query = array();
			$tax_args = array();
			$tax_output = 'objects';
			$tax_operator = 'and';
			$taxonomies = get_taxonomies( $tax_args, $tax_output, $tax_operator );

			if ( !empty( $taxonomies ) ) {

				foreach ( $taxonomies as $tax ) {

					if ( isset( $options['wpiss_chk_tax_' . $tax->name] ) ) {
						$tax_query[] = $tax->name;
					}
				}
			} else {

				if ( $options['wpiss_chk_tax_category'] ) { $tax_query[] = 'category'; }
				if ( $options['wpiss_chk_tax_post_tag'] ) { $tax_query[] = 'post_tag'; }

			}

			if ( !empty( $tax_query ) ) {

				$terms = get_terms( $tax_query, 'search='.$s );

				if ( ! empty( $terms ) ) {

					foreach ( $terms as $term ) {

						$results[] = array(
							'title' => $term->name,
							'permalink' => get_term_link( $term->name, $term->taxonomy ),
							'taxonomy' => $taxonomies[$term->taxonomy]->labels->singular_name,
							'count' => $term->count,
							'type' => 'taxonomy'
						);
					}
				}
			}
		}

		// sort and output results
		if ( ! empty( $results ) ) {

			// cache output for 10 minutes for everyone
			wp_cache_set( 'wpiss_' . sanitize_title_with_dashes( $s ), $results, '', 300 );

			if ( isset( $options['wpiss_suggestion_count'] ) && $options['wpiss_suggestion_count'] != 'all' ) {
				if ( count( $results ) > absint( $options['wpiss_suggestion_count'] ) ) {
					$more = true;
					$count = count( $results );
					$results = array_slice( $results, 0, absint( $options['wpiss_suggestion_count'] ) ); //only return the max set
				}
			}

			sort( $results );

			// add a view all if we have more results
			if ( isset( $more ) ) {
				$results[] = array(
					'title' => __( 'View all results', 'wpiss' ),
					'permalink' => add_query_arg( array( 's' => $s ), site_url() ),
					'count'	=> $count,
					'type' => 'more'
				);
			}

			echo json_encode( $results );
		}
	}

	die();
}
add_action( 'wp_ajax_iss_suggest', 'iss_suggest' );
add_action( 'wp_ajax_nopriv_iss_suggest', 'iss_suggest' );
