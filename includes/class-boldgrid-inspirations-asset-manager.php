<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Asset_Manager
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Asset Manager class.
 */
class Boldgrid_Inspirations_Asset_Manager extends Boldgrid_Inspirations {
	/**
	 * Class property for the asset cache object (only for preview servers).
	 *
	 * @since 1.1.2
	 * @access private
	 *
	 * @var object|null
	 */
	private $asset_cache = null;

	/**
	 * An array used to store data by asset id.
	 *
	 * @since  1.1.5
	 * @access public
	 *
	 * @var    array
	 */
	public $asset_id_data = array();

	/**
	 * A default setup for the boldgrid_asset option.
	 *
	 * @since  1.1.5
	 * @access public
	 *
	 * @var    array
	 */
	public $default_assets = array(
		'plugin' => array(),
		'theme'  => array(),
		'image'  => array(),
	);

	/**
	 * An array containing the different types of assets.
	 *
	 * @since  1.1.5
	 * @access public
	 *
	 * @var    array
	 */
	public $asset_types = array(
		'plugin',
		'theme',
		'image',
	);

	/**
	 * Get the access cache object.
	 *
	 * @since 1.1.2
	 *
	 * @return object
	 */
	public function get_asset_cache() {
		return $this->asset_cache;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 */
	public function __construct() {
		parent::__construct();

		// If Imagick is used, then set the thread limit to 1 to avoid known issues.
		putenv( 'MAGICK_THREAD_LIMIT=1' );

		require_once ABSPATH . 'wp-includes/class-wp-image-editor.php';
		require_once ABSPATH . 'wp-includes/class-wp-image-editor-imagick.php';

		if ( WP_Image_Editor_Imagick::test() ) {
			// The imagick::RESOURCETYPE_THREAD may not be declared, so use the int 6.
			// Silence warning due to static call to non-static method.
			@Imagick::setResourceLimit( 6, 1 );
		}

		// If on a preview server, then instantiate the cache class.
		if ( $this->is_preview_server ) {
			require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-cache.php';
			$this->asset_cache = new Boldgrid_Inspirations_Cache();

			// If cache is disabled, then null the object.
			if ( ! $this->asset_cache->is_cache_enabled() ) {
				$this->asset_cache = null;
			}
		}

		/*
		 * Get boldgrid_asset from the database.
		 *
		 * Because we are setting $this->wp_options_asset in the constructor here, we need to also
		 * add the actions that will update that class if the boldgrid_asset option is updated or
		 * deleted.
		 *
		 * If those actions were added to the add_hooks method, then every time we declarded a new
		 * instance of this class, we would also need to run add_hooks(). Forgetting just once to
		 * add_hooks could cause major problems.
		 *
		 * todo: Rewrite parts of this class so that usage of boldgrid_asset is cleaner.
		 */
		$this->get_wp_options_asset();
		add_action( 'delete_option_boldgrid_asset', array( $this, 'delete_boldgrid_asset' ) );
		add_action( 'update_option_boldgrid_asset', array( $this, 'update_boldgrid_asset' ), 10, 3 );
		add_action( 'delete_option_boldgrid_staging_boldgrid_asset', array( $this, 'delete_boldgrid_asset' ) );
		add_action( 'update_option_boldgrid_staging_boldgrid_asset', array( $this, 'update_boldgrid_asset' ), 10, 3 );
	}

	/**
	 * Add hooks.
	 *
	 * @since 0.3
	 *
	 * @link https://developer.wordpress.org/reference/functions/is_admin/
	 *
	 * @return null
	 */
	public function add_hooks() {
		if ( is_admin() ) {
			add_filter( 'delete_attachment', array( $this, 'delete_attachment' ) );
		}
	}

	/**
	 * Add new asset information to wp_options.
	 *
	 * @param string $type
	 * @param array $details
	 *
	 * @return bool
	 */
	public function add_new_asset( $type, $asset_details ) {

		// Make sure the asset does not already exist
		if ( false == $this->get_asset(
			array (
				'by' => 'asset_id',
				'asset_id' => $asset_details['asset_id']
			) ) ) {
			$this->wp_options_asset[$type][] = $asset_details;

			$this->save_wp_options_asset();

			return true;
		}

		return false;
	}

	/**
	 * Determine if an asset is ready for a decision to publish.
	 *
	 * @param array $asset Array of asset information.
	 */
	public function asset_needs_publish_decision( $asset ) {
		// If the user already purchased this asset.
		if ( ! empty( $asset['purchase_date'] ) ) {
			return false;
		}

		// If it's an attribution required image.
		if ( ! empty( $asset['attribution'] ) ) {
			return false;
		}

		// If they've already made a decision.
		if ( ! empty( $asset['publish_decision_status'] ) ) {
			return false;
		}

		// If we're at this point, then this asset needs a decision (use watermark or cc ).
		return true;
	}

	/**
	 * Attach an asset.
	 *
	 * @param array $params Parameters for the API call.
	 *
	 * @throws Exception
	 *
	 * @param array $params The input parameters.
	 * @return int|array|string
	 */
	public function attach_asset( $params ) {
		// Generate some variables for later use.
		/* @formatter:off */
		$data =					isset( $params['body'] ) 								? $params['body'] 								: null;
		$filename =				isset( $params['headers']['z-filename'] ) 				? $params['headers']['z-filename'] 				: null;
		$asset_type =			isset( $params['headers']['z-asset-type'] ) 			? $params['headers']['z-asset-type'] 			: null;
		$asset_coin_cost =		isset( $params['headers']['z-coin-cost'] ) 				? $params['headers']['z-coin-cost'] 			: null;
		$attribution_license =	isset( $params['headers']['z-attribution-license'] ) 	? $params['headers']['z-attribution-license'] 	: null;
		$attribution_data =		isset( $params['headers']['z-attribution-data'] ) 		? $params['headers']['z-attribution-data'] 		: null;
		$width =				isset( $params['headers']['z-width'] ) 					? $params['headers']['z-width'] 				: '234';
		$height =				isset( $params['headers']['z-height'] ) 				? $params['headers']['z-height'] 				: '234';
		$image_provider_id =	isset( $params['headers']['z-provider-id'] ) 			? $params['headers']['z-provider-id'] 			: null;
		$id_from_provider =		isset( $params['headers']['z-id-from-provider'] ) 		? $params['headers']['z-id-from-provider'] 		: null;
		$orientation =			isset( $params['headers']['z-orientation'] ) 			? $params['headers']['z-orientation'] 			: null;
		$image_size =			isset( $params['headers']['z-image-size'] ) 			? $params['headers']['z-image-size'] 			: null;
		$transaction_item_id =	isset( $params['headers']['z-transaction-item-id'] ) 	? $params['headers']['z-transaction-item-id'] 	: null;
		$transaction_id =		isset( $params['headers']['z-transaction-id'] ) 		? $params['headers']['z-transaction-id'] 		: null;
		/* @formatter:on */

		// Save the image.
		$uploaded = wp_upload_bits( $filename, null, $data );

		if ( $uploaded['error'] ) {
			throw new Exception( $uploaded['error'] );
		}

		$asset_id = $params['headers']['z-asset-id'];

		// Retrieve the file type from the file name.
		$wp_filetype = wp_check_filetype( $uploaded['file'], null );

		// Generate the attachment data.
		/* @formatter:off */
		$attachment = array (
			'post_mime_type' => $wp_filetype['type'],
			'guid' => $uploaded['url'],
			'post_parent' => $params['post_id'],
			'post_title' =>		isset( $params['headers']['z-wp-title'] )		? $params['headers']['z-wp-title']			: '',
			'post_content' =>	isset( $params['headers']['z-wp-description'] )	? $params['headers']['z-wp-description']	: '',
			'post_excerpt' =>	isset( $params['headers']['z-wp-caption'] )		? $params['headers']['z-wp-caption']		: ''
		);
		/* @formatter:on */

		/*
		 * Insert the attachment into the media library.
		 * $attachment_id is the ID of the entry created in the wp_posts table.
		 */
		$attachment_id = wp_insert_attachment( $attachment, $uploaded['file'], $params['post_id'] );

		if ( 0 == $attachment_id ) {
			throw new Exception( 'wp_insert_attachment() ERROR' );
		}

		// Add this new asset to boldgrid_asset in wp_options.
		$asset_details = array (
			'asset_id' => $asset_id,
			'coin_cost' => $asset_coin_cost,
			'name' => $uploaded['file'],
			'purchase_date' => '',
			'download_date' => date( 'Y-m-d H:i:s' ),
			'attribution' => $attribution_data,
			'attribution_license' => $attribution_license,
			'attachment_id' => $attachment_id,
			'width' => $width,
			'height' => $height,
			'image_provider_id' => $image_provider_id,
			'id_from_provider' => $id_from_provider,
			'orientation' => $orientation,
			'image_size' => $image_size,
			'transaction_item_id' => $transaction_item_id,
			'transaction_id' => $transaction_id
		);

		$is_added = $this->add_new_asset( 'image', $asset_details );

		/**
		 * Because we are resizing images before we set them as assets,
		 * we don't need WordPress to resize theme for us.
		 */
		if ( ! $this->is_preview_server || $params['add_meta_data'] ) {
			// Generates metadata for an image attachment,
			// and create a thumbnail and other intermediate sizes of the image attachment based on
			// the sizes defined on the Settings_Media_Screen.
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $uploaded['file'] );

			// Update metadata for the attachment.
			wp_update_attachment_metadata( $attachment_id, $attach_data );
		}

		// Is this a featured image?
		if ( $params['featured_image'] ) {
			/*
			 * The function update_post_meta() updates the value of an existing meta key (custom
			 * field) for the specified post.
			 */
			update_post_meta( $params['post_id'], '_thumbnail_id', $attachment_id );
		}

		/*
		 * ********************************************************************
		 * Determine what data needs to be returned, and return it.
		 * ********************************************************************
		 */

		/*
		 * If we're not attaching this to a post, return the url.
		 * @todo This statement can be incorporated into the switch($return) below.
		 */
		if ( false == $params['post_id'] ) {
			if ( 'all' == $params['return'] ) {
				// Add two more items to $uploaded array before returning it.
				$uploaded['transaction_item_id'] = $transaction_item_id;
				$uploaded['transaction_id'] = $transaction_id;
				$uploaded['attachment_id'] = $attachment_id;

				return $uploaded;
			} else {
				return $uploaded['url'];
			}
		}

		// Determine what needs to be returned from this function call.
		switch ( $params['return'] ) {
			case 'attachment_id' :
				$return_value = $attachment_id;
				break;

			case 'all' :
				$return_value = array (
					'uploaded_url' => $uploaded['url'],
					'attachment_id' => $attachment_id,
					'asset_id' => $asset_id,
					'coin_cost' => $asset_coin_cost,
					'headers' => $params['headers']
				);
				break;

			default :
				$return_value = $uploaded['url'];
		}

		return $return_value;
	}

	/**
	 * Action taken when an attachment is deleted.
	 *
	 * When an attachment is deleted, remove the attachment from the asset array option.
	 *
	 * @since 1.3.9
	 *
	 * @param int $post_id An attachment id.
	 */
	public function delete_attachment( $post_id ) {
		$asset = $this->get_asset(
			array (
				'by' => 'attachment_id',
				'attachment_id' => $post_id
			) );

		if( $asset ) {
			$this->update_asset( array(
				'task' => 'delete_attachment',
				'asset_type' => 'image',
				'attachment_id' => $post_id,
				'asset_id' => $asset[ 'asset_id' ],
			));
		}
	}

	/**
	 * Delete class property when option is deleted.
	 *
	 * When this class is instantiated, we store a copy of boldgrid_asset. If another class / method
	 * deletes that option, we need to keep our local property up to date.
	 *
	 * @since 1.3.1
	 */
	public function delete_boldgrid_asset() {
		$this->wp_options_asset = false;
	}

	/**
	 * This function downloads an image and assigns it to a page/post as an attachment.
	 *
	 * Return: url to raw uploaded image.
	 *
	 * @example http://www.example.com/wp-content/uploads/2014/09/0.340150001410438820.jpg
	 *
	 * @param int $post_id
	 * @param string $featured_image
	 * @param int|array $asset_id
	 * @param string $return (Possible values: url (default), attachment_id, all)
	 * @param string $add_meta_data
	 *
	 * @throws Exception
	 *
	 * @return string|int|array|bool
	 */
	public function download_and_attach_asset( $post_id, $featured_image = null, $asset_id, $return = 'url', $add_meta_data = false ) {
		// Is this an image purchase?
		$is_purchase = ( is_array( $asset_id ) && 'built_photo_search_purchase' == $asset_id['type'] );

		// If we have a transaction id, then set it.
		$transaction_id = isset( $asset_id['params']['transaction_id'] ) ? $asset_id['params']['transaction_id'] : null;

		// If we have an attachment id, then set it.
		$attachment_id = isset( $asset_id['params']['attachment_id'] ) ? ( int ) $asset_id['params']['attachment_id'] : null;

		$is_redownload = ! empty( $asset_id['params']['is_redownload'] );

		/*
		 * Get asset download url.
		 * - Assets will go to /api/asset/get.
		 * - Images will go to /api/image/download.
		 */
		$info = $this->get_asset_server_item_download_info( $asset_id, $transaction_id );

		/*
		 * Reset the max_execution_time.
		 * When called, set_time_limit() restarts the timeout counter from zero.
		 * In other words, if the timeout is the default 30 seconds,
		 * and 25 seconds into script execution a call such as set_time_limit(20) is made,
		 * the script will run for a total of 45 seconds before timing out.
		 * @link http://php.net/manual/en/function.set-time-limit.php
		 */
		// Set the PHP timeout limit to at least 120 seconds.
		set_time_limit(
			( ( $max_execution_time = ini_get( 'max_execution_time' ) ) > 120 ? $max_execution_time : 120 ) );

		// Initialize $image_from_cache.
		$image_from_cache = false;

		// If caching is enabled, try to get the $response from cache.
		if ( null !== $this->asset_cache && ! empty( $info['cache_id'] ) ) {
			$response = $this->asset_cache->get_cache_files( $info['cache_id'] );

			// Check cache response.
			if ( ! empty( $response ) ) {
				// Using cache.
				$image_from_cache = true;
			}
		}

		// If caching is not being used, then download the file.
		if ( ! $image_from_cache ) {
			// Not using cache.

			// File is not in cache, so download it.
			$successful_download = false;
			$download_timeouts = 0;

			// Attempt to retrieve an image, retry if needed.
			// If purchasing an image, then the following successful call will deduct coins.

			while ( ! $successful_download && $download_timeouts < 3 ) {
				switch ( $info['method'] ) {
					case 'get' :
						// all get requests should have an increased timeout.
						$info['arguments']['timeout'] = 10;
						$response = wp_remote_get( $info['url'], $info['arguments'] );
						break;

					case 'post' :
						// all post requests should have an increased timeout.
						$info['arguments']['timeout'] = 10;
						$response = wp_remote_post( $info['url'], $info['arguments'] );
						break;
				}

				// If this is a timeout.
				if ( $response instanceof WP_Error && isset(
					$response->errors['http_request_failed'][0] ) && substr_count(
					$response->errors['http_request_failed'][0], 'Operation timed out' ) > 0 ) {
					$download_timeouts ++;

					// Log.
					error_log(
						__METHOD__ . ': Error: Timeout downloading asset.  ' . print_r(
							array (
								'$asset_id' => $asset_id,
								'$info' => $info
							), true ) );
				} else {
					$successful_download = true;
				}
			}

			// If the download failed, return false.
			if ( ! $successful_download ) {
				return false;
			}
		}

		/*
		 * Validate our downloaded image.
		 *
		 * Validation use to be contained in one if statement. To help with troubleshooting, each
		 * check has been separated.
		 */

		$valid_image = true;

		if ( $response instanceof WP_Error ) {
			error_log( print_r( array(
				'Message' => __METHOD__ . ': Error validating downloaded image, we have a WP_Error.',
				'WP_Error' => $response,
			)));
			$valid_image = false;
		}

		// Check necessary headers are there.
		$check_headers = array( 'z-filename', 'z-asset-id', 'z-asset-type' );
		foreach( $check_headers as $header ) {
			if( ! isset( $response['headers'][$header] ) ) {
				error_log( 'Error validating downloaded image, missing header: ' . $header );
				$valid_image = false;
			}
		}

		if( ! strlen( $response['body'] ) ) {
			error_log( 'Error validating downloaded image, empty $response body.');
			$valid_image = false;
		}

		/*
		 * Determine our response for this failed download.
		 *
		 * Historically, we always returned false. This causes a problem when purchasing images
		 * because false does not tell us why the purchase failed.
		 *
		 * Because of this problem, we've added a check of $is_purchase. If we are purchasing an
		 * image, return additional information.
		 */
		if( ! $valid_image ) {
			if( $is_purchase ) {
				$server_response = json_decode( $response['body'], true );

				$message = ( is_array( $server_response ) && isset( $server_response['message'] ) )
				? esc_html( $server_response['message'] )
				: esc_html__( 'Unknown Error.', 'boldgrid-inspirations' );

				return array(
					'success' => false,
					'message' => $message,
				);
			} else {
				return false;
			}
		}

		// Save cache files, if enabled.
		if ( null !== $this->asset_cache && ! $image_from_cache && ! empty( $info['cache_id'] ) ) {
			$this->asset_cache->save_cache_files( $info['cache_id'], $response );
		}

		// Generate some variables for later use.
		/* @formatter:off */
		$data =					$response['body'];
		$filename =				$response['headers']['z-filename'];
		$asset_type =			isset( $response['headers']['z-asset-type'] )			? $response['headers']['z-asset-type']			: null;
		$asset_id =				$response['headers']['z-asset-id'];
		$asset_coin_cost =		isset( $response['headers']['z-coin-cost'] )			? $response['headers']['z-coin-cost']			: null;
		$attribution_license =	isset( $response['headers']['z-attribution-license'] )	? $response['headers']['z-attribution-license']	: null;
		$attribution_data =		isset( $response['headers']['z-attribution-data'] )		? $response['headers']['z-attribution-data']	: null;
		$width =				isset( $response['headers']['z-width'] )				? $response['headers']['z-width']				: '234';
		$height =				isset( $response['headers']['z-height'] )				? $response['headers']['z-height']				: '234';
		$image_provider_id =	isset( $response['headers']['z-provider-id'])			? $response['headers']['z-provider-id']			: null;
		$id_from_provider =		isset( $response['headers']['z-id-from-provider'])		? $response['headers']['z-id-from-provider']	: null;
		$orientation =			isset( $response['headers']['z-orientation'])			? $response['headers']['z-orientation']			: null;
		$image_size =			isset( $response['headers']['z-image-size'])			? $response['headers']['z-image-size']			: null;
		$transaction_item_id =	isset( $response['headers']['z-transaction-item-id'])	? $response['headers']['z-transaction-item-id']	: null;
		$transaction_id =		isset( $response['headers']['z-transaction-id'])		? $response['headers']['z-transaction-id']		: null;
		/* @formatter:on */

		// Have we already downloaded this asset?
		$existing_asset = $this->get_asset(
			array (
				'by' => 'asset_id',
				'asset_id' => $asset_id
			) );

		$asset_previously_downloaded = true;

		if ( false == $existing_asset ) {
			$asset_previously_downloaded = false;
		} elseif ( $existing_asset ) {
			$attributes_to_check = array (
				'image_provider_id' => $image_provider_id,
				'id_from_provider' => $id_from_provider,
				'image_size' => $image_size,
				'width' => $width,
				'height' => $height
			);

			foreach ( $attributes_to_check as $key => $value ) {
				if ( $existing_asset[$key] != $value ) {
					$asset_previously_downloaded = false;
					break;
				}
			}
		}

		if ( $asset_previously_downloaded ) {
			// Example $existing_asset_metadata: http://pastebin.com/FDtTV8uy .
			$existing_asset_metadata = wp_get_attachment_metadata(
				$existing_asset['attachment_id'] );

			/*
			 * Set the attachment id. We need to set this value because it may
			 * be needed at the end of this method when we return data. In the
			 * event an asset was never previously downloaded and we were not
			 * in this conditional, $attachment_id would be set further down,
			 * after we download the image for the first time.
			 */
			$attachment_id = $existing_asset['attachment_id'];

			// Example $upload_dir: http://pastebin.com/d07HDtAv .
			$upload_dir = wp_upload_dir();
		}

		/*
		 * Save the image.
		 * wp_upload_bits: Create a file in the upload folder with given content.
		 */
		if ( ! $asset_previously_downloaded ) {
			// Example $uploaded: http://pastebin.com/YGW6cmfW .
			$uploaded = wp_upload_bits( $filename, null, $data );

			if ( $uploaded['error'] ) {
				throw new Exception( $uploaded['error'] );
			}
		} else {
			$wp_filetype = wp_check_filetype( $existing_asset['name'], null );

			$uploaded = array (
				'file' => $existing_asset['name'],
				'url' => $upload_dir['baseurl'] . '/' . $existing_asset_metadata['file'],
				'type' => $wp_filetype['type']
			);
		}

		/*
		 * If this was an image purchase, we can return at this point.
		 * IF WE WERE TO CONTINUE, we would end up adding this image as:
		 * * a new attachment.
		 * * a new asset in the boldgrid_assets option.
		 * We'd then have two attachments, the watermarked and unwatermarked.
		 */
		if ( $is_purchase && ! $is_redownload ) {
			return array (
				'transaction_item_id' => $transaction_item_id,
				'transaction_id' => $transaction_id,
				'file' => $uploaded['file']
			);
		}

		/*
		 * Actions to take for new downloads.
		 * If we've already downloaded this asset previously, then we can skip this.
		 */
		if ( ! $asset_previously_downloaded ) {
			// Retrieve the file type from the file name.
			$wp_filetype = wp_check_filetype( $uploaded['file'], null );

			// Generate the attachment data.
			$attachment = array (
				'post_mime_type' => $wp_filetype['type'],
				'guid' => $uploaded['url'],
				'post_parent' => $post_id,
				'post_title' => isset( $response['headers']['z-wp-title'] ) ? $response['headers']['z-wp-title'] : '',
				'post_content' => isset( $response['headers']['z-wp-description'] ) ? $response['headers']['z-wp-description'] : '',
				'post_excerpt' => isset( $response['headers']['z-wp-caption'] ) ? $response['headers']['z-wp-caption'] : ''
			);

			/*
			 * Insert the attachment into the media library.
			 * $attachment_id is the ID of the entry created in the wp_posts table.
			 */
			$attachment_id = wp_insert_attachment( $attachment, $uploaded['file'], $post_id );

			if ( 0 == $attachment_id ) {
				throw new Exception( 'wp_insert_attachment() ERROR' );
			}

			// Add this new asset to boldgrid_asset in wp_options.
			$asset_details = array (
				'asset_id' => $asset_id,
				'coin_cost' => $asset_coin_cost,
				'name' => $uploaded['file'],
				'purchase_date' => '',
				'download_date' => date( 'Y-m-d H:i:s' ),
				'attribution' => $attribution_data,
				'attribution_license' => $attribution_license,
				'attachment_id' => $attachment_id,
				'width' => $width,
				'height' => $height,
				'image_provider_id' => $image_provider_id,
				'id_from_provider' => $id_from_provider,
				'orientation' => $orientation,
				'image_size' => $image_size,
				'transaction_item_id' => $transaction_item_id,
				'transaction_id' => $transaction_id
			);

			$is_added = $this->add_new_asset( 'image', $asset_details );

			/*
			 * Because we are resizing images before we set them as assets,
			 * we don't need wordpress to resize theme for us.
			 */
			if ( ! $this->is_preview_server || $add_meta_data ) {
				/*
				 * Generates metadata for an image attachment, and create a thumbnail and other
				 * intermediate sizes of the image attachment based on the sizes defined on the
				 * Settings_Media_Screen.
				 */
				$attach_data = wp_generate_attachment_metadata( $attachment_id, $uploaded['file'] );

				// Update metadata for the attachment.
				wp_update_attachment_metadata( $attachment_id, $attach_data );
			}

			// is this a featured image?
			if ( $featured_image ) {
				/*
				 * The function update_post_meta() updates the value of an existing meta key (custom
				 * field) for the specified post.
				 */
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}
		}

		/**
		 * ********************************************************************
		 * Determine what to return, and return it.
		 * ********************************************************************
		 */

		/*
		 * If we're not attaching this to a post, return the url.
		 * @todo: This statement can be incorporated into the switch($return) below.
		 */
		if ( false == $post_id ) {
			if ( 'all' == $return ) {
				// Add two more items to $uploaded array before returing it
				$uploaded['transaction_item_id'] = $transaction_item_id;
				$uploaded['transaction_id'] = $transaction_id;
				$uploaded['attachment_id'] = $attachment_id;

				return $uploaded;
			} else {
				return $uploaded['url'];
			}
		}

		// Determine what needs to be returned from this function call.
		switch ( $return ) {
			case 'attachment_id' :
				$return_value = $attachment_id;
				break;

			case 'all' :
				$return_value = array (
					'uploaded_url' => $uploaded['url'],
					'attachment_id' => $attachment_id,
					'asset_id' => $asset_id,
					'coin_cost' => $asset_coin_cost,
					'headers' => $response['headers']
				);
				break;

			default :
				$return_value = $uploaded['url'];
		}

		return $return_value;
	}

	/**
	 * Ensure assets array has proper structure.
	 *
	 * The three type of assets ( plugin, theme, image ) should be arrays. Test each type. If it
	 * is not an array, set it as an empty area.
	 *
	 * @since 1.1.5
	 *
	 * @param  array $assets An array of assets.
	 * @return array
	 */
	public function fix_array_structure( $assets ) {
		// If our assets is not an array, return the default structure.
		if( ! is_array( $assets ) ) {
			return $this->default_assets;
		}

		// Make sure each asset type is defined and is an array.
		foreach( $this->asset_types as $type ) {
			if( ! isset( $assets[ $type ] ) ) {
				$assets[ $type ] = array();
			} elseif ( ! is_array( $assets[ $type ] ) ) {
				$assets[ $type ] = array();
			}
		}

		return $assets;
	}

	/**
	 * Get the Active 'boldgrid_asset' option.
	 *
	 * The requires disabling all filters for 'pre_option_boldgrid_asset'.
	 *
	 * @since 1.1.5
	 *
	 * @return array.
	 */
	public function get_active_assets() {

		/*
		 * Force getting the unfiltered assets.
		 *
		 * We don't ever want to get boldgrid_staging_boldgrid_asset in this
		 * method, hence the method's get "active" assets name.
		 */
		update_option( 'boldgrid_staging_get_unfiltered_boldgrid_asset', '1' );

		$assets = get_option( 'boldgrid_asset' );

		delete_option( 'boldgrid_staging_get_unfiltered_boldgrid_asset' );

		return $assets;
	}

	/**
	 * Pass an attachment_id and return possible file names for the asset.
	 *
	 * For example, possible filenames include the file's filename as well as the filename of the
	 * thumbnail / etc.
	 *
	 * @param int $attachment_id The WordPress attachment id.
	 *
	 * @return array
	 */
	public function get_array_of_possible_filenames_for_an_asset( $attachment_id ) {
		$possible_asset_filenames = array ();

		// get all the data for the attachment id
		$attachment_metadata = wp_prepare_attachment_for_js( $attachment_id );

		// if we have sizes...
		if ( isset( $attachment_metadata['sizes'] ) and ! empty( $attachment_metadata['sizes'] ) ) {
			// loop through each size
			foreach ( $attachment_metadata['sizes'] as $size_type => $sizes_array ) {
				// setup some variables...
				$url = $sizes_array['url'];
				$url_exploded = explode( '/', $url );

				// then set the values we're looking for
				$possible_asset_filenames['size'][$size_type]['url'] = $url;
				$possible_asset_filenames['size'][$size_type]['filename'] = end( $url_exploded );
				$possible_asset_filenames['filenames'][] = $possible_asset_filenames['size'][$size_type]['filename'];
				$possible_asset_filenames['urls'][] = $url;
			}
		}

		return $possible_asset_filenames;
	}

	/**
	 * Get one asset by asset_id.
	 *
	 * @param array $params An array of parameters.
	 *
	 * @return array|false
	 */
	public function get_asset( $params ) {
		// Validate parameters:
		if ( 'asset_id'            != $params['by'] &&
			 'transaction_item_id' != $params['by'] &&
			 'attachment_id'       != $params['by'] ) {

			// LOG.
			error_log( __METHOD__ . ': Error: Invalid parameters: ' . print_r( $params, true ) );

			return false;
		}

		$assets = $this->get_combined_assets();

		foreach ( $assets as $asset_type => $array_of_assets ) {
			foreach ( $array_of_assets as $asset_key => $asset ) {
				if ( $asset[$params['by']] == $params[$params['by']] ) {
					return $asset;
				}
			}
		}

		// If no asset was found, return false.
		return false;
	}

	/**
	 * Get the URL needed to download an item from the asset server.
	 *
	 * Originally, we used get_asset.
	 * However, there may be times we want to download something other than an asset.
	 * We will check the $item variable to determine if we're downloading an asset
	 * or something else.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 *
	 * @param  int|array $item           An asset id or an array of parameters.
	 * @param  int       $transaction_id An optional transaction id.
	 * @return array
	 */
	public function get_asset_server_item_download_info( $item, $transaction_id = null ) {
		// Get configs.
		$boldgrid_configs = $this->get_configs();

		// Build return array.
		switch ( gettype( $item ) ) {
			case 'array' :
				switch ( $item['type'] ) {
					/*
					 * ********************************************************
					 * Built photo search.
					 * ********************************************************
					 */
					case 'built_photo_search' :

						$return = array (
							'url' => $boldgrid_configs['asset_server'] .
								 $boldgrid_configs['ajax_calls']['image_download'],
								'arguments' => array (
									'method' => 'POST',
									'body' => array (
									/* @formatter:off */
									'key' => $this->api->get_api_key_hash(),
									'id_from_provider' 			=> $item['params']['id_from_provider'],
									'image_provider_id' 		=> 	$item['params']['image_provider_id'],
									'imgr_image_id' 			=> 	isset( $item['params']['imgr_image_id'] )				? $item['params']['imgr_image_id']				: null,
									'width' 					=> 	isset( $item['params']['width'] )						? $item['params']['width']						: null,
									'height' 					=> 	isset( $item['params']['height'] )						? $item['params']['height']						: null,
									'orientation' 				=> 	isset( $item['params']['orientation'] )					? $item['params']['orientation']				: null,
									'image_size' 				=> 	isset( $item['params']['image_size'] )					? $item['params']['image_size']					: null,
									/* @formatter:on */
								)
								),
								'method' => 'post'
						);

						break;

					case 'built_photo_search_purchase' :
						$return = array (
							'url' => $boldgrid_configs['asset_server'] .
								 $boldgrid_configs['ajax_calls']['image_download'],
								'arguments' => array (
									'method' => 'POST',
									'body' => array (
										/* @formatter:off */
										'key' => $this->api->get_api_key_hash(),
										'id_from_provider' 			=> $item['params']['id_from_provider'],
										'image_provider_id' 		=> 	$item['params']['image_provider_id'],
										'imgr_image_id' 			=> 	isset( $item['params']['imgr_image_id'] )				? $item['params']['imgr_image_id']				: null,
										'width' 					=> 	isset( $item['params']['width'] )						? $item['params']['width']						: null,
										'height' 					=> 	isset( $item['params']['height'] )						? $item['params']['height']						: null,
										'orientation' 				=> 	isset( $item['params']['orientation'] )					? $item['params']['orientation']				: null,
										'image_size' 				=> 	isset( $item['params']['image_size'] )					? $item['params']['image_size']					: null,
										'expected_coin_cost' 		=> 	isset( $item['params']['expected_coin_cost'])			? $item['params']['expected_coin_cost']			: 0,
										'is_purchase' 				=> 	true,
										'is_redownload' 			=> 	isset( $item['params']['is_redownload'] )				? $item['params']['is_redownload']				: false,
										'user_transaction_item_id' 	=> 	isset( $item['params']['user_transaction_item_id'] )	? $item['params']['user_transaction_item_id']	: null,
										'boldgrid_connect_key' 		=> 	isset( $item['params']['boldgrid_connect_key'] )		? $item['params']['boldgrid_connect_key']		: null,
										'site_hash' 				=> 	isset( $boldgrid_configs['site_hash'] )					? $boldgrid_configs['site_hash']				: null
								 /* @formatter:on */
									)
								),
								'method' => 'post'
						);

						if ( ! empty( $transaction_id ) ) {
							$return['arguments']['body']['transaction_id'] = $transaction_id;
						}

						if ( function_exists( 'wp_get_current_user' ) &&
							 false !== ( $current_user = wp_get_current_user() ) ) {
							$return['arguments']['body']['wp_user_id'] = $current_user->ID;
							$return['arguments']['body']['wp_user_login'] = $current_user->user_login;
							$return['arguments']['body']['wp_user_email'] = $current_user->user_email;
						}

						break;

					/*
					 * ********************************************************
					 * Stock Photography.
					 * ********************************************************
					 */
					case 'stock_photography_download' :
						/* @formatter:off */
						$return = array (
							'url' => $boldgrid_configs['asset_server'] . $boldgrid_configs['ajax_calls']['image_download'],
							'arguments' => array (
								'method' => 'POST',
								'body' => array (
									'key' 				=> 	$this->api->get_api_key_hash(),
									'id_from_provider' 	=> 	$_POST['id_from_provider'],
									'image_provider_id' => 	$_POST['image_provider_id'],
									'image_size' 		=> 	$_POST['image_size'],
									'width' 			=> 	isset( $item['params']['width'] )	? $item['params']['width']	: null,
									'height' 			=> 	isset( $item['params']['height'] )	? $item['params']['height']	: null,
								)
							),
							'method' => 'post'
						);
						/* @formatter:on */
						break;
				}
				break;

			/*
			 * ****************************************************************
			 * if $item is a number, then assume it's an asset id.
			 * ****************************************************************
			 */
			default :
				/* @formatter:off */
				$url = $boldgrid_configs['asset_server'] . $boldgrid_configs['ajax_calls']['get_asset'] .
					'?id=' 				. 	$item .
					'&key=' 			. 	$this->api->get_api_key_hash();
				/* @formatter:on */

				$return = array (
					'url' => $url,
					'method' => 'get'
				);
				break;
		}

		// Set the cache_id.
		if ( null !== $this->asset_cache ) {
			$return['cache_id'] = $this->asset_cache->set_cache_id( $return );
		}

		return $return;
	}

	/**
	 * Retrieve an asset from wp_options, set it in a class property, and re-save it to wp_options.
	 */
	public function get_wp_options_asset() {
		// Get asset from the database.
		$this->wp_options_asset = get_option( 'boldgrid_asset' );

		// If the option doesn't already exist, initiate it.
		if ( false === $this->wp_options_asset ) {
			$this->wp_options_asset = $this->default_assets;

			// Save it.
			$this->save_wp_options_asset();
		}
	}

	/**
	 * Merge and return the Active and Staging values of the 'boldgrid_asset' option.
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	public function get_combined_assets() {

		/*
		 * A generic return value for this method. Our Active and Staging options will be merged
		 * into this empty array.
		 */
		$default = $this->default_assets;

		// Get all Active assets and flag them as Active.
		$active_assets = $this->get_active_assets();
		$active_assets = $this->fix_array_structure( $active_assets );
		foreach( $active_assets['image'] as $key => $asset ) {
			$this->asset_id_data[ $asset['asset_id'] ]['active'] = 'active';
		}

		// Get all Staging assets and flag them as Staging.
		$staging_assets = get_option( 'boldgrid_staging_boldgrid_asset' );
		$staging_assets = $this->fix_array_structure( $staging_assets );
		foreach( $staging_assets['image'] as $key => $asset ) {
			$this->asset_id_data[ $asset['asset_id'] ]['active'] = 'staging';
		}

		// Merge our active and staging options into $default.
		foreach( $this->asset_types as $asset_type ) {
				$default[ $asset_type ] = array_merge( $active_assets[ $asset_type ], $staging_assets[ $asset_type ] );
		}

		return $default;
	}

	/**
	 * Is asset used within a certain post?
	 *
	 * @param object $post A WordPress post object.
	 * @param array $asset An array of asset information.
	 * @return bool
	 */
	public function is_asset_used_within_post( $post, $asset ) {
		// Ensure we've got good data being passed in.

		// Do we have a valid post?
		if ( ! is_object( $post ) ) {
			die( 'invalid post' );
		}

		// Do we have a valid asset?
		if ( ! is_array( $asset ) ) {
			die( 'invalid asset' );
		}

		$filename_data_to_search_for = array (
			'filenames',
			'urls'
		);

		// Is the asset within the post_content?
		foreach ( $filename_data_to_search_for as $filename_data_key ) {
			// If we have filenames / urls.
			if ( isset( $asset['filename_data'][$filename_data_key] ) ) {
				// Loop through each filename.
				foreach ( $asset['filename_data'][$filename_data_key] as $search_string ) {
					// Is the filename/url found within the post_content?
					if ( substr_count( $post->post_content, $search_string ) > 0 ) {
						return true;
					}
				}
			}
		}

		// Is this asset the featured image of the post?
		if ( $post->featured_image_id ) {
			if ( $post->featured_image_id == $asset['attachment_id'] ) {
				return true;
			}
		}

		// No sir, this asset is not used within this post.
		return false;
	}

	/**
	 * Save the asset stored in a class property to wp_options.
	 */
	public function save_wp_options_asset() {
		// Just seems like a good idea to make sure we are not erasing the data.
		if ( ! empty( $this->wp_options_asset ) ) {
			update_option( 'boldgrid_asset', $this->wp_options_asset );
		}
	}

	/**
	 * Set asset attributes by asset id.
	 *
	 * @param int $asset_id An asset id.
	 * @param string $key A key name.
	 * @param string $value A value.
	 */
	public function set_asset_att_by_asset_id( $asset_id, $key, $value ) {
		// If the boldgrid_asset variable is set.
		if ( ! empty( $this->wp_options_asset ) ) {
			// Loop through each asset type (image / plugin / theme).
			foreach ( $this->wp_options_asset as $asset_type => $assets ) {
				// If we have assets for this type... (for example, if we have image[0] and image[1].
				if ( $assets ) {
					// Loop through each of the assets belonging to this asset type.
					foreach ( $assets as $asset_key => $asset ) {
						if ( $asset['asset_id'] == $asset_id ) {
							// Set the $key / $value.
							$this->wp_options_asset[$asset_type][$asset_key][$key] = $value;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Pass in a post and get a listing of assets being used within it.
	 *
	 * @param object $post A WordPress post object.
	 */
	public function set_assets_within_post( $post ) {
		// Ensure we have a post.
		if ( ! is_object( $post ) ) {
			die( 'bad post' );
		}

		// The post object does not include the featured image (if it has one).
		$post->featured_image_id = get_post_thumbnail_id( $post->ID );

		/*
		 * We'll need to start off by checking if things exist.
		 * If the boldgrid_asset variable is set.
		 */
		if ( ! empty( $this->wp_options_asset ) ) {
			// Loop through each asset type (image / plugin / theme).
			foreach ( $this->wp_options_asset as $asset_type => $assets ) {
				// If we have assets for this type... (for example, if we have image[0] and image[1].
				if ( $assets ) {
					// Loop through each of the assets belonging to this asset type.
					foreach ( $assets as $asset_key => $asset ) {
						// First, get an array of possible filenames for this asset.
						$this->wp_options_asset[$asset_type][$asset_key]['filename_data'] = $this->get_array_of_possible_filenames_for_an_asset(
							$asset['attachment_id'] );
						// Then check to see if this asset is in this post.
						if ( $this->is_asset_used_within_post( $post,
							$this->wp_options_asset[$asset_type][$asset_key] ) ) {
							$this->assets_within_post[] = $this->wp_options_asset[$asset_type][$asset_key];
						}
					}
				}
			}
		}
	}

	/**
	 * Set publish decision status.
	 *
	 * @param int $post_id A WordPress post id.
	 * @param unknown $decision Some decision.
	 * @param string $which_assets A string to determine which asset(s) to affect.
	 * @return string
	 */
	public function set_publish_decision_status( $post_id, $decision, $which_assets ) {
		// ensure $post_id is a number
		if ( ! is_numeric( $post_id ) ) {
			die( 'Invalid post id' );
		}

		// get the post
		$post = get_post( $post_id );

		// get the assets used within the post
		$this->set_assets_within_post( $post );

		// We can set attribute for one asset, or all_that_need_a_publish_decision
		switch ( $which_assets ) {
			case 'all_that_need_a_publish_decision' :
				foreach ( $this->assets_within_post as $asset ) {
					if ( true == $this->asset_needs_publish_decision( $asset ) ) {
						$this->set_asset_att_by_asset_id( $asset['asset_id'],
							'publish_decision_status', $decision );
					}
				}
				$this->save_wp_options_asset();
				break;
		}

		return 'success';
	}

	/**
	 * Update an asset.
	 *
	 * One asset has an array of data stored about it. This method allows you to update either:
	 * 1. The entire array of the asset data.
	 * 2. One specific key / value of the asset array.
	 *
	 * This method will automatically determine if the asset in question is Active or Staging, and
	 * update the applicable option ( boldgrid_asset or boldgrid_staging_boldgrid_asset ).
	 *
	 * @param array $params An array of parameters.
	 *
	 * @return bool
	 */
	public function update_asset( $params ) {
		// Define a few variables to help code readability.
		$asset_id = $params['asset_id'];
		$type     = $params['asset_type'];

		// Call the get_combined_assets method to set this class' asset_id_data property.
		$this->get_combined_assets();

		// Determine if we are updating an Active or Staging asset.
		$is_asset_active = ( 'active' === $this->asset_id_data[ $asset_id ]['active'] );

		// Determine the option name we will be working with.
		$option_name = ( $is_asset_active ? 'boldgrid_asset' : 'boldgrid_staging_boldgrid_asset' );

		// Get our assets.
		if ( $is_asset_active ) {
			$assets = $this->get_active_assets();
		} else {
			$assets = get_option( $option_name );
		}

		// If we do not have any assets, return false.
		if ( false === $assets ) {
			return false;
		} else {
			$assets = $this->fix_array_structure( $assets );
		}

		switch ( $params['task'] ) {
			case 'update_key_value' :
				// Loop through all assets of the given type: $params['asset_type'].
				foreach ( $assets[ $type ] as $asset_key => $asset ) {

					// When we get an asset_id match.
					if ( $asset['asset_id'] == $asset_id ) {

						// Update the key value.
						$assets[ $type ][ $asset_key ][ $params['key'] ] = $params['value'];

						// Save our updated assets.
						update_option( $option_name, $assets );

						return true;
					}
				}
				break;

			case 'update_entire_asset' :
				foreach ( $assets as $asset_type => $array_of_assets ) {
					// Loop through all of the assets.
					foreach ( $array_of_assets as $asset_key => $asset ) {

						// If we have found an asset_id match.
						if ( $asset['asset_id'] == $asset_id ) {

							// Update the asset.
							$assets[ $asset_type ][ $asset_key ] = $params['asset'];

							// Save our updated assets.
							update_option( $option_name, $assets );

							return true;
						}
					}
				}
				break;

			case 'delete_attachment':
				foreach ( $assets as $asset_type => $array_of_assets ) {
					foreach ( $array_of_assets as $asset_key => $asset ) {
						if ( $asset['attachment_id'] == $params['attachment_id'] ) {
							unset( $assets[ $asset_type ][$asset_key]);
							update_option( $option_name, $assets );
							return true;
						}
					}
				}
				break;
		}

		// If we have reached this point, we were unable to find and update the requested asset.
		return false;
	}

	/**
	 * Update class property when option is updated.
	 *
	 * When this class is instantiated, we store a copy of boldgrid_asset. If another class / method
	 * updates that option, we need to keep our local property up to date.
	 *
	 * @since 1.3.1
	 *
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @param string $option    Option name.
	 */
	public function update_boldgrid_asset( $old_value, $value, $option = null ) {
		$this->wp_options_asset = $value;
	}
}
