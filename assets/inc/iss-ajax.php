<?php

/***************************************************************
* Function wpiss_header
* Catch ajax action for instant search.
***************************************************************/

add_action('template_redirect', 'wpiss_header', -1);

function wpiss_header() {
	
	if (isset($_GET['action'])) {
	
		switch($_GET['action']) {
		
			case 'iss_instant': 
				
				// verify different _wpnonce value in 3.3+
				if (get_bloginfo('version') >= '3.3') { 
					$nonce = $_REQUEST['_wpnonce'];
				} else {
					$nonce = $_REQUEST['amp;_wpnonce'];
				}
				
				if (is_search() && wp_verify_nonce($nonce, 'iss_instant')) {

					// grab our settings
					$options = get_option('wpiss_options');
					
					// load the phpQuery library if not already loaded
					if (!class_exists('phpQueryObject')) {
						include(WPISS_PATH . '/assets/lib/class-phpquery.php');
					}
					
					// query the results page and return for instant search - use curl if available
					if (function_exists('curl_init')) {
						$curl = curl_init(); 
						curl_setopt($curl, CURLOPT_URL, get_bloginfo('url').'/?s='.urlencode(trim(stripslashes($_GET['s']))));  
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
						curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);  
						$search = curl_exec($curl);  
						curl_close($curl);  
						$html = $search;
					} else {				
						$html = file_get_contents(get_bloginfo('url').'/?s='.urlencode(trim(stripslashes($_GET['s']))));
					}
				
					phpQuery::newDocument($html);
					$tempcontent = pq($options['wpiss_txt_content']);
					$content = $tempcontent->html();
					
					echo $content;
					
					// clean up
					unset($html);
					unset($content);

				}
				
				exit;

			break;
			
			case 'iss_suggest': 
				
				// verify different _wpnonce value in 3.3+
				if (get_bloginfo('version') >= '3.3') { 
					$nonce = $_REQUEST['_wpnonce'];
				} else {
					$nonce = $_REQUEST['amp;_wpnonce'];
				}
				
				if (wp_verify_nonce($nonce, 'iss_suggest')) {
					
					$results = array();
					
					// grab our settings
					$options = get_option('wpiss_options');
				
					// clean up the query
					$s = trim(stripslashes($_GET['q']));
					
					// post types
					$post_query = array();									
					if( function_exists( 'get_post_types' ) ) {
					
				        $args = array('public' => true, 'show_ui' => true); 
				        $output = 'objects';
				        $operator = 'and';
				        $post_types = get_post_types( $args, $output, $operator );
				
				        foreach($post_types as $post_type) {
				        					        	
				            if (isset($options['wpiss_chk_post_' . $post_type->name])) {
				            	$post_query[] = $post_type->name;
				            }
				            
				        }
				    } else {
				    
				    	if ($options['wpiss_chk_post_post']) { $post_query[] = 'post'; }
				    	if ($options['wpiss_chk_post_page']) { $post_query[] = 'page'; }
				    	
				    }
					
					if (!empty($post_query)) {
					
						$query_args = array('s' => $s, 'post_status' => 'publish', 'post_type' => $post_query);
						$query = new WP_Query($query_args);
						
						if ( ! empty($query->posts) ) {
						
							foreach ( $query->posts as $post ) {

								if (function_exists('has_post_thumbnail')) {
									if (has_post_thumbnail($post->ID)) {
										$post_image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID, 'thumbnail'));	
									} else {
										unset($post_image);
									}
								}

								// get the categories
								$categories = '';
								foreach(get_the_category($post->ID) as $category) { 
								   $categories .= $category->cat_name . ', '; 
								}
								$categories = rtrim($categories, ', ');
																
								$results[] = array(
									'title' => strip_tags($post->post_title), 
									'permalink' => get_permalink($post->ID), 
									'postdate' => wpiss_get_time($post->ID),
									'posttype' => $post_types[$post->post_type]->labels->singular_name,
									'categories' => $categories,
									'type' => 'post',
									'image' => (isset($post_image) ? $post_image[0] : 0) 
								);
							}
						}
					}

					// taxononomies
					$tax_query = array();									
					if( function_exists( 'get_taxonomies' ) ) {
					
				        $tax_args = array(); 
				        $tax_output = 'objects';
				        $tax_operator = 'and';
				        $taxonomies = get_taxonomies( $tax_args, $tax_output, $tax_operator );
				
				        foreach($taxonomies as $tax) {
				        					        		        	
				            if (isset($options['wpiss_chk_tax_' . $tax->name])) {
				            	$tax_query[] = $tax->name;
				            }
				            
				        }
				    } else {
				    
				    	if ($options['wpiss_chk_tax_category']) { $tax_query[] = 'category'; }
				    	if ($options['wpiss_chk_tax_post_tag']) { $tax_query[] = 'post_tag'; }
				    	
				    }
					
					if (!empty($tax_query)) {
												
						$terms = get_terms($tax_query, 'search='.$s);
						
						if ( ! empty($terms) ) {
						
							foreach ( $terms as $term ) {
								
								$results[] = array(
									'title' => $term->name, 
									'permalink' => get_term_link($term->name, $term->taxonomy),
									'taxonomy' => $taxonomies[$term->taxonomy]->labels->singular_name,
									'count' => $term->count,
									'type' => 'taxonomy'
								);
							}
						}
					}
					
					// sort and output results
					if ( ! empty($results)) {
						
						if (isset($options['wpiss_suggestion_count']) && $options['wpiss_suggestion_count'] != 'all') {
							if (count($results) > absint($options['wpiss_suggestion_count'])) {
								$more = true;
								$count = count($results);
								$results = array_slice($results, 0, absint($options['wpiss_suggestion_count'])); //only return the max set
							}
						}

						sort($results);
						
						// add a view all if we have more results
						if ($more) {
							$results[] = array(
								'title' => __('View all results', 'wpiss'),
								'permalink' => add_query_arg(array('s' => $s), site_url()),
								'count'	=> $count,
								'type' => 'more'
							);									
						}
						
						echo json_encode($results);
					}
				}
				
				exit;
			
			break;
			
			default: 
								
			break;
		
		}
	}
}

/***************************************************************
* Function wpiss_get_time
* Helper function for caluclation of date and time of supplied post
***************************************************************/

function wpiss_get_time($post_id, $format='') {
		
	// parse a custom stamp format e.g. F jS, Y &#8212; H:i
	if ($format) {
		return get_the_time($format, $post_id);
	}
	
	if ((get_option('date_format') != '') && (get_option('time_format') != '')) {
		return get_the_time(get_option('date_format'), $post_id) . " - " . get_the_time('', $post_id);
	}

	if ((get_option('date_format') != '') && (get_option('time_format') == '')) {
		return get_the_time(get_option('date_format'), $post_id);
	}
}
?>