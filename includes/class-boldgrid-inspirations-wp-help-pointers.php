<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_WP_Help_Pointers
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid WP Help Pointer class.
 *
 * @link https://github.com/rawcreative/wp-help-pointers
 */
class Boldgrid_WP_Help_Pointers {
	public $screen_id;
	public $valid;
	public $pointers;

	/**
	 * My Pointers.
	 *
	 * @since 2.7.6
	 *
	 * @var array
	 */
	public $my_pointers = array();

	/**
	 * Initial Pointers.
	 *
	 * @since 2.7.6
	 *
	 * @var array
	 */
	public $initial_pointers = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// Don't run on WP < 3.3
		if ( get_bloginfo( 'version' ) < '3.3' ) {
			return;
		}

		// get and set the screen id
		$screen = get_current_screen();
		$this->screen_id = $screen->id;

		// get boldgrid_pointers from options table.
		$this->get_pointers();

		// this will soon be deprecated, but for now, set our initial pointers.
		$this->add_initial_pointers();

		// only filters assigned to this screen are applicable.
		$this->filter_pointers_by_screen();

		// Get dismissed pointers
		$dismissed = explode( ',',
			( string ) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Distraction-free writing - dismiss this by default. Sorry WordPress.
		if ( ! in_array( 'wp410_dfw', $dismissed ) ) {
			$dismissed[] = 'wp410_dfw';
			update_user_meta( get_current_user_id(), 'dismissed_wp_pointers',
				implode( ',', $dismissed ) );
		}
	}

	/**
	 * Add actions to configure WP pointers
	 */
	public function add_hooks() {
		add_action( 'admin_enqueue_scripts',
			array (
				&$this,
				'admin_enqueue_scripts_wp_pointer'
			), 1000 );

		add_action( 'admin_head', array (
			&$this,
			'admin_head_print_our_pointers'
		) );
	}

	/**
	 * Add initial pointers
	 */
	public function add_initial_pointers() {
		/**
		 * First we define our pointers
		 */
		$this->initial_pointers = array (
			array (
				// unique id for this pointer
				'id'       => 'boldgrid_image_search_internal_only_8',
				// this is the page hook we want our pointer to show on
				'screen'   => 'page',
				// the css selector for the pointer to be tied to, best to use ID's
				'target'   => '#media-search-input',
				'title'    => esc_html__( 'Image search', 'boldgrid-inspirations' ),
				// translators: 1 opening em tag, 2 closing em tag, 3 opening strong tag, 4 closing strong tag.
				'content'  => sprintf( __( '%1$sThis search function%2$s helps you find images you\'ve already uploaded to your Media Library. If you would like to search the web for new images, click the %3$sBoldGrid Connect Search%4$s tab in the top menu.', 'boldgrid-inspirations' ), '<em>', '</em>', '<strong>', '</strong>' ),
				'position' => array (
					// top, bottom, left, right
					'edge'  => 'right',
					'align' => 'middle'
				)
			),
			// Onboarding Progress
			array(
				// unique id for this pointer
				'id'       => 'boldgrid_inspirations_onboarding_progress',
				// this is the page hook we want our pointer to show on
				'screen'   => 'admin_page_my-inspiration',
				// the css selector for the pointer to be tied to, best to use ID's
				'target'   => '#toplevel_page_boldgrid-inspirations',
				'title'    => esc_html__( 'BoldGrid Inspirations Progress', 'boldgrid-inspirations' ),
				'content'  => __(
					'Your progress throughout the BoldGrid Inspirations process is displayed here. You can also click on "Inspirations" at any time to return to the checklist.',
					'boldgrid-inspirations'
				),
				'position' => array(
					// top, bottom, left, right
					'edge'  => 'left',
					'align' => 'middle',
				),
			),
			// Dashboard >> Media >> Library >> Search
			array (
				'id'       => 'boldgrid_media_library_image_search_internal_only',
				'screen'   => 'upload',
				'target'   => '#media-search-input',
				'title'    => esc_html__( 'Image search', 'boldgrid-inspirations' ),
				// translators: 1 opening em tag, 2 closing em tag, 3 opening strong tag, 4 closing strong tag.
				'content'  => sprintf( __( '%1$sThis search function%2$s helps you find images you\'ve already uploaded to your Media Library. If you would like to search the web for new images, click the %3$sBoldGrid Connect Search%4$s tab in the top menu.', 'boldgrid-inspirations' ), '<em>', '</em>', '<strong>', '</strong>' ),
				'position' => array (
					'edge'              => 'top',
					'align'             => 'middle',
					'open_on_page_load' => false
				)
			),
			array (
				'id'       => 'boldgrid_image_size_do_you_need_help_8',
				'screen'   => 'media-upload',
				// the css selector for the pointer to be tied to, best to use ID's
				'target'   => '#image_size',
				'title'    => esc_html__( 'Image size', 'boldgrid-inspirations' ),
				'content'  => esc_html__( 'Need help choosing an image size?', 'boldgrid-inspirations' ),
				'position' => array (
					// top, bottom, left, right
					'edge'  => 'right',
					'align' => 'middle'
				)
			),
			array (
				'id'       => 'boldgrid_customization_widget',
				'screen'   => 'dashboard',
				// the css selector for the pointer to be tied to, best to use ID's
				'target'   => '#customization_widget',
				'title'    => esc_html__( 'Begin customizing your new website!', 'boldgrid-inspirations' ),
				'content'  => esc_html__( 'Congratulations, you\'ve just installed your new website! Below you\'ll find tips to help you begin customizing your site.', 'boldgrid-inspirations' ),
				'position' => array (
					// top, bottom, left, right
					'edge'  => 'bottom',
					'align' => 'middle'
				)
			)
		);

		// loop through each of these initial pointers
		foreach ( $this->initial_pointers as $pointer ) {
			$this->set_pointer( $pointer );
		}
	}

	/**
	 * IF we have pointers AND they have not been dismissed
	 * THEN enqueue wp-pointer styles / scripts
	 */
	public function admin_enqueue_scripts_wp_pointer() {
		// $pointers has already been filtered by screen id, this was done in
		// $this->filter_pointers_by_screen();
		$pointers = $this->pointers;

		// If we don't have any pointers:
		if ( empty( $pointers ) || ! is_array( $pointers ) ) {
			return;
		}

		// Dismissed pointers:
		// $dismissed = Array
		// (
		// [0] => wp410_dfw
		// [1] => test-ab1
		// [2] => test_ab2
		// [3] => test_aa1
		// )
		$dismissed = explode( ',',
			( string ) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		$valid_pointers = array ();

		// Check pointers and remove dismissed ones:
		// // [$pointer_id] => boldgrid_image_search_internal_only_8
		// // [$pointer] => Array
		// // // (
		// // // // [screen] => page
		// // // // [target] => #media-search-input
		// // // // [options] => Array
		// // // // // (
		// // // // // // [content] => Pointer content goes here....
		// // // // // // [position] => Array
		// // // // // // // (
		// // // // // // // // [edge] => right
		// // // // // // // // [align] => middle
		// // // // // // // )
		// // // // // )
		// // // )
		foreach ( $pointers as $pointer_id => $pointer ) {
			// Make sure we have pointers & check if they have been dismissed
			if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) ||
				 empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
				continue;
			}

			$pointer['pointer_id'] = $pointer_id;

			// Add the pointer to $valid_pointers array
			$valid_pointers['pointers'][] = $pointer;
		}

		// No valid pointers? Stop here.
		if ( empty( $valid_pointers ) ) {
			return;
		}

		// $this->valid are pointers for this screen_id that have not been dismissed.
		$this->valid = $valid_pointers;

		// enqueue wordpress' js/css for pointers
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}

	/**
	 * Add necessary jQuery code to header
	 */
	public function admin_head_print_our_pointers() {
		$pointers = $this->valid;

		if ( empty( $pointers ) ) {
			return;
		}

		// Create a pointer index.
		foreach ( $pointers['pointers'] as $pointer_key => $pointer_data ) {
			$pointer_index[$pointer_data['target']] = $pointer_key;
		}

		$pointers = json_encode( $pointers );
		$pointer_index = json_encode( $pointer_index );

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'WPHelpPointer = ' . $pointers . ';' );
		Boldgrid_Inspirations_Utility::inline_js_oneliner(
			'WPHelpPointerIndex = ' . $pointer_index . ';' );
		Boldgrid_Inspirations_Utility::inline_js_file( 'print_pointers_in_header.js' );
	}

	/**
	 * Get pointers
	 */
	public function get_pointers() {
		// ON HOLD
		// ********************************************************************
		// In the future, we'll use hooks to set pointers to get_option( 'boldgrid_pointers' );
		// For now, we'll use this file to set the pointers.
		// ********************************************************************
		// $this->my_pointers = get_option( 'boldgrid_pointers' );
		//
		// // create and save blank array of pointers if it doesn't exist.
		// if ( ! is_array( $this->my_pointers ) ) {
		// $this->my_pointers = array ();
		// update_option( 'boldgrid_pointers', $this->my_pointers );
		// }
		$this->my_pointers = array ();
	}

	/**
	 * Add a pointer based off of 'id'.
	 *
	 * If 'id' already exists, then overwrite it.
	 *
	 * @param unknown $pointer
	 */
	public function set_pointer( $pointer ) {
		/*
		 * loop through each pointer to see if this one exists
		 */
		foreach ( $this->my_pointers as $existing_pointer_key => $existing_pointer ) {
			// if pointer exists...
			if ( $existing_pointer['id'] == $pointer['id'] ) {
				// overwrite it
				$this->my_pointers[$existing_pointer_key] = $pointer;

				update_option( 'boldgrid_pointers', $this->my_pointers );

				return true;
			}
		}

		/*
		 * If the pointer already existed, we would have updated it and returned above.
		 * Since we're here, this is a new pointer.
		 */
		$this->my_pointers[] = $pointer;

		update_option( 'boldgrid_pointers', $this->my_pointers );

		return true;
	}

	/**
	 * Configure the pointers / tooltips that we want to show to the user.
	 */
	public function filter_pointers_by_screen() {
		/*
		 * Loop through each of our pointers.
		 * If it is assigned to the current screen->id, then add it to $this->pointers
		 */
		foreach ( $this->my_pointers as $ptr ) {
			if ( $ptr['screen'] == $this->screen_id ) {
				$pointers[$ptr['id']] = array (
					'screen' => $ptr['screen'],
					'target' => $ptr['target'],
					'options' => array (
						'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
							__( $ptr['title'], 'plugindomain' ),
							__( $ptr['content'], 'plugindomain' ) ),
						'position' => $ptr['position']
					)
				);
			}
		}

		if ( isset( $pointers ) ) {
			$this->pointers = $pointers;
		}
	}
}
