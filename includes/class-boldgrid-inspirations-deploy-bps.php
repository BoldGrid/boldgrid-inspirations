<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Deploy
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Deploy BPS class.
 *
 * This class is responsible for "Built Photo Search" functionality during deployment.
 *
 * @since 1.7.0
 */
class Boldgrid_Inspirations_Deploy_Bps {
	/**
	 * Class property for the asset cache object (only for preview servers).
	 *
	 * @since 1.1.2
	 * @access private
	 * @var object|null
	 */
	private $asset_cache = null;

	/**
	 * Our Boldgrid_Inspirations_Deploy class.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy
	 */
	private $deploy;

	/**
	 * Image placeholders needing images.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var array
	 */
	private $image_placeholders_needing_images;

	/**
	 * Built photo search log.
	 *
	 * @since 1.7.0
	 * @var array
	 */
	public $built_photo_search_log = array();

	/**
	 * Built photo search placement.
	 *
	 * @since 1.7.0
	 * @var array
	 */
	public $built_photo_search_placement;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param Boldgrid_Inspirations_Deploy $deploy
	 */
	public function __construct( $deploy ) {
		$this->deploy = $deploy;

		$this->built_photo_search_log['count'] = 0;

		$this->asset_cache = $this->deploy->asset_manager->get_asset_cache();
	}

	/**
	 * http://stackoverflow.com/questions/10589889/returning-header-as-array-using-curl
	 *
	 * @param string $response
	 *
	 * @return array
	 */
	public function curl_response_arrayify( $response ) {
		$headers = array();
		$file    = array();

		$strpos_rnrn = strpos( $response, "\r\n\r\n" );

		$header_text = substr( $response, 0, $strpos_rnrn );
		$body = substr( $response, $strpos_rnrn );

		foreach ( explode( "\r\n", $header_text ) as $i => $line ) {
			if ( $i === 0 )
				$headers['http_code'] = $line;
				else {
					list ( $key, $value ) = explode( ': ', $line );

					$headers[strtolower( $key )] = $value;
				}
		}

		$file['headers'] = $headers;
		$file['body'] = trim( $body );

		return $file;
	}

	/**
	 *
	 */
	public function deploy() {
		$this->deploy_page_sets_media_find_placeholders();
		$this->deploy_page_sets_media_process_image_queue();
		$this->deploy_page_sets_media_replace_placeholders();
	}

	/**
	 * Gather media information.
	 *
	 * Deploy page sets: Media: Find placeholders.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 */
	public function deploy_page_sets_media_find_placeholders() {
		$boldgrid_configs = $this->deploy->get_configs();

		$pages_and_posts = $this->get_media_pages();

		$api_key_hash = $this->deploy->asset_manager->api->get_api_key_hash();

		$this->image_placeholders_needing_images['bps_build_info'] = array (
			'subcategory_id'     => $this->deploy->subcategory_id,
			'page_set_id'        => $this->deploy->page_set_id,
			'theme_id'           => $this->deploy->theme_id,
			'language_id'        => $this->deploy->language_id,
			'asset_user_id'      => $this->deploy->asset_user_id,
			'key'                => $api_key_hash,
			'current_build_cost' => $this->deploy->current_build_cost,
			'coin_budget'        => $this->deploy->coin_budget,
			'site_hash'          => $this->deploy->site_hash
		);

		foreach ( $pages_and_posts as $k => $page ) {
			// Get all of the images.
			$dom = new DOMDocument();
			@$dom->loadHTML( Boldgrid_Inspirations_Utility::utf8_to_html( $page->post_content ) );
			$images = $dom->getElementsByTagName( 'img' );

			// Keep track of the order in which built_photo_search images appear on the page.
			// For further info, see docBlock for set_built_photo_search_placement()
			$remote_page_id = $this->get_remote_page_id_from_local_page_id( $page->ID );

			$bps_image_position     = 0;
			$asset_image_position   = 0;
			$gallery_image_position = 0;

			// Loop through every image in this page.
			foreach ( $images as $image ) {
				// Reset the placeholder.
				$image_placeholder = array ();

				$asset_id = $image->getAttribute( 'data-imhwpb-asset-id' );

				$built_photo_search = $image->getAttribute( 'data-imhwpb-built-photo-search' );

				$source = $image->getAttribute( 'src' );

				// Take action if we're downloading an asset_id.
				if ( ! empty( $asset_id ) ) {
					$image_placeholder = array (
						'page_id' => $page->ID,
						'asset_id' => $asset_id,
						'asset_image_position' => $asset_image_position
					);

					$asset_image_position ++;
				}

				// Take action if we're downloading an image from "built_photo_search".
				if ( ! empty( $built_photo_search ) && false == $this->deploy->is_author ) {
					// keep track of the number of bps we've requested
					$this->built_photo_search_log['count'] ++;

					// keep track of the src for this bps
					$this->built_photo_search_log['sources'][] = $built_photo_search;

					// get built_photo_search details (query_id | orientation)
					$exploded_bps = explode( '|', $built_photo_search );

					$bps_query_id = $exploded_bps[0];

					$bps_orientation = ! empty( $exploded_bps[1] ) ? $exploded_bps[1] : 'any';

					/*
					 * Get width and height from src url.
					 *
					 * Example $source: https://placehold.it/200x200&text=200x200+(dynamic+image)
					 *
					 * Regular expression match looks for: /###x###
					 */
					preg_match( '/\/([0-9]*)x([0-9]*)/', $source, $matches );
					$width = ! empty( $matches[1] ) ? $matches[1] : null;
					$height = ! empty( $matches[2] ) ? $matches[2] : null;

					$image_placeholder = array (
						'page_id'            => $page->ID,
						'asset_id'           => null,
						'bps_image_position' => $bps_image_position,
						'bps_query_id'       => $bps_query_id,
						'bps_orienation'     => $bps_orientation,
						'bps_width'          => $width,
						'bps_height'         => $height,
						'remote_page_id'     => $remote_page_id
					);

					$bps_image_position ++;
				}

				if ( ! empty( $image_placeholder ) ) {
					$this->image_placeholders_needing_images['by_page_id'][$page->ID][] = $image_placeholder;
				}
			}

			// Take action if we have a [gallery] on the page.
			if ( preg_match_all( '/\[gallery .+?\]/i', $page->post_content, $matches ) ) {
				foreach ( $matches[0] as $index => $match ) {
					preg_match( '/data-imhwpb-assets=\'.*\'/', $match, $data_assets );

					$images = array ();

					if ( preg_match( '/data-imhwpb-assets=\'(.+)\'/', $data_assets[0],
						$asset_images_ids ) ) {
							$images = ( explode( ',', $asset_images_ids[1] ) );
						}

						foreach ( $images as $image_asset_id ) {
							$image_placeholder = array (
								'page_id'                => $page->ID,
								'asset_id'               => $image_asset_id,
								'gallery_image_position' => $gallery_image_position
							);

							$gallery_image_position ++;

							$this->image_placeholders_needing_images['by_page_id'][$page->ID][] = $image_placeholder;
						}
				}
			}

			/*
			 * Find any background images within style tags that need to be downloaded.
			 *
			 * @since 1.4.3
			 */
			foreach( $this->deploy->tags_having_background as $tag ) {

				$tag_position = 0;

				$elements = $dom->getElementsByTagName( $tag );

				foreach ( $elements as $element ) {
					$asset_id = $element->getAttribute( 'data-imhwpb-asset-id' );

					if( empty ( $asset_id ) ) {
						continue;
					}

					$this->image_placeholders_needing_images['by_page_id'][$page->ID][] = array (
						'page_id'              => $page->ID,
						'asset_id'             => $asset_id,
						$tag . '_tag_position' => $tag_position,
					);

					$tag_position++;
				}
			}
		}

		// Get all the bps data from the image server.
		$args = array (
			'key'                               => $api_key_hash,
			'image_placeholders_needing_images' => json_encode( $this->image_placeholders_needing_images ),
			'coin_budget'                       => $this->deploy->coin_budget,
			'is_generic'                        => $this->deploy->is_generic,
		);
		$response_body = $this->deploy->api->get_photos( $args );

		// Validate response:
		if ( empty( $response_body['result']['data'] ) || ( isset(
			$response_body['result']['status'] ) && 200 != $response_body['result']['status'] ) ) {
				return;
			}

			// Set $response_data from the response data:
			$response_data = $response_body['result']['data'];

			// Update our ['by_page_id'] value with that of the API call return.
			$this->image_placeholders_needing_images['by_page_id'] = $response_data;

			// Do one final loop, and create the download urls.
			foreach ( $this->image_placeholders_needing_images['by_page_id'] as $page_id => $images_array ) {
				// If $images_array is empty or is not an array, then skip this iteration:
				if ( empty( $images_array ) || ! is_array( $images_array ) ) {
					continue;
				}

				// Iterate through $images_array:
				foreach ( $images_array as $images_array_key => $image_data ) {
					// Are we downloading an asset?
					if ( isset( $image_data['asset_id'] ) && is_numeric( $image_data['asset_id'] ) ) {
						$download_url = $boldgrid_configs['asset_server'] .
						$boldgrid_configs['ajax_calls']['get_asset'] . '?id=' .
						$image_data['asset_id'] . '&key=' . $api_key_hash;

						$this->image_placeholders_needing_images['by_page_id'][$page_id][$images_array_key]['download_type'] = 'get';
						$this->image_placeholders_needing_images['by_page_id'][$page_id][$images_array_key]['download_url'] = $download_url;
					}

					// Are we downloading a bps?
					if ( isset( $image_data['bps_query_id'] ) ) {
						$download_url = $boldgrid_configs['asset_server'] .
						$boldgrid_configs['ajax_calls']['image_download'];

						/* @formatter:off */
						$download_params = array(
							'key' => $api_key_hash,
							'id_from_provider'         => isset( $image_data['getPhotoAction']['id_from_provider'] ) ? $image_data['getPhotoAction']['id_from_provider'] : null,
							'image_provider_id'        => isset( $image_data['getPhotoAction']['image_provider_id'] ) ? $image_data['getPhotoAction']['image_provider_id'] : null,
							'imgr_image_id'            => isset( $image_data['getPhotoAction']['imgr_image_id'] ) ? $image_data['getPhotoAction']['imgr_image_id'] : null,
							'width'                    => isset( $image_data['bps_width'] ) ? $image_data['bps_width'] : null,
							'height'                   => isset( $image_data['bps_height'] ) ? $image_data['bps_height'] : null,
							'orientation'              => isset( $image_data['bps_orientation'] ) ? $image_data['bps_orientation']  : null,
							'image_size'               => isset( $item['params']['image_size'] ) ? $image_data['params']['image_size']  : null,
							'is_redownload'            => isset( $item['params']['is_redownload'] ) ? $image_data['params']['is_redownload'] : false,
							'user_transaction_item_id' => isset( $item['params']['user_transaction_item_id'] ) ? $image_data['params']['user_transaction_item_id'] : null,
							'boldgrid_connect_key'     => isset( $item['params']['boldgrid_connect_key'] ) ? $image_data['params']['boldgrid_connect_key'] : null,
						);
						/* @formatter:on */

						$this->image_placeholders_needing_images['by_page_id'][$page_id][$images_array_key]['download_type']   = 'post';
						$this->image_placeholders_needing_images['by_page_id'][$page_id][$images_array_key]['download_url']    = $download_url;
						$this->image_placeholders_needing_images['by_page_id'][$page_id][$images_array_key]['download_params'] = $download_params;
					}
				}
			}
	}

	/**
	 * Download media.
	 *
	 * Deploy page sets: Media: Process image queue
	 */
	public function deploy_page_sets_media_process_image_queue() {
		// Return if we have no media to download for pages.
		if ( empty( $this->image_placeholders_needing_images['by_page_id'] ) ) {
			return;
		}

		// Create our image queue.
		foreach ( $this->image_placeholders_needing_images['by_page_id'] as $page_id => $images_array ) {
			// If $images_array is empty or is not an array, then skip this iteration:
			if ( empty( $images_array ) || ! is_array( $images_array ) ) {
				continue;
			}

			foreach ( $images_array as $images_array_key => $image_data ) {
				$image_queue[] = array (
					'download_type'    => isset( $image_data['download_type'] ) ? $image_data['download_type'] : null,
					'download_url'     => isset( $image_data['download_url'] ) ? $image_data['download_url'] : null,
					'download_params'  => isset( $image_data['download_params'] ) ? $image_data['download_params'] : null,
					'post_id'          => isset( $image_data['page_id'] ) ? $image_data['page_id'] : null,
					'images_array_key' => $images_array_key,
				);
			}
		}

		// Using curl_multi_:
		$mh = curl_multi_init();

		global $wp_version;

		$user_agent = 'WordPress/' . $wp_version . '; ' . get_site_url();

		// If $image_queue is empty, then return:
		if ( empty( $image_queue ) ) {
			return;
		}

		foreach ( $image_queue as $image_key => $image_data ) {
			// If image caching is enabled, then check cache.
			if ( null !== $this->asset_cache ) {
				// Create an array to be used to set a cache id.
				if ( isset( $image_data['bps_query_id'] ) ) {
					$cache_array = array (
						'id_from_provider' => $image_data['download_params']['id_from_provider'],
						'image_provider_id' => $image_data['download_params']['image_provider_id'],
						'imgr_image_id' => $image_data['download_params']['imgr_image_id'],
						'width' => $image_data['download_params']['width'],
						'orientation' => $image_data['download_params']['orientation'],
						'image_size' => $image_data['download_params']['image_size']
					);
				} else {
					$cache_array = $image_data;
				}

				// Set the cache id.
				$image_queue[$image_key]['cache_id'] = $this->asset_cache->set_cache_id(
					$cache_array );

				// Try to get the $response from cache.
				if ( ! empty( $image_queue[$image_key]['cache_id'] ) ) {
					$response[$image_key] = $this->asset_cache->get_cache_files(
						$image_queue[$image_key]['cache_id'] );
				}
			}

			// If there was no cached response, then queue the download.
			if ( empty( $response[$image_key] ) ) {
				// Using curl_multi_:
				${'ch' . $image_key} = curl_init();

				curl_setopt( ${'ch' . $image_key}, CURLOPT_URL, $image_data['download_url'] );
				curl_setopt( ${'ch' . $image_key}, CURLOPT_HEADER, true );
				curl_setopt( ${'ch' . $image_key}, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( ${'ch' . $image_key}, CURLOPT_USERAGENT, $user_agent );

				if ( 'post' == $image_data['download_type'] &&
					! empty( $image_data['download_params'] ) ) {
						curl_setopt( ${'ch' . $image_key}, CURLOPT_POST, true );
						curl_setopt( ${'ch' . $image_key}, CURLOPT_POSTFIELDS,
						http_build_query( $image_data['download_params'] ) );
					}

					curl_multi_add_handle( $mh, ${'ch' . $image_key} );

					$image_queue[$image_key]['cached'] = false;
			} else {
				$image_queue[$image_key]['cached'] = true;
			}
		}

		// If any curl handle was added to the $mh handle, then get data from curl_multi_.
		if ( isset( $mh ) ) {
			// Using curl_multi_.
			$still_running = null;

			do {
				$mrc = curl_multi_exec( $mh, $still_running );
			} while ( $still_running > 0 );

			while ( $still_running && CURLM_OK == $mrc ) {
				if ( curl_multi_select( $mh ) != - 1 ) {
					do {
						$mrc = curl_multi_exec( $mh, $still_running );
					} while ( $still_running > 0 );
				}
			}

			foreach ( $image_queue as $image_key => $image_data ) {
				if ( empty( $response[$image_key] ) ) {
					$response[$image_key] = curl_multi_getcontent( ${'ch' . $image_key} );

					curl_multi_remove_handle( $mh, ${'ch' . $image_key} );
				}
			}
		}

		// Check responses.
		foreach ( $image_queue as $image_key => $image_data ) {
			if ( isset( $response[$image_key]['headers']['z-filename'] ) ) {
				$arrayify = $response[$image_key];
			} else {
				$arrayify = $this->curl_response_arrayify( $response[$image_key] );
			}

			// If we did not receive a filename in the headers, then log and skip.
			if ( empty( $arrayify['headers']['z-filename'] ) ) {
				error_log( 'Failed to download image during deployment, ["headers"]["z-filename"] was empty.' );
				continue;
			} else {
				// If appplicable, save to cache.
				if ( null !== $this->asset_cache && ! empty( $image_data['cache_id'] ) && ! $image_data['cached'] ) {
					$this->asset_cache->save_cache_files( $image_data['cache_id'], $arrayify );
				}
			}

			$attachment_data = $this->deploy->asset_manager->attach_asset(
				array (
					'headers' => $arrayify['headers'],
					'body' => $arrayify['body'],
					'post_id' => $image_data['post_id'],
					'featured_image' => false,
					'return' => 'all',
					'add_meta_data' => ( isset(
						$this->image_placeholders_needing_images['by_page_id'][$image_data['post_id']][$image_data['images_array_key']]['gallery_image_position'] ) )
				) );

			$this->deploy->messages->print_image( $attachment_data['attachment_id'] );

			$attachment_url = $attachment_data['uploaded_url'];

			/**
			 * Filter the url to replace placeholder url with.
			 *
			 * The image may have originally been:
			 * https://placehold.it/200x200&text=200x200+(dynamic+image)
			 *
			 * If we were able to parse a width and height from the image url (IE 200px by 200px), they
			 * will be stored in $image_data['download_params']['width'] and ['height']. If we have
			 * those attributues, we'll apply this filter that resizes the downloaded image to the dimensions
			 * specified by the author in the "placehold.it" image url.
			 *
			 * @since 1.4.8
			 *
			 * @see Boldgrid_Inspirations_Deploy_Image::post_process_image
			 *
			 * @param int $attachment_data['attachment_id']
			 * @param int $image_data['download_params']['width']
			 * @param int $image_data['download_params']['height']
			 */
			if ( ! empty( $image_data['download_params']['width'] ) && ! empty( $image_data['download_params']['height'] ) ) {
				$attachment_url = apply_filters( 'boldgrid_deploy_post_process_image', $attachment_data['attachment_id'], $image_data['download_params']['width'], $image_data['download_params']['height'] );
			}

			// Update our data...
			$this->image_placeholders_needing_images['by_page_id'][$image_data['post_id']][$image_data['images_array_key']]['attachment_url'] = $attachment_url;
			$this->image_placeholders_needing_images['by_page_id'][$image_data['post_id']][$image_data['images_array_key']]['attachment_id'] = $attachment_data['attachment_id'];
			$this->image_placeholders_needing_images['by_page_id'][$image_data['post_id']][$image_data['images_array_key']]['asset_id'] = $attachment_data['asset_id'];

			// Update the cost of this build.
			if ( isset( $attachment_data['coin_cost'] ) ) {
				$this->deploy->current_build_cost += $attachment_data['coin_cost'];
			}
		}
	}

	/**
	 * Replace media in pages.
	 *
	 * Deploy page sets: Media: Replace placeholders.
	 *
	 * This method updates a post's content in 2 ways:
	 * # Updating the $dom, then saving the $dom to post_content.
	 * # Updating post_content directly.
	 *
	 * The reason this is done in 2 ways is because the $dom can parse tags, but it cannot parse
	 * WordPress shortcodes.
	 *
	 * # Standard images    $dom
	 * # Built photo images $dom
	 * # Background images  $dom
	 * # Gallery images     post_content
	 */
	public function deploy_page_sets_media_replace_placeholders() {
		$pages_and_posts = $this->get_media_pages();

		foreach ( $pages_and_posts as $k => $page ) {
			$dom = new DOMDocument();
			@$dom->loadHTML( Boldgrid_Inspirations_Utility::utf8_to_html( $page->post_content ) );

			$images = $dom->getElementsByTagName( 'img' );
			$remote_page_id = $this->get_remote_page_id_from_local_page_id( $page->ID );

			$dom_changed = false;
			$content_changed = false;
			$built_photo_search_counter = 0;
			$bps_image_position = 0;
			$asset_image_position = 0;

			foreach ( $images as $image ) {
				$asset_id = $image->getAttribute( 'data-imhwpb-asset-id' );
				$built_photo_search = $image->getAttribute( 'data-imhwpb-built-photo-search' );
				$source = $image->getAttribute( 'src' );

				// Get the image that belongs in this placeholder.
				if ( ! empty( $asset_id ) ) {
					$placeholder = $this->get_placeholder_image( $page->ID, 'asset_image_position', $asset_image_position );
				} elseif ( ! empty( $built_photo_search ) && false === $this->deploy->is_author ) {
					$placeholder = $this->get_placeholder_image( $page->ID, 'bps_image_position', $bps_image_position );
				} else {
					$placeholder = array();
				}

				// Check if we have the information we need, or skip this iteration.
				if ( empty( $placeholder['attachment_url'] ) || empty( $placeholder['asset_id'] ) ) {
					continue;
				}

				$attachment_url = $placeholder['attachment_url'];
				$attachment_id = ( isset( $placeholder['attachment_id'] ) ? (int) $placeholder['attachment_id'] : null );

				/*
				 * Determine our wp-image-## class.
				 *
				 * This class is required if WordPress is to later add the srcset attribute.
				 */
				$new_image_class = null;
				if ( ! empty( $attachment_id ) ) {
					$new_image_class = $this->dom_element_append_attribute( $image, 'class', 'wp-image-' . $attachment_id );
				}

				// If we're downloading an asset_id...
				if ( ! empty( $asset_id ) ) {
					$image->setAttribute( 'src', $attachment_url );

					if ( ! is_null( $new_image_class ) ) {
						$image->setAttribute( 'class', $new_image_class );
					}

					$dom_changed = true;
					$asset_image_position ++;
				}

				// Build photo search.
				if ( ! empty( $built_photo_search ) && false === $this->deploy->is_author ) {
					$this->built_photo_search_log['count'] ++;
					$this->built_photo_search_log['sources'][] = $built_photo_search;

					// Update and save the <img> tag.
					$image->setAttribute( 'src', $attachment_url );
					$image->setAttribute( 'width', $placeholder['bps_width'] );

					if ( $this->deploy->is_preview_server ) {
						$image->setAttribute( 'data-id-from-provider', $placeholder['download_params']['id_from_provider'] );
						$image->setAttribute( 'data-image-provider-id', $placeholder['download_params']['image_provider_id'] );
					}

					if ( ! is_null( $new_image_class ) ) {
						$image->setAttribute( 'class', $new_image_class );
					}

					$this->set_built_photo_search_placement( $remote_page_id,
						$built_photo_search_counter,
						$placeholder['asset_id']
						);

					// Increment our counters.
					$bps_image_position ++;
					$built_photo_search_counter ++;
					$dom_changed = true;
				}
			} // End of foreach images.

			/*
			 * Set background images within the style tag.
			 *
			 * @since 1.4.3
			 */
			foreach ( $this->deploy->tags_having_background as $tag ) {

				$tag_position = 0;

				$elements  = $dom->getElementsByTagName( $tag );

				foreach ( $elements as $element ) {

					$asset_id = $element->getAttribute( 'data-imhwpb-asset-id' );

					if ( empty( $asset_id ) ) {
						continue;
					}

					$placeholder = $this->get_placeholder_image( $page->ID, $tag . '_tag_position', $tag_position );

					$style = $element->getAttribute( 'style' );

					preg_match( '/(background:|background-image:).*(url\()[\'"](.*)[\'"]\)/', $style, $matches );

					if ( empty( $matches ) ) {
						continue;
					}

					// Create our new style tag, update it within the dom, and save post_content.
					$updated_matches_0 = str_replace( $matches[3], $placeholder['attachment_url'], $matches[0] );
					$new_style = str_replace( $matches[0], $updated_matches_0, $style );
					$element->setAttribute( 'style', $new_style );

					$element->setAttribute( 'data-image-url', $placeholder['attachment_url'] );

					$dom_changed = true;
					$tag_position++;
				}
			}

			if ( $dom_changed ) {
				$dom->saveHTML();
				$page->post_content = $this->format_html_fragment( $dom );
				$content_changed = true;
			}

			// Get asset ids for gallery images and swap data with the attachment ids in the shortcode.
			if ( preg_match_all( '/\[gallery .+?\]/i', $page->post_content, $matches ) ) {
				// Create an array of asset_id's to local attachment_id's.
				foreach ( $this->image_placeholders_needing_images['by_page_id'][ $page->ID ] as $image ) {
					$assets[ $image['asset_id'] ] = $image['attachment_id'];
				}

				foreach ( $matches[0] as $index => $match ) {
					preg_match( '/data-imhwpb-assets=\'.*\'/', $match, $data_assets );

					$images = array();

					if ( preg_match( '/data-imhwpb-assets=\'(.+)\'/i', $data_assets[0], $asset_images_ids ) ) {
						$images = ( explode( ',', $asset_images_ids[1] ) );
					}

					$attachment_ids = array();

					foreach ( $images as $asset_id ) {
						if ( ! empty( $assets[ $asset_id ] ) ) {
							$attachment_ids[ $asset_id ] = $assets[ $asset_id ];
						}
					}

					$attribute_value = ' ids="' . implode( ',', $attachment_ids ) . '" ';

					$updated_match = str_ireplace( 'ids=""', $attribute_value, $match );

					$page->post_content = str_ireplace( $match, $updated_match, $page->post_content );
					$content_changed = true;
				}
			}

			if ( $content_changed ) {
				wp_update_post( $page );
			}
		} // End of foreach pages_and_posts.
	}

	/**
	 * Modify a dom element's attribute.
	 *
	 * For example, let's say we want to add a new class to an image, 'my-class'. If we simply set
	 * the image's class to 'my-class', then any other classes previously set will be lost. This
	 * method instead takes into consideration any values currently existing for a given attribute.
	 *
	 * @since 1.0.8
	 *
	 * @param object $dom_item A dom element, gathered elsewhere via a DOMDocument object.
	 * @param string $attribute An attribute of dom element, such as 'class' or 'src'.
	 * @param string $value A value to set for the above attribute.
	 * @return string An updated $value.
	 */
	public function dom_element_append_attribute( $dom_item, $attribute, $value ) {
		$current_value = $dom_item->getAttribute( $attribute );

		if ( ! empty( $current_value ) ) {
			$new_value = $current_value . ' ' . $value;
		} else {
			$new_value = $value;
		}

		return $new_value;
	}

	/**
	 * Strip uneeded markup
	 *
	 * @param DOMDOcument $dom
	 * @return string
	 */
	public function format_html_fragment( $dom ) {
		$html = preg_replace( '/^<!DOCTYPE.+?>/', '',
			str_replace( array (
				'<html>',
				'</html>',
				'<body>',
				'</body>'
			), array (
				'',
				'',
				'',
				''
			), $dom->saveHTML() ) );

		return $html;
	}

	/**
	 * Get media pages.
	 *
	 * Media pages are posts and pages that we just installed and we need to loop through and
	 * replace the images within.
	 *
	 * The code within this method was duplicated in this class several times. As of 1.4, it has
	 * been consolidated into this method.
	 *
	 * @since 1.4
	 *
	 * @return array An array of WP_Post objects.
	 */
	public function get_media_pages() {
		$posts = array();

		$post_params = array (
			'posts_per_page' => -1,
			'post__in'       => $this->deploy->installed_page_ids,
			'post_type'      => array (
				'page',
				'post',
			)
		);

		if ( 'publish' != $this->deploy->post_status ) {
			$post_params['post_status'] = $this->deploy->post_status;
		}

		/*
		 * Only get_posts if we have an array of installed_page_ids.
		 *
		 * Otherwise, we'll end up getting all pages. This would be bad because if there were 100+
		 * pages already existing, we would end up scanning each page, looking at the image tags,
		 * and trying to download those images (which would cause lots of errors).
		 */
		if ( ! empty( $this->deploy->installed_page_ids ) ) {
			$posts = get_posts( $post_params );
		}

		/**
		 * Filter posts in which we download images for.
		 *
		 * @since 1.4
		 *
		 * @param array $posts                    An array of post objects.
		 * @param array $this->deploy->installed_page_ids An array of pages we've installed.
		 */
		$posts = apply_filters( 'boldgrid_deploy_media_pages', $posts, $this->deploy->installed_page_ids );

		return $posts;
	}

	/**
	 * Get image data for an image that will replace a placeholder.
	 *
	 * This method helps solve a long time bug. Images used to be called in this manner:
	 * $this->image_placeholders_needing_images['by_page_id'][$page->ID][$asset_image_position]['attachment_url'];
	 *
	 * The problem is that $asset_image_position in that array is simply an auto incremented key.
	 * It's not actually the $asset_image_position or $bps_image_position like we intended.
	 *
	 * This method loops through all the images until it finds the correct $type and $position.
	 *
	 * @since 1.3.2
	 *
	 * @param  int        $page_id
	 * @param  string     $type Either 'asset_image_position' or 'bps_image_position'.
	 * @param  int        $position
	 * @return array|null
	 */
	public function get_placeholder_image( $page_id, $type, $position ) {
		foreach( $this->image_placeholders_needing_images['by_page_id'][$page_id] as $image ) {
			if( isset( $image[$type] ) && $position === $image[$type] ) {
				return $image;
			}
		}

		return null;
	}

	/**
	 * Get remote page id from local page id.
	 *
	 * @param integer $page_id
	 */
	public function get_remote_page_id_from_local_page_id( $page_id ) {
		foreach ( $this->deploy->installed_page_ids as $remote_page_id => $local_page_id ) {
			if ( $local_page_id == $page_id ) {
				return $remote_page_id;
			}
		}
	}

	/**
	 * Keep track of the order in which built_photo_search images appear on the page.
	 *
	 * Ultimately, we want to know for this images we've dynamically loaded:
	 *
	 * [built_photo_search_placement] => stdClass Object (
	 * [45] => Array
	 * . (
	 * . . [0] => 9558
	 * . . [1] => 10658
	 * . )
	 *
	 * This equates to:
	 *
	 * [built_photo_search_placement] => stdClass Object (
	 * [remote_page_id] => Array
	 * . (
	 * . . [counter] => asset_id
	 * . . [counter] => asset_id
	 * . )
	 */
	public function set_built_photo_search_placement( $remote_page_id, $counter, $asset_id ) {
		$this->built_photo_search_placement[$remote_page_id][$counter] = $asset_id;
	}
}
