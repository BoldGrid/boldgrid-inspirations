<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Purchase_For_Publish
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Purchase for Publish class.
 */
class Boldgrid_Inspirations_Purchase_For_Publish extends Boldgrid_Inspirations {
	/**
	 * Name of transient that stores total cost to purchase for publish.
	 *
	 * @since SINCEVERSION
	 * @var string
	 *
	 * @see self::get_total_cost_to_purchase_for_publishing()
	 */
	public static $transient_name_cost = 'bginsp_total_coin_cost';

	/**
	 * Hooks required for the PurchaseForPublish class
	 */
	public function add_admin_hooks() {
		if ( is_admin() ) {
			// Load up any css / js we need.
			add_action( 'admin_enqueue_scripts',
				array(
					$this,
					'enqueue_header_content'
			) );

			// Add a shopping cart icon to the admin header.
			add_action( 'admin_bar_menu', array(
				$this,
				'toolbar_link_to_mypage'
			), 999 );

			// Add the submenu item "Cart".
			add_action( 'admin_menu', array(
				$this,
				'cart_checkout'
			), 1234 );

			// In the right sidebar, tell the user how many watermarked images they have on the
			// page.
			add_action( 'post_submitbox_misc_actions',
				array(
					$this,
					'post_submitbox_misc_actions_show_user_watermarked_image_count'
			), 9 );

			/**
			 * AJAX calls
			 */

			add_action( 'wp_ajax_get_purchased_image_details',
				array(
					$this,
					'get_purchased_image_details_callback'
			) );

			add_action( 'wp_ajax_re_download_purchased_image',
				array(
					$this,
					're_download_purchased_image_callback'
			) );

			// The user checked / unchecked an image in the shopping cart.
			add_action( 'wp_ajax_image_in_shopping_cart_checked',
				array(
					$this,
					'image_in_shopping_cart_checked_callback'
			) );

			add_action( 'boldgrid_post_image_purchased', array( $this, 'post_image_purchased' ), 10, 2 );
		}
	}

	/**
	 * Add hooks, regardless of is_admin.
	 *
	 * @since SINCEVERSION
	 */
	public function add_hooks_always() {
		/*
		 * Delete the "total cost to purchase for publish" value when someone edits a post or they
		 * modify the boldgrid_asset option (like unchecking an image from cart).
		 */
		$actions = array(
			'save_post',
			'update_option_boldgrid_asset',
		);

		foreach ( $actions as $action ) {
			add_action( $action, function() {
				delete_transient( self::$transient_name_cost );
			});
		}
	}

	/**
	 * Register styles/scripts
	 */
	public function enqueue_header_content( $hook ) {
		// CSS and JS that need to be on all Z pages:
		wp_register_style( 'purchase_for_publish',
			plugins_url(
				'/' . basename( BOLDGRID_BASE_DIR ) . '/assets/css/purchase_for_publish.css' ),
				array(), BOLDGRID_INSPIRATIONS_VERSION );

		wp_enqueue_style( 'purchase_for_publish' );

		// Cart.
		$cart_hooks = array(
			// Cart page, when not using "BoldGrid Admin Menu system".
			'boldgrid_page_boldgrid-cart',
			// Cart page, when using "BoldGrid Admin Menu system".
			'inspirations_page_boldgrid-cart',
			'transactions_page_boldgrid-cart',
		);
		if ( in_array( $hook, $cart_hooks ) ) {
			wp_register_style( 'boldgrid-cart',
				plugins_url( '/' . basename( BOLDGRID_BASE_DIR ) . '/assets/css/boldgrid-cart.css' ),
				array(), BOLDGRID_INSPIRATIONS_VERSION );

			wp_enqueue_style( 'boldgrid-cart' );

			$handle = 'boldgrid-cart';

			wp_register_script(
				$handle,
				plugins_url( '/assets/js/boldgrid-cart.js', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
				array(),
				BOLDGRID_INSPIRATIONS_VERSION,
				true
			);
			wp_localize_script(
				$handle,
				'BoldGridCart',
				array(
					'imageSelection'   => esc_html__( 'Error, image selection has not been updated.', 'boldgrid-inspirations' ),
					'reviewKey'        => esc_html__( 'Please enter your BoldGrid Connect Key!', 'boldgrid-inspirations' ),
					'reviewKeyInvalid' => esc_html__( 'Invalid BoldGrid Connect Key!', 'boldgrid-inspirations' ),
					'reviewKeyShort'   => esc_html__( 'Your BoldGrid Connect Key appears to be invalid, it is too short.', 'boldgrid-inspirations' ),
					'reviewTos'        => sprintf(
						// translators: 1 A break tag, please insert the break tag in the middle of the sentence.
						__( 'Please review the terms of service and%1$scheck the box above if you agree.', 'boldgrid-inspirations' ),
						'<br />'
					),
				)
			);
			wp_enqueue_script( $handle );
		}

		if ( 'boldgrid_page_transaction-history' != $hook && 'transactions_page_cart' != $hook ) {
			return;
		}

		Boldgrid_Inspirations_Ajax::enqueue();
	}

	/**
	 * Return the coin value it costs to purchase for publish
	 */
	public function get_total_cost_to_purchase_for_publishing( $args = array() ) {
		// This can be expensive. Try to get from transient first.
		$total_coin_cost = get_transient( self::$transient_name_cost );
		if ( false !== $total_coin_cost ) {
			return $total_coin_cost;
		}

		/**
		 * ********************************************************************
		 * Configure args and vars
		 * ********************************************************************
		 */
		$defaults['process_checked_in_cart_attribute'] = true;

		$args = wp_parse_args( $args, $defaults );

		$total_coin_cost = 0;

		// Before printing the information, we first need to generate it.
		$this->create_array_assets_needing_purchase( $args );

		/**
		 * ********************************************************************
		 * Abort / return early
		 * ********************************************************************
		 */
		if ( ! isset( $this->assets_needing_purchase['by_page_id'] ) ) {
			return $total_coin_cost;
		}

		/**
		 * ********************************************************************
		 * Loop through each page
		 * ********************************************************************
		 */
		foreach ( $this->assets_needing_purchase['by_page_id'] as $post_id => $assets ) {
			/**
			 * ****************************************************************
			 * Loop through each asset in that page
			 * ****************************************************************
			 */
			foreach ( $assets as $asset ) {
				// Does this asset have a coin cost?
				$has_coin_cost = ( isset( $asset['coin_cost'] ) && 0 < $asset['coin_cost'] ) ? true : false;

				// Is this image 'checked' in the cart?
				// By default, it is always 'checked' unless:
				// 1: $asset['checked_in_cart'] is specifically set, and
				// 2: it is set to false.
				$is_checked_in_cart = ( ( isset( $asset['checked_in_cart'] ) &&
					 ! $asset['checked_in_cart'] ) ? false : true
				);

				if ( $has_coin_cost && $is_checked_in_cart ) {
					$total_coin_cost += $asset['coin_cost'];
				}
			}
		}

		set_transient( self::$transient_name_cost, $total_coin_cost, DAY_IN_SECONDS );

		return $total_coin_cost;
	}

	/**
	 * The user checked / unchecked an image in the shopping cart.
	 */
	public function image_in_shopping_cart_checked_callback() {
		global $wpdb;

		// Only Administrators can be in the cart.
		if( ! current_user_can( 'manage_options' ) ) {
			esc_html_e( 'Insufficient permissions', 'boldgrid-inspirations' );
			wp_die();
		}

		$asset_id = intval( $_POST['asset_id'] );
		$checked = trim( $_POST['checked'] );

		/**
		 * ********************************************************************
		 * Shall we abort?
		 * ********************************************************************
		 */
		// Abort if asset_id is not a number.
		if ( ! ( is_numeric( $asset_id ) && 0 < $asset_id ) ) {
			echo 'bad asset id';
			wp_die();
		}

		// Abrt if checked is not bool.
		if ( 'true' != $checked && 'false' != $checked ) {
			echo 'bad checked';
			wp_die();
		}

		// Convert the string to bool.
		$checked = 'true' == $checked ? true : false;

		/**
		 * ********************************************************************
		 * Data is good, continue...
		 * ********************************************************************
		 */

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$assetManager = new Boldgrid_Inspirations_Asset_Manager();

		$asset = $assetManager->get_asset(
			array(
				'by' => 'asset_id',
				'asset_id' => $asset_id,
			) );

		$assetManager->update_asset(
			array(
				'task' => 'update_key_value',
				'asset_type' => 'image',
				'asset_id' => $asset_id,
				'key' => 'checked_in_cart',
				'value' => $checked,
			) );

		echo 'success';

		wp_die();
	}

	/**
	 * Determine if a call to purchase an image was successful.
	 *
	 * @since 1.2.12
	 *
	 * @param bool|array $result The results of an image download request.
	 */
	public function is_successful_purchase( $result ) {
		/*
		 * Historically, if a call to assetManager->download_and_attach_asset() returned false, then
		 * the purchase failed. We'll keep this historical check in here.
		 */
		if( false === $result ) {
			return false;
		}

		/*
		 * We recently updated assetManager->download_and_attach_asset() to return an array of data
		 * rather than false on image purchase failure.
		 *
		 * If false === $result['success'], then the purchase failed.
		 */
		if( is_array( $result ) && isset( $result['success'] ) && false === $result['success'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if local cost matches remote cost
	 *
	 * @return boolean
	 */
	public function local_cost_matches_remote_cost() {
		if ( empty( $this->local_publish_cost_data ) ) {
			esc_html_e( 'Error: We have no local cost data', 'boldgrid-inspirations' );

			return false;
		}

		// Loop through all the local prices.
		foreach ( $this->local_publish_cost_data as $asset_id => $asset_cost ) {
			if ( $asset_cost != $this->remote_publish_cost_data->$asset_id ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Method used for 'boldgrid_post_image_purchased' action.
	 *
	 * @since 1.4.8
	 *
	 * @param int   $attachment_id
	 * @param array $original_metadata
	 */
	public function post_image_purchased( $attachment_id, $original_metadata ) {

		/*
		 * During deployment, we may have center cropped this image. If we did,
		 * it would have been saved as a new size, 'boldgrid_deployment_resize'.
		 *
		 * If that size exists in the original metadata, then now we need to
		 * resize the unwatermarked image.
		 */
		if( ! empty( $original_metadata['sizes']['boldgrid_deployment_resize'] ) ) {
			$width  = $original_metadata['sizes']['boldgrid_deployment_resize']['width'];
			$height = $original_metadata['sizes']['boldgrid_deployment_resize']['height'];

			Boldgrid_Inspirations_Attachment::resize( $attachment_id, $width, $height );
		}
	}

	/**
	 * Display watermarked image count
	 *
	 * In the right sidebar of the editor, show the user how many watermarked images they have.
	 */
	public function post_submitbox_misc_actions_show_user_watermarked_image_count() {
		// Configure args and vars:
		$page_id = get_the_ID();

		$this->create_array_assets_needing_purchase();

		// Get the count of images on this page that are watermarked:
		$count_of_watermarked_images_on_this_page = isset(
			$this->assets_needing_purchase['by_page_id'][ $page_id ] ) ? count(
			$this->assets_needing_purchase['by_page_id'][ $page_id ] ) : 0;

		// Should it say "Image" or "Images" ?
		$text_images = ( (int) 1 == $count_of_watermarked_images_on_this_page ) ? 'Image' : 'Images';

		// If there are images that need purchasing, create a "Purchase" link:
		$purchase_link = ( 0 == $count_of_watermarked_images_on_this_page ) ? '' : '<a href="admin.php?page=boldgrid-cart">Purchase</a>';

		echo '
			<style>
				.watermarked_image_count:before {
					content: "\f128";
					top: -1px;
					font: 400 20px/1 dashicons;
  					speak: none;
  					display: inline-block;
  					padding: 0 2px 0 0;
  					top: 0;
  					left: -1px;
  					position: relative;
  					vertical-align: top;
  					-webkit-font-smoothing: antialiased;
  					-moz-osx-font-smoothing: grayscale;
  					text-decoration: none!important;
					color: #82878c;
				}
			</style>
			<div class="misc-pub-section watermarked_image_count">
		';

		printf(
			wp_kses(
				// translators: 1 The count of watermarked images on this page, 'Image' or 'Images', 3 a link to where the user can purchase these images.
				__( 'Watermarked: <strong>%1$u %2$s</strong> %3$s', 'boldgrid-inspirations' ),
				array( 'a' => array( 'href' => array(), ), 'strong' => array(), )
			),
			$count_of_watermarked_images_on_this_page,
			$text_images,
			$purchase_link
		);

		echo '</div>';
	}

	/**
	 * The content echo'd here is printed to the user's screen
	 *
	 * @return boolean
	 */
	public function purchase_for_publish() {
		$total_coins_spent = 0;

		include BOLDGRID_BASE_DIR . '/pages/cart/checking-out.php';

		$this->send_publish_status( '<li>' . esc_html__( 'Gathering local data about images needing purchase...', 'boldgrid-inspirations' ) );

		$local_publish_cost_data = $this->get_local_publish_cost_data();

		if ( false == $this->local_publish_cost_data ) {
			esc_html_e( 'Error: We have no local cost data', 'boldgrid-inspirations' );

			return false;
		}

		$this->send_publish_status( '<li>' . esc_html__( 'Gathering remote data about each image...', 'boldgrid-inspirations' ) . '</li>' );
		$remote_publish_cost_data = $this->get_remote_publish_cost_data();

		$this->send_publish_status( '<li>' . esc_html__( 'Checking your coin balance...', 'boldgrid-inspirations' ) . '</li>' );
		$current_copyright_coin_balance = $this->get_current_copyright_coin_balance();
		$this->send_publish_status(
			'<li>' .
			sprintf(
				// translators: 1 The user's current Copyright Coin balance.
				esc_html__( 'Available coin balance before purchase is: %1$s', 'boldgrid-inspirations' ),
				$current_copyright_coin_balance
			) .
			'</li>'
		);

		if ( ! $this->local_cost_matches_remote_cost() ) {
			$this->update_local_asset_cost();

			// Complete with errors:
			$status = '<li>' .
				printf(
					wp_kses(
						// translators: 1 The opening anchor tag to our Cart page, 2 its closing anchor tag.
						__( 'Local prices have been updated.  Please proceed to the %1$sCart%2$s', 'boldgrid-inspirations' ),
						array( 'a' => array( 'href' => array(), ), )
					),
					'<a href="admin.php?page=boldgrid-cart">',
					'</a>'
				) .
			'</li>';
			$this->send_publish_status( $status );
			Boldgrid_Inspirations_Utility::inline_js_file( 'checking_out_complete_with_errors.js', $this->get_language() );

			return false;
		}

		// Intialize error counter to zero:
		$errors = 0;

		// Initialize $transaction_id to null:
		$transaction_id = null;

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$assetManager = new Boldgrid_Inspirations_Asset_Manager();

		require_once BOLDGRID_BASE_DIR .
			 '/includes/class-boldgrid-inspirations-enable-media-replace.php';
		$mediaReplacer = new Boldgrid_Inspirations_Enable_Media_Replace();

		/**
		 * ********************************************************************
		 * This is where we begin the actual purchasing
		 * ********************************************************************
		 */
		foreach ( $local_publish_cost_data as $asset_id => $asset_cost ) {
			// Grab the details of the asset based off of asset_id.
			$asset = $assetManager->get_asset( array(
				'by'       => 'asset_id',
				'asset_id' => $asset_id,
			) );

			$download_data = array(
				'type'   => 'built_photo_search_purchase',
				'params' => array(
					'id_from_provider'     => $asset['id_from_provider'],
					'image_provider_id'    => $asset['image_provider_id'],
					'orientation'          => $asset['orientation'],
					'image_size'           => $asset['image_size'],
					'expected_coin_cost'   => $asset['coin_cost'],
					'boldgrid_connect_key' => $_POST['boldgrid_connect_key'],
					'transaction_id'       => $transaction_id,
					'attachment_id'        => $asset['attachment_id'],
				)
			);

			$original_metadata = wp_get_attachment_metadata( $asset['attachment_id'] );

			$unique_image_key = $asset['image_provider_id'] . '-' . $asset['id_from_provider'] . '-' .
				 $asset['image_size'];

			$this->send_publish_status(
				'<li><strong>Downloading</strong> unwatermarked image <em>' . $unique_image_key .
					 '</em>...</li>' );

			// After this point, the image is purchased and coins deducted

			// Download and update the attachment:
			$call_to_download_and_attach = $assetManager->download_and_attach_asset( null, null,
				$download_data, 'all', true );

			// Were we able to download the image successfully?
			if ( false === $this->is_successful_purchase( $call_to_download_and_attach ) ) {
				$errors ++;

				// Determine the error message to print.
				$message = ( is_array( $call_to_download_and_attach ) && isset( $call_to_download_and_attach['message'] ) )
							? htmlspecialchars( $call_to_download_and_attach['message'] )
							: esc_html__( 'Error downloading image.', 'boldgrid-inspirations' );

				// Print the error message.
				$this->send_publish_status( "<span style='color:red;'>$message</li>" );
			} else {
				$total_coins_spent += $asset['coin_cost'];

				// Set the key/value pairs to update.
				$asset_data_to_update = array(
					'purchase_date'       => date( 'Y-m-d H:i:s' ),
					'transaction_item_id' => $call_to_download_and_attach['transaction_item_id'],
					'transaction_id'      => $call_to_download_and_attach['transaction_id'],
				);

				// Set $transaction_id:
				if ( empty( $transaction_id ) &&
					 ! empty( $call_to_download_and_attach['transaction_id'] ) ) {
					$transaction_id = $call_to_download_and_attach['transaction_id'];
				}

				// ... and update them
				foreach ( $asset_data_to_update as $update_key => $update_value ) {
					$assetManager->update_asset(
						array(
							'task'       => 'update_key_value',
							'asset_type' => 'image',
							'asset_id'   => $asset_id,
							'key'        => $update_key,
							'value'      => $update_value,
						) );
				}

				$this->send_publish_status( '<li>' . esc_html__( 'Replacing watermaked image...', 'boldgrid-inspirations' ) . '</li>' );

				// Replace the watermakred image with the new image
				$mediaReplacer->replace_image( $asset['attachment_id'],
					$call_to_download_and_attach['file'] );

				/**
				 * Action to take after an image has been successfully purchased.
				 *
				 * @since 1.4.8
				 *
				 * @param int   $asset['attachment_id']
				 * @param array $original_metadata
				 */
				do_action( 'boldgrid_post_image_purchased', $asset['attachment_id'], $original_metadata );
			}
		}

		if ( $errors ) {
			Boldgrid_Inspirations_Utility::inline_js_file( 'checking_out_complete_with_errors.js', $this->get_language() );
		} else {
			Boldgrid_Inspirations_Utility::inline_js_file( 'checking_out_complete.js', $this->get_language() );
		}

		// Add a JS var to the page so we have access to how many coins we spent.
		// We'll use this to update the cart total in the top right of the page.
		Boldgrid_Inspirations_Utility::inline_js_oneliner(
			'boldgrid_cart_total_coins_spent = ' . $total_coins_spent . ';' );
	}

	/**
	 * Redownload purchased image callback.
	 */
	public function re_download_purchased_image_callback() {
		global $wpdb;

		// Only Administrators can re-download purchased images.
		if( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		// Get input POST vars:
		$image_provider_id = $_POST['image_provider_id'];
		$id_from_provider = $_POST['id_from_provider'];
		$user_transaction_item_id = $_POST['user_transaction_item_id'];

		// Validate input vars:
		if ( ! is_numeric( $image_provider_id ) || ! is_numeric( $id_from_provider ) ||
			 ! is_numeric( $user_transaction_item_id ) ) {
			esc_html_e( 'Invalid data.', 'boldgrid-inspirations' );
			wp_die();
		}

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$assetManager = new Boldgrid_Inspirations_Asset_Manager();

		$download_data = array(
			'type'   => 'built_photo_search_purchase',
			'params' => array(
				'id_from_provider'         => $id_from_provider,
				'image_provider_id'        => $image_provider_id,
				'user_transaction_item_id' => $user_transaction_item_id,
				'expected_coin_cost'       => '0',
				'is_redownload'            => true,
			)
		);

		$call_to_download_and_attach = $assetManager->download_and_attach_asset( null, null,
			$download_data, 'all', true );

		echo json_encode( $call_to_download_and_attach );

		wp_die();
	}

	/**
	 * Send publish status
	 *
	 * @param string $status A status message.
	 */
	public function send_publish_status( $status ) {
		$oneliner = '
			$installation_log.find(".plugin-card-top").append("' . $status . '");
			update_deploy_log_line_count();
		';
		Boldgrid_Inspirations_Utility::inline_js_oneliner( $oneliner );

		if ( ob_get_level() > 0 ) {
			ob_flush();
			flush();
		}
	}

	/**
	 * Add an icon link on the admin bar to the cart
	 *
	 * @param object $wp_admin_bar A WP_Admin_Bar class object.
	 */
	public function toolbar_link_to_mypage( $wp_admin_bar ) {
		$args = array(
			'id' => 'pfp',
			'title' => '<span class="ab-icon"></span> (' .
				 $this->get_total_cost_to_purchase_for_publishing() . ')',
				'href' => 'admin.php?page=boldgrid-cart',
				'meta' => array(
					'class' => 'toolbar-pfp',
				)
		);
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Update local asset cost
	 *
	 * @return bool
	 */
	public function update_local_asset_cost() {
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$assetManager = new Boldgrid_Inspirations_Asset_Manager();

		$local_updated = false;

		foreach ( $this->remote_publish_cost_data as $asset_id => $asset_coin_cost ) {
			$params = array(
				'by'       => 'asset_id',
				'asset_id' => $asset_id,
			);
			$current_asset = $assetManager->get_asset( $params );

			// If the local prices does not match the remote prices.
			if ( $current_asset['coin_cost'] != $asset_coin_cost ) {
				$params = array(
					'task'       => 'update_key_value',
					'asset_type' => 'image',
					'asset_id'   => $asset_id,
					'key'        => 'coin_cost',
					'value'      => $asset_coin_cost,
				);

				if ( null === $asset_coin_cost || '' == $asset_coin_cost ) {
					$asset_coin_cost = 'unknown';

					// Alert that the remote image is not available:
					echo '<p style="color:red;">' .
						sprintf(
							// translators: 1 The id of the asset that is no longer available for purchase.
							esc_html__( 'Error: Image (asset id "%1$s") is no longer available for purchase, and will be removed from the cart.', 'boldgrid-inspirations' ),
							$asset_id
						) .
						'</p>';

					error_log( __METHOD__ . ': Error: Image (asset id "' . $asset_id .  '") is no longer available for purchase.' );
				} else {
					// Alert that there was a mismatch:
					echo '<p style="color:red;">' .
						sprintf(
							// translators: 1 The local coin cost, 2 the remote coin cost.
							esc_html__( 'Error: Local coin cost of %1$s does not match remote coin cost %2$s.', 'boldgrid-inspirations' ),
							$current_asset['coin_cost'],
							$asset_coin_cost
						) .
						'</p>';
				}

				if ( true == $assetManager->update_asset( $params ) ) {
					$local_updated = true;
				}
			}
		}

		if ( $local_updated ) {
			echo '<p style="color:green;">' .
				sprintf(
					wp_kses(
						// translators: 1 An opening anchor tag linking to the shopping card, 2 its closing anchor tag.
						__( 'Local prices have been updated! Please proceed to the %1$sCart%2$s.', 'boldgrid-inspirations' ),
						array( 'a' => array( 'href' => array(), ), )
					),
					'<a href="admin.php?page=boldgrid-cart">',
					'</a>'
				) .
				'</p>';
		}

		return $local_updated;
	}

	// Add the cart submenu page
	public function cart_checkout() {
		$boldgrid_menu_options = get_option( 'boldgrid_settings' );

		if ( 1 == $boldgrid_menu_options['boldgrid_menu_option'] ) {
			add_submenu_page(
				'boldgrid-transactions',
				esc_html__( 'Cart', 'boldgrid-inspirations' ),
				esc_html__( 'Cart', 'boldgrid-inspirations' ),
				'administrator',
				'boldgrid-cart',
				array( $this, 'cart_checkout_admin_page' )
			);
		} else {
			add_submenu_page(
				'boldgrid-inspirations',
				esc_html__( 'Cart', 'boldgrid-inspirations' ),
				esc_html__( 'Cart', 'boldgrid-inspirations' ),
				'administrator',
				'boldgrid-cart',
				array( $this, 'cart_checkout_admin_page' )
			);
		}
	}

	/**
	 * Cart checkout admin page.
	 */
	public function cart_checkout_admin_page() {
		if ( isset( $_POST['task'] ) && 'purchase_all' == $_POST['task'] ) {
			$invalid_nonce = ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'purchase_for_publish' );
			if ( $invalid_nonce ) {
				echo '<div class="error"><p>' .
					esc_html__( 'Error processing request to purchase for publish; WordPress security violation! Please try again.', 'boldgrid-inspirations' ) .
					'</p></div>';

				include BOLDGRID_BASE_DIR . '/pages/cart.php';
			} else {
				$this->purchase_for_publish();
			}
		} else {
			include BOLDGRID_BASE_DIR . '/pages/cart.php';

			delete_transient( 'boldgrid_coin_balance' );
		}
	}

	/**
	 * Create an array with assets needing purchase
	 *
	 * @param array $args An array of arguments.
	 */
	public function create_array_assets_needing_purchase( $args = array() ) {
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$asset_manager = new Boldgrid_Inspirations_Asset_Manager();

		/**
		 * ********************************************************************
		 * Configure args and vars
		 * ********************************************************************
		 */
		// Reset the array.
		$this->assets_needing_purchase = array();

		// Process our args.
		$defaults = array(
			'process_checked_in_cart_attribute' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		// Get all assets from the options table:
		$this->wp_options_asset = $asset_manager->get_combined_assets();

		/**
		 * ********************************************************************
		 * Abort if necessary.
		 * ********************************************************************
		 */
		if ( empty( $this->wp_options_asset ) || ! is_array( $this->wp_options_asset ) ) {
			return;
		}

		/**
		 * ********************************************************************
		 * Loop through each asset type (image / plugin / theme)
		 * ********************************************************************
		 */
		foreach ( $this->wp_options_asset as $asset_type => $assets ) {
			// If we have assets for this type...
			// For example, if we have image[0] and image[1]...
			if ( count( $assets ) and is_array( $assets ) ) {
				/**
				 * ************************************************************
				 * loop through each of the assets belonging to this asset type
				 * ************************************************************
				 */
				foreach ( $assets as $asset_key => $asset ) {
					$asset['asset_key'] = $asset_key;

					// FYI: $this->assets_needing_purchase variable is built by
					// the method below, $this->asset_needs_purchase.
					$this->asset_needs_purchase( $asset, $asset_type, $args );
				}
			}
		}
	}

	/**
	 * Get all data for assets needing purchase.
	 *
	 * @param array $args An array of arguments.
	 * @return array
	 */
	public function get_all_data_of_assets_needing_purchase( $args = array() ) {
		$defaults['process_checked_in_cart_attribute'] = true;

		$args = wp_parse_args( $args, $defaults );

		// Get the array of assets needing purchase.
		$this->create_array_assets_needing_purchase( $args );

		// If we have assets needing purchase.
		if ( isset( $this->assets_needing_purchase['by_page_id'] ) &&
			 count( $this->assets_needing_purchase['by_page_id'] ) > 0 ) {

			// Loop through the each page.
			foreach ( $this->assets_needing_purchase['by_page_id'] as $post_id => $assets ) {

				if ( is_numeric( $post_id ) ) {
					// Get the post.
					$post = get_post( $post_id );

					// Get the post title.
					$this->assets_needing_purchase['page_data'][ $post_id ]['post_title'] = $post->post_title;
				} else {
					$this->assets_needing_purchase['page_data'][ $post_id ]['post_title'] = $post_id;
				}

				// Foreach asset found on this page.
				foreach ( $assets as $asset_key => $asset ) {

					// Get the attachment meta data.
					$attachment_metadata = wp_prepare_attachment_for_js( $asset['attachment_id'] );
					$this->assets_needing_purchase['by_page_id'][ $post_id ][ $asset_key ]['attachment_metadata'] = $attachment_metadata;

					// Get the thumbnail url.
					$this->assets_needing_purchase['by_page_id'][ $post_id ][ $asset_key ]['thumbnail_url'] = ( isset(
						$attachment_metadata['sizes']['thumbnail'] ) ? $attachment_metadata['sizes']['thumbnail']['url'] : $attachment_metadata['sizes']['full']['url'] );
				}
			}
		}

		if ( isset( $this->assets_needing_purchase ) ) {
			$return['assets_needing_purchase'] = $this->assets_needing_purchase;
		}

		$return['total_cost'] = $this->get_total_cost_to_purchase_for_publishing( $args );

		return $return;
	}

	/**
	 * Get our language strings.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	private function get_language() {
		return array(
			'name' => 'BoldGridInspirationsPurchase',
			'data' => array(
				'purchaseComplete' => esc_html__( 'Purchase complete!', 'boldgrid-inspirations' ),
				'withErrors'       => esc_html__( 'Purchase complete (with errors)!', 'boldgrid-inspirations' ),
			),
		);
	}

	/* @formatter:off */
	/**
	 * Below, we will loop through $this->assets_needing_purchase.
	 * This is what that array looks like:
	 *
	 * Array
	 * (
	 * 		[by_page_id] => Array
	 * 			(
	 * 				[8] => Array
	 * 					(
	 * 						[0] => Array
	 * 							(
	 * 								[asset_id] => 10910
	 * 								[coin_cost] => 5
	 */
	/* @formatter:on */
	public function get_local_publish_cost_data() {
		// If it's already set, then just return it.
		if ( isset( $this->local_publish_cost_data ) ) {
			return $this->local_publish_cost_data;
		}

		// Get an array of all the assets needing purchase.
		$this->create_array_assets_needing_purchase();

		if ( isset( $this->assets_needing_purchase['by_page_id'] ) ) {
			foreach ( $this->assets_needing_purchase['by_page_id'] as $page_id => $array_of_assets ) {
				foreach ( $array_of_assets as $asset_key => $asset ) {
					$return[ $asset['asset_id'] ] = $asset['coin_cost'];
				}
			}
		} else {
			$return = false;
		}

		$this->local_publish_cost_data = $return;

		return $return;
	}

	/**
	 * Return post_status we will look for watermarked images within.
	 *
	 * By default, we will look for watermarked images within drafts and published pages.
	 *
	 * @since 1.1.4
	 *
	 * @return string A string to be used after in IN statement, example: ( 'draft', 'publish' ).
	 */
	public function get_post_status() {
		$post_status = array( 'draft', 'publish' );

		/**
		 * Allow other plugins to change the post_status we look for watermarked images within.
		 *
		 * By default, we will look within drafts and published pages. Other plugins, such as the
		 * BoldGrid Staging plugin, may want to look for watermarked images within staged pages.
		 *
		 * @since 1.1.4
		 *
		 * @param array $post_status The default post_status to look within for watermarked images.
		*/
		$post_status = apply_filters( 'boldgrid_cart_post_status', $post_status );

		// Implode our array of $post_status to be used alongside an IN statement.
		$post_status = '( "' . implode( '", "', esc_sql( $post_status ) ) . '" )';

		return $post_status;
	}

	/**
	 * Ajax calls come here to get details by transaction_item_id.
	 *
	 * When you are reviewing your transaction history and click 'View' for an invoice, this method
	 * gets data for each of your images.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 */
	public function get_purchased_image_details_callback() {
		// Connect WordPress database.
		global $wpdb;

		// Only Administrators can see transaction data.
		if( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		// Get and make sure we have a valid $transaction_item_id.
		$transaction_item_id = $_POST['transaction_item_id'];

		if ( ! is_numeric( $transaction_item_id ) ) {
			echo 'Invalid transaction_item_id';
			error_log( __METHOD__ . ': Invalid transaction_item_id: ' . print_r( $transaction_item_id, true ) );
			wp_die();
		}

		/**
		 * **********************************************************************
		 * Attempt to get our data based upon $transaction_item_id and
		 * the data in wp_options boldgrid_asset
		 * **********************************************************************
		 */

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$assetManager = new Boldgrid_Inspirations_Asset_Manager();

		// Grab the details of the asset based off of asset_id.
		$search_params = array(
			'by'                  => 'transaction_item_id',
			'transaction_item_id' => $transaction_item_id,
		);

		$asset = $assetManager->get_asset( $search_params );

		/*
		 * Currently, there is an 'active' cart and a 'staging' cart.
		 *
		 * If the user purchases an image in their staging cart, the transaction info will have been
		 * saved to boldgrid_staging_boldgrid_asset.
		 *
		 * Transactions are not flagged as active or staging.
		 *
		 * For any images purchased through the staged cart, the above call to get_asset() will fail
		 * because it will try to get the asset from the active assets, not the staging assets.
		 *
		 * IF the call to get_asset above returned false, try again, but force the search within
		 * staging assets.
		 */
		if( ! $asset ) {
			$search_params[ 'staging' ] = true;
			$asset = $assetManager->get_asset( $search_params );
		}

		// Get all the local data for the attachment id.
		if ( isset( $asset['attachment_id'] ) && is_numeric( $asset['attachment_id'] ) ) {
			$attachment_metadata = wp_prepare_attachment_for_js( $asset['attachment_id'] );

			if ( false != $attachment_metadata ) {
				$attachment_metadata['data_type'] = 'local_data';

				echo json_encode( $attachment_metadata );

				wp_die();
			}
		}

		/**
		 * **********************************************************************
		 * If we are here, then we've had a problem getting the attachment data.
		 * For example, the attachment may have been deleted.
		 *
		 * In this case, reach out to the asset server and get the image details
		 * **********************************************************************
		 */
		$boldgrid_configs = $this->get_configs();

		$url_to_get_image_details = $boldgrid_configs['asset_server'] .
			 $boldgrid_configs['ajax_calls']['image_get_details'];

		$arguments = array(
			'method'  => 'POST',
			'body'    => array(
				'user_transaction_item_id' => $transaction_item_id,
				'key'                      => $this->api->get_api_key_hash(),
			),
			'timeout' => 20,
		);

		$call_to_get_image_details = wp_remote_post( $url_to_get_image_details, $arguments );

		if ( is_wp_error( $call_to_get_image_details ) || '200' != $call_to_get_image_details['response']['code'] ) {
			esc_html_e( 'Unable to get image details.', 'boldgrid-inspirations' );

			error_log(
				__METHOD__ . ': Error: Could not retrieve image details from the asset server.  ' . print_r(
					array(
						'url' => $url_to_get_image_details,
						'arguments' => $arguments,
						'response' => $call_to_get_image_details,
					), true ) );

			wp_die();
		}

		$image_data = json_decode( $call_to_get_image_details['body'] );

		// If the remote data is bad, then log and exit.
		if ( empty( $image_data ) ) {
			error_log( __METHOD__ . ': Error in remote data call.  $call_to_get_image_details: ' . print_r( $call_to_get_image_details, true ) );
			esc_html_e( 'Unable to get image details from asset server.', 'boldgrid-inspirations' );
			wp_die();
		}

		/**
		 * **********************************************************************
		 * Check the media library for the image
		 * **********************************************************************
		 */

		if ( isset( $image_data->result->data->filename ) ) {
			// Get the table prefix.
			$table_prefix = $wpdb->prefix;

			// Retrieve the first attachment id from matching posts.
			$attachment_id = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT ID FROM ' . $table_prefix .
					"posts WHERE post_type='attachment' AND guid LIKE '%%%s%%' AND guid NOT LIKE '%%%s%%'",
					$image_data->result->data->filename,
					$image_data->result->data->filename . '-' ) );
		}

		if ( ! empty( $attachment_id ) ) {
			// Image does exist in the local media library.
			$attachment_metadata = wp_prepare_attachment_for_js( $attachment_id );

			if ( false != $attachment_metadata ) {
				$attachment_metadata['data_type'] = 'local_library_data';
				$attachment_metadata['attachment_id'] = $attachment_id;

				echo json_encode( $attachment_metadata );

				wp_die();
			}
		}

		/**
		 * **********************************************************************
		 * Send the details for the remote image:
		 * **********************************************************************
		 */
		$return_data = array(
			'data_type'         => 'remote_data',
			'thumbnail_url'     => $image_data->result->data->thumbnail_url,
			'id_from_provider'  => $image_data->result->data->id_from_provider,
			'image_provider_id' => $image_data->result->data->image_provider_id,
		);

		echo json_encode( $return_data );

		wp_die();
	}

	/**
	 * Get remote publish cost data.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 *
	 * @return boolean|unknown
	 */
	public function get_remote_publish_cost_data() {
		// If we don't have any items needing purchase.
		if ( false == $this->get_local_publish_cost_data() ) {
			return false;
		}

		// If the data exists, just return it.
		if ( isset( $this->remote_publish_cost_data ) ) {
			return $this->remote_publish_cost_data;
		}

		$boldgrid_configs = $this->get_configs();

		$url_to_get_remote_publish_cost_data = $boldgrid_configs['asset_server'] .
			 $boldgrid_configs['ajax_calls']['get-total-asset-cost'];

		$arguments = array(
			'method' => 'POST',
			'body'   => array(
				'key'       => $this->api->get_api_key_hash(),
				'cost_data' => $this->get_local_publish_cost_data(),
			)
		);

		$response = wp_remote_post( $url_to_get_remote_publish_cost_data, $arguments );

		// Error checking.
		if ( is_wp_error( $response ) ) {
			error_log( 'Error: Could not retrieve asset cost details from the asset server! ' . print_r( array(
				'Method'    => __METHOD__,
				'Error'     => '$response is wp_error',
				'url'       => $url_to_get_remote_publish_cost_data,
				'arguments' => $arguments,
				'response'  => $response,
			), true ) );

			return false;
		}

		$json_decode_response = json_decode( $response['body'] );

		$return = $json_decode_response->result->data;

		$this->remote_publish_cost_data = $return;

		return $return;
	}

	/**
	 * Determine if an asset needs to be published.
	 *
	 * IMPORTANT!
	 * This method is actually building the $this->assets_needing_purchase array.
	 * This is an important array used throughout this class.
	 * This method is currently only called by $this->create_array_assets_needing_purchase();
	 *
	 * @param array $asset An asset.
	 * @param string $asset_type An asset type.
	 * @param array $args An array of arguments.
	 * @return boolean
	 */
	public function asset_needs_purchase( $asset, $asset_type, $args = array() ) {
		$defaults['process_checked_in_cart_attribute'] = true;

		$args = wp_parse_args( $args, $defaults );

		global $wpdb;

		$attachment_id = (int) $asset['attachment_id'];

		// If an asset has a coin cost <= 0, then it doesn't need purchase.
		if ( $asset['coin_cost'] <= 0 ) {
			return false;
		}

		// If an asset already has a 'purchase_date', then it doesn't need purchase.
		if ( ! empty( $asset['purchase_date'] ) ) {
			return false;
		}

		/**
		 * On the cart page, there is a checkbox next to each image.
		 *
		 * IF the user has unchecked the box, meaning they don't want to purchase the image,
		 * THEN the asset does not need to be purchased (so return false).
		 *
		 * ********************************************************************
		 *
		 * This check is default behavior. To skep this check,
		 * false must == $args['process_checked_in_cart_attribute'].
		 *
		 * One time we would want to skip this is when we're getting all images not purchased,
		 * for the cart page itself for example.
		 */
		if ( true == $args['process_checked_in_cart_attribute'] ) {
			if ( isset( $asset['checked_in_cart'] ) && ! $asset['checked_in_cart'] ) {
				return false;
			}
		}

		$post_status = $this->get_post_status();

		/*
		 * Is this image used within shortcode? For example, is image 123 used in a gallery, such as
		 * [gallery ids='123,456'].
		 */
		$regexp = '[\[][^\]]+[\'\", ]' . $attachment_id . '[^0-9]+.*[\]]';
		$query = '
			SELECT `ID`
			FROM ' . $wpdb->posts . '
			WHERE	`post_status` IN ' . $post_status . ' AND
					`post_type` IN ("page","post") AND
					`post_content` REGEXP "' . $regexp . '"
		';
		$in_shortcode = $wpdb->get_var( $query );
		if ( ! empty( $in_shortcode ) ) {
			$this->assets_needing_purchase['by_page_id'][ $in_shortcode ][] = $asset;
			return true;
		}

		/**
		 * Is this a featured image needing attribution?
		 */
		$asset_a_featured_image = $wpdb->get_var(
			$wpdb->prepare(
				"	SELECT `post_id`
				FROM	$wpdb->postmeta,
				$wpdb->posts
				WHERE	$wpdb->postmeta.meta_key = '_thumbnail_id' AND
				$wpdb->postmeta.meta_value = %s AND
				$wpdb->postmeta.post_id = $wpdb->posts.ID AND
				$wpdb->posts.post_status IN $post_status AND
					$wpdb->posts.post_type IN ('page','post')
						", $attachment_id ) );

		// If we found results, then the image is being used in a page/post.
		if ( ! empty( $asset_a_featured_image ) ) {
			$this->assets_needing_purchase['by_page_id'][ $asset_a_featured_image ][] = $asset;
			return true;
		}

		/**
		 * Is this an image used within a page / post?
		 *
		 * // First, build a list of possible filenames for the asset
		 * // Then, loop through each filename and check if it is in a page / post
		 */

		/**
		 * ************************************************************************
		 * Build a list of possible filenames for the asset.
		 *
		 * $array_file_names_to_query may resemble this:
		 * (
		 * ____[0] => 4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf-150x150.jpg
		 * ____[1] => 4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf-300x214.jpg
		 * ____[2] => 2015/09/4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf.jpg
		 * ____[3] => cropped-4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf2.jpg
		 * ____[4] => cropped-4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf3.jpg
		 * ____[5] => cropped-4-FotoliaComp_91221240_2TBVzO8SQwN5i5TaKHnXpySRBK3xmZqf4.jpg
		 * )
		 * ************************************************************************
		 */

		/*
		 * We will create an array of names this asset could have. For example, the same asset might
		 * have been resized into several different files / thumbnails, and we need to check for all
		 * of them.
		 */
		$array_file_names_to_query = array();

		$wp_attachment_metadata = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );

		if ( ! empty( $wp_attachment_metadata ) ) {
			// Save this metadata for future use.
			$this->wp_options_asset[ $asset_type ][ $asset['asset_key'] ]['_wp_attachment_metadata'] = $wp_attachment_metadata;

			if ( ! empty( $wp_attachment_metadata['sizes'] ) ) {
				foreach ( $wp_attachment_metadata['sizes'] as $image_size ) {
					$array_file_names_to_query[] = $image_size['file'];
				}
			}
		}

		$wp_attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );

		if ( ! empty( $wp_attached_file ) ) {
			// Save this metadata for future use.
			$this->wp_options_asset[ $asset_type ][ $asset['asset_key'] ]['_wp_attached_file'] = $wp_attached_file;

			$array_file_names_to_query[] = $wp_attached_file;
		}

		/*
		 * When we crop images, we save the path to the new file. Let's add those paths to
		 * $array_file_names_to_query as well.
		 */
		if ( isset( $asset['crops'] ) && count( $asset['crops'] ) > 0 ) {
			foreach ( $asset['crops'] as $crops_key => $crops_array ) {
				$array_file_names_to_query[] = end( ( explode( '/', $crops_array['path'] ) ) );
			}
		}

		/**
		 * ************************************************************************
		 * Now that we have a list of $array_file_names_to_query, query them and see if they're used
		 * anywhere.
		 * ************************************************************************
		 */

		// Then, loop through each filename and check if it is in a page / post.

		/**
		 * Add the ability to exclude looking for images in certain pages.
		 *
		 * @since 1.3.7
		 *
		 * @param  array $not_in_page An array of pages not to include in the BoldGrid Cart.
		 */
		$not_in_page = apply_filters( 'boldgrid_cart_exclude_page', array() );

		if ( 'image' == $asset_type && ! empty( $array_file_names_to_query ) ) {
			foreach ( $array_file_names_to_query as $file_name_to_query ) {

				// SELECT post_title where post_content like
				// '%2015/02/google-maps-int-1410976385-pi.jpg%'
				$query = $wpdb->prepare("
					SELECT	`ID`
					FROM	$wpdb->posts
					WHERE	`post_content` LIKE %s AND
							`post_status` IN $post_status AND
							`post_type` IN ('page','post')
					",
					'%' . $wpdb->esc_like( $file_name_to_query ) . '%'
				);

				// If we want to exclude any page IDs, exclude them now.
				foreach( $not_in_page as $id ) {
					$query .= ' AND `ID` != ' . (int) $id;
				}

				$asset_in_page = $wpdb->get_results( $query );

				// If we found results, then the image is being used in a page/post.
				// Example $asset_in_page: http://pastebin.com/DSiGZFN7.
				if ( ! empty( $asset_in_page ) ) {
					foreach ( $asset_in_page as $page_object ) {
						$this->assets_needing_purchase['by_page_id'][ $page_object->ID ][] = $asset;
					}

					return true;
				}

				/**
				 * Is this a background image?
				 */
				$background_image = get_background_image();
				if ( substr( $background_image, ( - 1 * strlen( $file_name_to_query ) ) ) ==
						$file_name_to_query ) {
					$this->assets_needing_purchase['by_page_id']['Background and Header'][] = $asset;

					return true;
				}

				/**
				 * Is this a header image?
				 */
				$header_image = get_header_image();

				// Does the header image have "/cropped-" in it? If so, remove it so we can get a
				// match.
				// Example $header_image:
				// https://domain.com/wp-content/uploads/2015/09/cropped-4-FotoliaComp_83145029_xjIiigtqliUtXxXzXEtvkUtPVYylPgdD.jpg
				// Example $file_name_to_query:
				// 2015/09/4-FotoliaComp_69178126_KOvMJTRxmnoPWbTByK6hrCIsBuZoGxWR.jpg
				if ( 1 == substr_count( $header_image, '/cropped-' ) ) {
					$header_image = str_replace( '/cropped-', '/', $header_image );
				}
				if ( substr( $header_image, ( - 1 * strlen( $file_name_to_query ) ) ) ==
						$file_name_to_query ) {
					$this->assets_needing_purchase['by_page_id']['Background and Header'][] = $asset;

					return true;
				}
			}
		}

		// If we weren't able to find the asset being used in a page/post or as a featured image,
		// then return false for asset_needs_attribution.
		return false;
	}

	/**
	 * Get current coin balance.
	 *
	 * @see Boldgrid_Inspirations_Coins::__construct().
	 * @see Boldgrid_Inspirations_Coins::get_coin_balance().
	 *
	 * @return boolean
	 */
	public function get_current_copyright_coin_balance() {
		include_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-coins.php';

		$boldgrid_coins = new Boldgrid_Inspirations_Coins();

		$coin_balance = $boldgrid_coins->get_coin_balance();

		return $coin_balance;
	}
}
