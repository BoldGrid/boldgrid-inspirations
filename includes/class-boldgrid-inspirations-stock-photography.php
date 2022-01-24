<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Stock_Photography
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Stock Photography class.
 */
class Boldgrid_Inspirations_Stock_Photography extends Boldgrid_Inspirations {
	/**
	 * The Boldgrid Inspirations Asset Manager class object.
	 *
	 * @var Boldgrid_Inspirations_Asset_Manager
	 */
	private $asset_manager;

	/**
	 * Add dashboard media tabs.
	 *
	 * @param string $hook
	 */
	public function add_dashboard_media_tabs( $hook ) {
		global $pagenow;

		$pages_to_add_menu = array(
			'upload.php',
			'media-new.php',
		);

		if ( in_array( $pagenow, $pages_to_add_menu, true ) ) {
			// Determine which page should be active
			$is_library = ( 'upload.php' == $pagenow && ( ! isset( $_GET['page'] ) ||
				 ( isset( $_GET['page'] ) && 'boldgrid-connect-search' != $_GET['page'] ) ) );

			$is_boldgrid_connect_search = ( 'upload.php' == $pagenow && isset( $_GET['page'] ) &&
				 'boldgrid-connect-search' == $_GET['page'] );

			$is_upload = ( 'media-new.php' == $pagenow );

			$library_active = true == $is_library ? 'nav-tab-active' : '';

			$boldgrid_connect_search_active = true == $is_boldgrid_connect_search ? 'nav-tab-active' : '';

			$upload_active = true == $is_upload ? 'nav-tab-active' : '';

			?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="upload.php" class="nav-tab <?php echo $library_active; ?>"><?php esc_html_e( 'Media Library' ); ?></a>
		<a href="media-new.php" class="nav-tab <?php echo $upload_active; ?>"><?php esc_html_e( 'Add New Media' ); ?></a>
		<a href="upload.php?page=boldgrid-connect-search" class="nav-tab <?php echo $boldgrid_connect_search_active; ?>"><?php esc_html_e( 'BoldGrid Connect Search', 'boldgrid-inspirations' ); ?></a>
	</h2>
</div>
<?php
		}
	}

	/**
	 * Hooks required for the Stock Photography class
	 */
	public function add_hooks() {
		add_filter( 'media_upload_tabs',
			array (
				$this,
				'custom_media_upload_image_search'
			) );

		add_action( 'media_upload_image_search',
			array (
				$this,
				'custom_media_upload_tab_content'
			) );

		add_action( 'admin_enqueue_scripts', array (
			$this,
			'enqueue_header_content'
		) );

		// Handle the user clicking "Download and insert into page"
		add_action( 'wp_ajax_download_and_insert_into_page',
			array (
				$this,
				'download_and_insert_into_page_callback'
			) );

		if ( is_admin() ) {
			// Add a sub menu item to "Media", "BoldGrid Connect Search".
			add_action( 'admin_menu',
				array (
					$this,
					'register_boldgrid_connect_search_page'
				) );

			add_action( 'admin_notices', array (
				$this,
				'add_dashboard_media_tabs'
			) );

			add_filter( 'ajax_query_attachments_args',
				array (
					$this,
					'ajax_query_attachments_args'
				) );

			/**
			 * When an image is cropped, save the cropping details.
			 *
			 * The following 2 filters are used for this:
			 *
			 * # wp_create_file_in_uploads - wp_create_file_in_uploads (header image)
			 * # crop-image - wp_create_file_in_uploads (background image)
			 */
			add_filter( 'wp_create_file_in_uploads',
				array (
					$this,
					'wp_create_file_in_uploads'
				), 10, 2 );

			add_action( 'crop-image', array (
				$this,
				'wp_create_file_in_uploads'
			) );
		}
	}

	/**
	 * Filter query attachment args.
	 *
	 * When this method was initially comitted almost one year ago, it lacked a description. Looking
	 * through commit logs, this method was including in a commit that added BoldGrid Connect
	 * Search to the Customizer. I believe this filter adjusts the images in the media library so
	 * that the newest ones appear first. If you download an image with BoldGrid Connect Search,
	 * this method ensures it will show first in your media library.
	 *
	 * @param  array $query An array of query variables.
	 * @return array
	 */
	public function ajax_query_attachments_args( $query = array() ) {
		/*
		 * We do not want to adjust query attachment args when an ajax request is being made for
		 * images in a gallery. IF WE DID adjust the order of that query, the images would always
		 * return in DESC order, instead of the specific order the user set them in (dragged / dropped).
		 *
		 * There is not a specific flag that says a query is for gallery images. However, gallery ajax
		 * calls do set the limit to -1, so that all images in the gallery are returned.
		 *
		 * If this is a call for a gallery, don't modify the query, just return.
		 */
		if( isset( $query[ 'posts_per_page' ] ) && -1 == $query[ 'posts_per_page' ] ) {
			return $query;
		}

		$query['orderby'] = 'ID';
		$query['order'] = 'DESC';

		return $query;
	}

	/**
	 * Generate the css / js / html for the Dashboard >> Media >> BoldGrid Connect Search page.
	 */
	public function boldgrid_connect_search_page() {
		?>
<style>
.wrap-boldgrid-connect-search h2 {
	margin-bottom: 16px;
}

iframe#boldgrid_connect_search {
	width: 100%;
	height: calc(100% - 20px);
	min-height: 420px;
	border: 1px solid #ddd;
}
</style>

<div class="wrap wrap-boldgrid-connect-search">
	<h2><?php  esc_html_e( 'BoldGrid Connect Search', 'boldgrid-inspirations' ); ?></h2>
	<iframe id="boldgrid_connect_search"
		src="media-upload.php?chromeless=1&tab=image_search&ref=dashboard-media"></iframe>
</div>
<?php
	}

	/**
	 * Add tab for custom media upload image search
	 *
	 * @param array $tabs
	 */
	public function custom_media_upload_image_search( $tabs ) {
		$newtab['image_search'] = esc_html__( 'Image Search', 'boldgrid-inspirations' );

		return array_merge( $tabs, $newtab );
	}

	/**
	 * Add custom media upload tab content
	 */
	public function custom_media_upload_tab_content() {
		wp_iframe( array (
			$this,
			'include_image_search_php'
		) );
	}

	/**
	 * Handle the user clicking "Download and insert into page"
	 *
	 * This method is triggered generally throughout all stock image downloads via ajax. Even though
	 * the method name "insert into page" implies you're editing a page, you could actually be in
	 * Dashboard > Media > BoldGrid Connect Search.
	 *
	 * @param string $_POST['caption']
	 * @param string $_POST['alignment']
	 * @param string $_POST['alt_text']
	 * @param string $_POST['title']
	 * @param int $_POST['post_id']
	 * @param string $_POST['image_provider_id']
	 * @param string $_POST['id_from_provider']
	 * @param string $_POST['image_size']
	 * @param string $_POST['width']
	 * @param string $_POST['height']
	 */
	public function download_and_insert_into_page_callback() {
		global $wpdb;

		// An array of info to send back to the browser.
		$response = array();

		// Access the post object.
		$post = ! empty( $_POST['post_id'] ) && get_post_status( $_POST['post_id'] ) ? get_post( $_POST['post_id'] ) : null;

		/*
		 * Capability check.
		 *
		 * If the user is editing a page / post, make sure they have permission to do so.
		 *
		 * Else, do a general check. Make sure they have permission to upload files.
		 */
		if( $post ) {
			$cap = ( 'page' == $post->post_type ) ? 'edit_page' : 'edit_post';
			current_user_can( $cap, $post->ID ) ? : wp_die();
		} elseif( ! current_user_can( 'upload_files' ) ) {
			wp_die();
		}

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';

		$this->asset_manager = new Boldgrid_Inspirations_Asset_Manager();

		$boldgrid_configs = $this->get_configs();

		$item = array (
			'type'   => 'stock_photography_download',
			'params' => array (
				'key'               => $boldgrid_configs['api_key'],
				'id_from_provider'  => (int) $_POST['id_from_provider'],
				'image_provider_id' => (int) $_POST['image_provider_id'],
				'image_size'        => $_POST['image_size'],
				'width'             => isset( $_POST['width'] ) ? (int) $_POST['width'] : null,
				'height'            => isset( $_POST['height'] ) ? (int) $_POST['height'] : null
			)
		);

		/*
		 * Configure our $post_id for the download_and_attach_asset call.
		 *
		 * If we don't have a post, then set to false. We won't have a post, for example, when we're
		 * within Dashboard > Media > BGCS.
		 */
		$post_id = ( is_null( $post ) ? false : $post->ID );

		$image = $this->asset_manager->download_and_attach_asset( $post_id, $item, null, 'all', false );

		$response['attachment_id'] = $image['attachment_id'];

		$response['attachment_url'] = wp_get_attachment_url( $image['attachment_id'] );

		// Config attributes for image tag, if we have a caption:
		if ( ! empty( $_POST['caption'] ) ) {
			$caption = sanitize_text_field( $_POST['caption'] );

			$caption_id = 'id="attachment_' . $image['attachment_id'] . '"';

			$caption_align = 'align="align' . sanitize_text_field( $_POST['alignment'] ) . '"';

			$image_class = '';
		} else {
			$image_class = 'class="align' . sanitize_text_field( $_POST['alignment'] ) . ' wp-image-' .
				 $image['attachment_id'] . '"';
		}

		$image_alt_text = ( empty( $_POST['alt_text'] ) ? '' : 'alt="' .
			 sanitize_text_field( $_POST['alt_text'] ) . '"' );

		// If inseting into a page in the editor, then include markup:
		if ( isset( $image['headers']['z-width'] ) && $image['headers']['z-height'] ) {
			$image_width_and_height = 'width="' . $image['headers']['z-width'] . '" height="' .
				 $image['headers']['z-height'] . '"';

			// Return / echo the image tag, if we have a caption:
			if ( ! empty( $_POST['caption'] ) ) {
				$response['html_for_editor'] = '[caption ' . $caption_id . ' ' . $caption_align .
					 '] <img src="' . $image['uploaded_url'] . '" ' . $image_width_and_height . ' ' .
					 $image_alt_text . ' ' . $image_class . ' />' . $caption . '[/caption]';
			} else {
				$response['html_for_editor'] = '<img src="' . $image['uploaded_url'] . '" ' .
					 $image_width_and_height . ' ' . $image_alt_text . ' ' . $image_class . ' />';
			}
		}

		echo json_encode( $response );

		wp_die();
	}

	/**
	 * Include image search PHP file
	 */
	public function include_image_search_php() {
		include BOLDGRID_BASE_DIR . '/pages/image_search.php';

		do_action( 'boldgrid_image_search_post_form' );
	}

	/**
	 * Add a sub menu item to "Media", "BoldGrid Connect Search".
	 */
	public function register_boldgrid_connect_search_page() {
		add_submenu_page( 'upload.php', 'BoldGrid Connect Search', 'BoldGrid Connect Search',
			'manage_options', 'boldgrid-connect-search',
			array (
				$this,
				'boldgrid_connect_search_page'
			) );
	}

	/**
	 * When an image is cropped, save the cropping details.
	 *
	 * ************************************************************************
	 * When cropping a header image via Customizer, the following is sent:
	 * ************************************************************************
	 *
	 * Array
	 * (
	 * _____[$_POST] => Array
	 * _____(
	 * __________[nonce] => 0f203facbc
	 * __________[id] => 22953
	 * __________[cropDetails] => Array
	 * __________(
	 * _______________[x1] => 0
	 * _______________[y1] => 650
	 * _______________[x2] => 2048
	 * _______________[y2] => 944
	 * _______________[width] => 2048
	 * _______________[height] => 294
	 * __________)
	 * __________[action] => custom-header-crop
	 * _____)
	 *
	 * _____[$path] =>
	 * __________/home/user/public_html/wp-content/uploads/2015/09/cropped-1-7970107050_0e4f031b6b_k1.jpg
	 * ______[$attachment_id] => 22953
	 * )
	 *
	 * ************************************************************************
	 * When cropping a background image via Customizer, the following is sent:
	 * ************************************************************************
	 *
	 * wp_customize:on
	 * nonce:xyzxyzxyz
	 * id:24187
	 * context:background_image
	 * cropDetails[x1]:0
	 * cropDetails[y1]:0
	 * cropDetails[x2]:700
	 * cropDetails[y2]:393
	 * cropDetails[width]:700
	 * cropDetails[height]:393
	 * cropDetails[dst_width]:1920
	 * cropDetails[dst_height]:1080
	 * action:crop-image
	 */
	public function wp_create_file_in_uploads( $path, $attachment_id ) {
		// If this is not a custom-header-crop, abort.
		if ( ! ( isset( $_POST['cropDetails'] ) and is_array( $_POST['cropDetails'] ) ) ) {
			return $path;
		} else {
			$cropDetails = $_POST['cropDetails'];

			// If we don't have a dst_width:
			if ( ! isset( $cropDetails['dst_width'] ) ) {
				$cropDetails['dst_width'] = ( isset( $cropDetails['width'] ) ) ? $cropDetails['width'] : null;
			}

			// If we don't have a dst_height:
			if ( ! isset( $cropDetails['dst_height'] ) ) {
				$cropDetails['dst_height'] = ( isset( $cropDetails['height'] ) ) ? $cropDetails['height'] : null;
			}
		}

		// If we don't have a post action, abort.
		if ( ! isset( $_POST['action'] ) ) {
			return path;
		}

		$allowed_actions = array (
			'custom-header-crop',
			'crop-image'
		);

		if ( ! in_array( $_POST['action'], $allowed_actions ) ) {
			return $path;
		}

		// Get the asset.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$this->asset_manager = new Boldgrid_Inspirations_Asset_Manager();

		$asset = $this->asset_manager->get_asset(
			array(
				'by' => 'attachment_id',
				'attachment_id' => $attachment_id
			)
		);

		// If this is not an asset, abort.
		if ( false == $asset ) {
			return $path;
		}

		// Add the new crop details to the asset.
		$asset['crops'][] = array (
			'cropDetails' => $cropDetails,
			'path' => $path
		);

		// Update the asset.
		$this->asset_manager->update_asset(
			array (
				'task' => 'update_entire_asset',
				'asset_id' => $asset['asset_id'],
				'asset' => $asset,
				'asset_type' => 'image',
			) );

		$asset = $this->asset_manager->get_asset(
			array (
				'by' => 'attachment_id',
				'attachment_id' => $attachment_id
			) );

		return $path;
	}

	/**
	 * Register styles/scripts
	 *
	 * @global string $post_type
	 * @global object $wp_customize
	 */
	public function enqueue_header_content( $hook ) {
 		global $post_type;
 		global $wp_customize;

 		$in_customizer = isset( $wp_customize );

 		$tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : null );
 		$in_image_search = ( 'media-upload-popup' === $hook && 'image_search' == $tab );

		$post_hooks = array( 'post.php', 'post-new.php' );
		$skip_post_types = array( 'attachment' );
 		$in_page_editor = in_array( $hook, $post_hooks ) && ! in_array( $post_type, $skip_post_types );

		/*
		 * Load the necessary js/css for the BoldGrid Connect Search.
		 *
		 * These scripts are loaded within the media upload popup, which is
		 * usually loaded within an iframe.
		 */
		if ( $in_image_search ) {
 			wp_enqueue_media();

 			$handle = 'image_search';
			wp_register_script(
				$handle,
				plugins_url( '/assets/js/image_search.js', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
				array (),
				BOLDGRID_INSPIRATIONS_VERSION,
				true
			);
			wp_localize_script(
				$handle,
				'BoldGridImageSearch',
				array(
					'downloading'         => esc_html__( 'Downloading image...', 'boldgrid-inspirations' ),
					'imageDownloaded'     => esc_html__( 'Image downloaded!', 'boldgrid-inspirations' ),
					'loadingImageDetails' => esc_html__( 'Loading image details', 'boldgrid-inspirations' ),
					'noMore'              => esc_html__( 'No more search results', 'boldgrid-inspirations' ),
					'noSearchResults'     => esc_html__( 'No search results.', 'boldgrid-inspirations' ),
					'noSearchTerm'        => esc_html__( 'Please enter a search term.', 'boldgrid-inspirations' ),
					'scrollDown'          => sprintf(
						// translators: 1 Opening strong tag, 2 closing strong tag.
						__( '%1$sScroll down%2$s or %1$sclick here%2$s to load more search results', 'boldgrid-inspirations' ),
						'<strong>',
						'</strong>'
					),
					'searching'           => esc_html__( 'Searching', 'boldgrid-inspirations' ),
					'viewInLibrary'       => esc_html__( 'View image in Media Library', 'boldgrid-inspirations' ),
				)
			);
			wp_enqueue_script( $handle );

			wp_register_style( 'wp_iframe-media_upload',
				plugins_url( '/' . basename( BOLDGRID_BASE_DIR ) . '/assets/css/wp_iframe-media_upload.css' ),
				array (),
				BOLDGRID_INSPIRATIONS_VERSION
			);
			wp_enqueue_style( 'wp_iframe-media_upload' );

			wp_enqueue_style( 'boldgrid-inspirations-font-awesome' );

			/**
			 * Actions to take when we're in the BoldGrid Connect Search iframe.
			 *
			 * @since 1.47
			 */
			do_action( 'boldgrid_image_search_scripts' );
		}

		/*
		 * Enqueue insert-media-tab-manager.js
		 *
		 * This js file listens to clicks of "Add media" and handles the
		 * display of the BoldGrid Connect Search tab.
		 */
		if ( $in_page_editor || $in_customizer ) {
			$handle = 'insert-media-tab-manager';
			wp_register_script(
				$handle,
				plugins_url( '/assets/js/insert-media-tab-manager.js', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
				array(),
				BOLDGRID_INSPIRATIONS_VERSION,
				true
			);
			wp_localize_script( $handle, 'BoldGridInspirationsMediaTab', array(
				'Change'      => __( 'Change', 'boldgrid-inspirations' ),
				'editImage'   => __( 'Edit image', 'boldgrid-inspirations' ),
				'imageSearch' => __( 'Image Search', 'boldgrid-inspirations' ),
				'loading'     => __( 'Loading BoldGrid Connect Search.', 'boldgrid-inspirations' )
			));
			wp_enqueue_script( $handle );
		}
	}
}
