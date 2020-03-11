<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Pages_And_Posts
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Pages And Posts.
 *
 * This is a generic class used for manipulating pages and posts.
 *
 * @since 1.0.8
 */
class Boldgrid_Inspirations_Pages_And_Posts {
	/**
	 * Add hooks.
	 *
	 * This method is generally only called via the Boldgrid_Inspirations_Inspiration Class.
	 *
	 * @since 1.0.8
	 */
	public function add_hooks() {
		if ( is_admin() ) {
			add_action( 'wp_trash_post',
				array (
					$this,
					'delete_page_from_menu_after_page_trash'
				) );

			add_action( 'untrash_post',
				array (
					$this,
					'restore_page_to_menu_after_untrash'
				) );

			add_filter( 'bulk_post_updated_messages',
				array (
					$this,
					'bulk_post_updated_messages'
				), 10, 2 );

			add_action( 'post_submitbox_misc_actions',
				array (
					$this,
					'post_submitbox_misc_actions_auto_add_to_menu'
				) );

			add_action( 'wp_insert_post', array (
				$this,
				'save_post_auto_add_to_menu'
			), 10, 3 );

			add_filter( 'post_updated_messages',
				array (
					$this,
					'post_updated_messages'
				) );

			add_action( 'admin_enqueue_scripts',
				array (
					$this,
					'admin_enqueue_scripts'
				) );
		}
	}

	/**
	 * Enqueue dashboard scripts.
	 *
	 * @since 1.0.8
	 *
	 * @global object $post Data from the current post in The Loop.
	 *
	 * @param  string $hook The $hook_suffix for the current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $post;

		$is_post_hook = ( 'post-new.php' === $hook || 'post.php' === $hook );
		$is_post      = ( ! empty( $post->post_type ) && ( 'page' === $post->post_type || 'post' === $post->post_type ) );

		if( $is_post_hook && $is_post ) {
			wp_enqueue_script( 'manage_menu_assignment_within_editor',
				plugins_url( '/assets/js/manage_menu_assignment_within_editor.js', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
				array (),
				BOLDGRID_INSPIRATIONS_VERSION,
				true
			);

			wp_register_style( 'boldgrid-in-menu-css',
				plugins_url( '/assets/css/boldgrid-in-menu.css', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
				array (),
				BOLDGRID_INSPIRATIONS_VERSION
			);
			wp_enqueue_style( 'boldgrid-in-menu-css' );
		}
	}

	/**
	 * Update bulk_post_updated_messages.
	 *
	 * These messages are displayed when you trash a page or untrash a page. For example, when you
	 * trash a page, you'll see the message, "1 page restored from the Trash.". These messages are
	 * being updated to mention we have also automatically added / removed the pages from menus. If
	 * a menu is added / removed from a menu, it is done by the following two methods in this class:
	 * delete_page_from_menu_after_page_trash and restore_page_to_menu_after_untrash.
	 *
	 * @since 1.0.8
	 *
	 * @param array $bulk_messages
	 *        	Arrays of messages, each keyed by the corresponding post type. Messages are
	 *        	keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
	 *        	Example $bulk_messages: http://pastebin.com/24WZ8XFM
	 * @param array $bulk_counts
	 *        	Array of item counts for each message, used to build internationalized strings.
	 * @return array $bulk_messages
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		// Determine if we have automatically added / removed pages from the menu.
		// It doesn't matter what the option values are, if they exist, it's assumed menus were
		// modifed.
		$boldgrid_page_trashed_from_menu_after_page_trashed = get_option(
			'boldgrid_page_trashed_from_menu_after_page_trashed' );
		$boldgrid_page_untrashed_from_menu_after_page_untrashed = get_option(
			'boldgrid_page_untrashed_from_menu_after_page_untrashed' );

		if ( false != $boldgrid_page_trashed_from_menu_after_page_trashed ) {
			$bulk_messages['page']['trashed'] = _n(
				'%s page moved to the Trash and removed from menus.',
				'%s pages moved to the Trash and removed from menus.', $bulk_counts['trashed'] );
		}

		if ( false != $boldgrid_page_untrashed_from_menu_after_page_untrashed ) {
			$bulk_messages['page']['untrashed'] = _n(
				'%s page restored from the Trash and added back to menus.',
				'%s pages restored from the Trash and added back to menus.',
				$bulk_counts['untrashed'] );
		}

		// Deleting the options below is resetting their values to false. See above as to why we are
		// deleting rather than emptying their value.
		delete_option( 'boldgrid_page_trashed_from_menu_after_page_trashed' );
		delete_option( 'boldgrid_page_untrashed_from_menu_after_page_untrashed' );

		return $bulk_messages;
	}

	/**
	 * Get menu items by post id.
	 *
	 * Pass a post ID and get any menu items that link to that post ID.
	 *
	 * @since 1.0.8
	 *
	 * @param integer $post_id
	 *        	A post ID.
	 * @return array Results of a generic $wpdb SELECT call.
	 *         See https://codex.wordpress.org/Class_Reference/wpdb#SELECT_Generic_Results.
	 *         Example results: http://pastebin.com/6d1JBS4q
	 */
	public function get_menu_items_by_post_id( $post_id ) {
		// Ensure $post_id is an integer.
		$post_id = ( int ) $post_id;

		global $wpdb;

		// Get all nav_menu_items that link to this post id.
		$querystr = "
		SELECT	$wpdb->postmeta.post_id
		FROM	$wpdb->postmeta
		WHERE	$wpdb->postmeta.meta_key = '_menu_item_object_id' AND
				$wpdb->postmeta.meta_value = $post_id;
		";

		// Example $menu_items: http://pastebin.com/6d1JBS4q
		$menu_items = $wpdb->get_results( $querystr, ARRAY_A );

		return $menu_items;
	}

	/**
	 * Get menu item objects linking a page to a menu.
	 *
	 * @since 1.0.8
	 *
	 * @global $wpdb
	 *
	 * @param int $post_id
	 *        	A post ID.
	 * @param int $menu_id
	 *        	A menu id (term_id).
	 * @return array Menu item objects that link pages to a menu.
	 */
	public function get_menu_item_object_by_post_and_menu( $post_id, $menu_id ) {
		global $wpdb;

		// Get the menu item object for this post / menu
		$querystr = "
		SELECT	$wpdb->posts.*
		FROM	$wpdb->term_relationships,
				$wpdb->postmeta,
				$wpdb->posts
		WHERE
				# Get the 'page' to 'menu' relationship in term_relationships
				$wpdb->term_relationships.term_taxonomy_id = $menu_id AND
				# Get the 'page' to 'page' relationship between posts and term_relationships
				$wpdb->term_relationships.object_id = $wpdb->posts.ID AND
				# Get the 'posts' to 'postsmeta'
				$wpdb->posts.ID = $wpdb->postmeta.post_id AND
				# The the 'post' that is a menu item for this page.
				$wpdb->postmeta.meta_key = '_menu_item_object_id' AND
				$wpdb->postmeta.meta_value = $post_id
		";

		return $wpdb->get_results( $querystr, ARRAY_A );
	}

	/**
	 * Return a list of menus a page belongs to.
	 *
	 * @since 1.0.8
	 *
	 * @param
	 *        	int A post ID.
	 * @return array A list of menus a page belongs to.
	 */
	public function get_menus_by_post_id( $post_id ) {
		// Ensure $post_id is an integer.
		$post_id = ( int ) $post_id;

		// An array of menu id's (term_id) that a page belongs to.
		$in_menu = array ();

		// Grab a list of all menus.
		// Example $nav_menus: http://pastebin.com/SExeMBT4
		$nav_menus = get_terms( 'nav_menu', array (
			'hide_empty' => false
		) );

		// If there are no nav menus, abort.
		if ( empty( $nav_menus ) ) {
			return array ();
		}

		// Loop through each nav menu. If the page is in the menu, add it to our $in_menu array.
		foreach ( $nav_menus as $nav_menu ) {
			if ( $this->page_in_menu( $post_id, $nav_menu->term_id ) ) {
				$in_menu[] = $nav_menu->term_id;
			}
		}

		return $in_menu;
	}

	/**
	 * After a page is trashed, trash the page from menus.
	 *
	 * @since 1.0.8
	 *
	 * @param integer $post_id
	 *        	A post ID.
	 */
	public function delete_page_from_menu_after_page_trash( $post_id ) {
		$menu_items = $this->get_menu_items_by_post_id( $post_id );

		// If we don't have any menu items, return;
		if ( empty( $menu_items ) ) {
			return;
		}

		// Loop through all menu_items and delete them.
		foreach ( $menu_items as $menu_item ) {
			$menu_item_id = $menu_item['post_id'];

			if ( is_nav_menu_item( $menu_item_id ) ) {
				// Remove this action in order to avoid an infinite loop.
				remove_action( 'wp_trash_post',
					array (
						$this,
						'delete_page_from_menu_after_page_trash'
					) );

				wp_trash_post( $menu_item_id );

				// Create this option to signify we have altered at least one menu based upon a page
				// being trashed.
				update_option( 'boldgrid_page_trashed_from_menu_after_page_trashed', true );

				// Infinite loop has been avoided, add this action back.
				add_action( 'wp_trash_post',
					array (
						$this,
						'delete_page_from_menu_after_page_trash'
					) );
			}
		}
	}

	/**
	 * Determine if a menu is configured with auto_add.
	 *
	 * "Auto add" refers to the option for a menu to, "Auto add pages: Automatically add new
	 * top-level pages to this menu".
	 *
	 * This code exists already natively within WordPress (wp-admin/nav-menus.php) , but not as a
	 * function.
	 *
	 * @since 1.0.8
	 *
	 * @param int $menu_id
	 *        	The id of a menu.
	 * @return boolean Is this menu configured with auto_add?
	 */
	public function menu_configured_with_auto_add( $menu_id ) {
		$auto_add = get_option( 'nav_menu_options' );

		if ( ! isset( $auto_add['auto_add'] ) ) {
			$auto_add = false;
		} elseif ( false !== array_search( $menu_id, $auto_add['auto_add'] ) ) {
			$auto_add = true;
		} else {
			$auto_add = false;
		}

		return $auto_add;
	}

	/**
	 * Determine if a page belongs to a specific menu.
	 *
	 * @since 1.0.8
	 *
	 * @link http://wordpress.stackexchange.com/questions/75607/check-if-page-is-in-a-certain-menu
	 *
	 * @param int $post_id
	 *        	A post ID.
	 * @param int $menu_id
	 *        	A menu id (term_id).
	 * @return boolean Does this page belong to this menu?
	 */
	public function page_in_menu( $post_id, $menu_id ) {
		// Ensure $post_id and $menu_id are integers.
		$post_id = ( int ) $post_id;
		$menu_id = ( int ) $menu_id;

		// Get the menu object.
		$menu_object = wp_get_nav_menu_items( $menu_id );

		// Abort if this is not a menu.
		if ( ! $menu_object ) {
			return false;
		}

		// get the object_id field out of the menu object
		$menu_items = wp_list_pluck( $menu_object, 'object_id' );

		// test if the specified page is in the menu or not. return true or false.
		return in_array( $post_id, $menu_items );
	}

	/**
	 * Create HTML for the page/post edit form menu manager post submitbox.
	 *
	 * This method collects and parses through data, and then includes a template at the end to
	 * display the data.
	 *
	 * @since 1.0.8
	 *
	 * @global string $pagenow.
	 * @global object $post.
	 */
	public function post_submitbox_misc_actions_auto_add_to_menu() {
		global $pagenow;
		global $post;

		// Determine if this is a new page, or we're editing an exist page.
		$is_new_page = ( 'post-new.php' == $pagenow ? '1' : '0' );

		// Grab a list of all menus.
		// Example $nav_menus: http://pastebin.com/SExeMBT4
		$nav_menus = get_terms( 'nav_menu', array (
			'hide_empty' => false
		) );

		// Initialize $checkbox_data.
		$original_selections = null;

		// If there are no nav menus, abort with a message.
		if ( empty( $nav_menus ) ) {
			// Print a message.
			$nav_menus_html = '<div class="active staging">' . esc_html__( 'There are no menus to select.', 'boldgrid-inspirations' ) . '</div>';

			// Include the page template.
			require BOLDGRID_BASE_DIR .
				 '/pages/includes/post_submitbox_misc_actions_auto_add_to_menu.php';

			// Pass the selected checkboxes to the JavaScript.
			wp_localize_script( 'manage_menu_assignment_within_editor', 'original_selections',
				$original_selections );

			return;
		}

		// Get menu locations.
		$menu_locations = get_nav_menu_locations();

		// Get registered nav menus.
		$menu_names = get_registered_nav_menus();

		// Create HTML that contains a list of navigation menus preceded by checkboxes.
		$nav_menus_html = '';

		// Track if there are any active menus.
		$has_active_menus = false;

		// Track if there are any staging menus.
		$has_staging_menus = false;

		// Iterate through nav menus.
		foreach ( $nav_menus as $nav_menu ) {
			/*
			 * Determine if this menu should be checked.
			 * 1. If it is a new page, check the primary menu.
			 * 2. If it is an existing page, check all menus the page currently belongs to.
			 */
			if ( 'post-new.php' == $pagenow ) {
				// If the menu is called "Primary", check it by default.
				$checked = ( 'primary' == strtolower( $nav_menu->name ) ? 'checked' : '' );
			} else {
				$checked = ( $this->page_in_menu( $post->ID, $nav_menu->term_id ) ? 'checked' : '' );
			}

			// Add $nav_menu->name to $checkbox_data.
			if ( 'checked' == $checked ) {
				$original_selections[] = $nav_menu->name;
			}

			// Initialize $menu_location_names.
			$menu_location_names = array ();

			// Get this menu's location name key.
			foreach ( $menu_locations as $key => $value ) {
				if ( $value == $nav_menu->term_id && isset( $menu_names[$key] ) ) {
					$menu_location_names[] = $menu_names[$key];
				}
			}

			// Convert $menu_location_names from an array into an unordered list.
			$menu_location_list = '<ul><li>';

			if ( count( $menu_location_names ) > 0 ) {
				$menu_location_list .= implode( '</li><li>', $menu_location_names );
			} else {
				$menu_location_list .= '<i>' . esc_html__( 'Not in a menu location.', 'boldgrid-inspirations' ) . '</i>';
			}

			$menu_location_list .= '</li></ul>';

			// Create the html for the checkbox itself.
			$checkbox = '<input type="checkbox" name="boldgrid_auto_add_to_menus[]" id="boldgrid-auto-add-to-menus" value="' .
				 $nav_menu->term_id . '" ' . $checked . ' data-menu-name="' .
				 esc_attr( $nav_menu->name ) . '">';

			// Configure css classes for this menu's div. By default, there are no classes.
			$div_classes = array ();

			/*
			 * Add css classes to this menu's div.
			 * Allow other plugins to add classes to the div containing each checkbox. For example,
			 * the BoldGrid Staging plugin will add active / staging classes, to help track if this
			 * menu is an active or staging menu.
			 * @since 1.0.8
			 * @param array $div_classes
			 * See declaration of $div_classes immediately above.
			 * @param string $nav_menu->name
			 * The name of this navigation menu.
			 */
			$div_classes = apply_filters(
				'boldgrid_div_classes_post_submitbox_misc_actions_auto_add_to_menu', $div_classes,
				$nav_menu->name );

			// Convert the $div_classes array into a space separated string of class names.
			$div_classes = ( empty( $div_classes ) ? null : implode( ' ', $div_classes ) );

			// Create the div tag.
			// If we have no $div_classes to add, omit the class attribute.
			$div_tag = ( empty( $div_classes ) ? '<div>' : '<div class="' . esc_attr( $div_classes ) .
				 '">' );

			$nav_menus_html .= $div_tag . $checkbox . ' ' . esc_html( $nav_menu->name ) .
				 $menu_location_list . '</div>';

			// If there is an active menu, then update $has_active_menus, else define a div.
			if ( false !== strpos( $div_classes, 'active' ) ) {
				$has_active_menus = true;
			} else {
				// Define an active div.
				$active_div_tag = str_replace( 'staging', 'active', $div_tag );
			}

			// If there is a staging menu, then update $has_staging_menus, else define a div.
			if ( false !== strpos( $div_classes, 'staging' ) ) {
				$has_staging_menus = true;
			} else {
				// Define an staging div.
				$staging_div_tag = str_replace( 'active', 'staging', $div_tag );
			}
		}

		// If there are no active menus, then print a message.
		if ( ! $has_active_menus && '<div>' != $active_div_tag ) {
			$nav_menus_html .= $active_div_tag . '<i>' . esc_html__( 'There are no menus to select.', 'boldgrid-inspirations' ) . '</i></div>';
		} else

		// If there are no staging menus, then print a message.
		if ( ! $has_staging_menus && '<div>' != $staging_div_tag ) {
			$nav_menus_html .= $staging_div_tag . '<i>' . esc_html__( 'There are no menus to select.', 'boldgrid-inspirations' ) . '</i></div>';
		}

		// Include the page template.
		require BOLDGRID_BASE_DIR .
			 '/pages/includes/post_submitbox_misc_actions_auto_add_to_menu.php';

		// Pass the selected checkboxes to the JavaScript.
		wp_localize_script( 'manage_menu_assignment_within_editor', 'original_selections',
			$original_selections );
	}

	/**
	 * Modify the post_updated_messages.
	 *
	 * When you publish a page, a success message is shown in your dashboard. This message will be
	 * similar to, "Page published. View page".
	 *
	 * If the user is utilizing our feature to auto add a new page to a menu, we want to update this
	 * message to reflect the page was added to a menu as well. This "auto add" is done by the
	 * save_post_auto_add_to_menu method in this class.
	 *
	 * @since 1.0.8
	 *
	 * @param array $messages
	 *        	Post updated messages. For defaults, see wp-admin/edit-form-advanced.php.
	 *        	Example $messages: http://pastebin.com/QrggEcep
	 */
	public function post_updated_messages( $messages ) {
		// Determine if we have automatically added a new page to a menu. It doesn't matter what the
		// option value is set to, if it exists, it's assumed a new menu item was added.
		$boldgrid_new_page_added_to_menu_via_auto_add = get_option(
			'boldgrid_new_page_added_to_menu_via_auto_add' );

		// Update the "Page published" success message if we auto added the page to a menu.
		// 6 is hard coded below as it is hard coded in wp-admin/edit-form-advanced.php.
		if ( false != $boldgrid_new_page_added_to_menu_via_auto_add ) {
			$messages['page'][6] = str_replace( 'Page published',
				'Page published and added to menu', $messages['page'][6] );
		}

		// Deleting the option below is resetting its value to false. See above as to why we are
		// deleting rather than emptying the value of the option.
		delete_option( 'boldgrid_new_page_added_to_menu_via_auto_add' );

		return $messages;
	}

	/**
	 * After a page is untrashed, untrash the page from menus.
	 *
	 * @since 1.0.8
	 *
	 * @param integer $post_id
	 *        	A post ID.
	 */
	public function restore_page_to_menu_after_untrash( $post_id ) {
		$menu_items = $this->get_menu_items_by_post_id( $post_id );

		// If we don't have any menu items, return;
		if ( empty( $menu_items ) ) {
			return;
		}

		// Loop through all menu_items and delete them.
		foreach ( $menu_items as $menu_item ) {
			$menu_item_id = $menu_item['post_id'];

			if ( is_nav_menu_item( $menu_item_id ) ) {
				// Remove this action in order to avoid an infinite loop.
				remove_action( 'untrash_post',
					array (
						$this,
						'restore_page_to_menu_after_untrash'
					) );

				wp_untrash_post( $menu_item_id );

				// Create this option to signify we have altered at least one menu based upon a page
				// being untrashed.
				update_option( 'boldgrid_page_untrashed_from_menu_after_page_untrashed', true );

				// Infinite loop has been avoided, add this action back.
				add_action( 'untrash_post',
					array (
						$this,
						'restore_page_to_menu_after_untrash'
					) );
			}
		}
	}

	/**
	 * Update page menu assignment on page save.
	 *
	 * Within the page editor in the post submitbox, there is a new section that allows you to
	 * manage which menus the page you're editing belongs to. When you save a page, this method is
	 * triggered to add / remove the page from a menu.
	 *
	 * @since 1.0.8
	 *
	 * @param int $post_id
	 *        	The post ID.
	 * @param post $post
	 *        	The post object.
	 * @param bool $update
	 *        	Whether this is an existing post being updated or not.
	 */
	public function save_post_auto_add_to_menu( $post_id, $post, $update ) {
		// Only post_types of page are suppored at this time. If this is not a page, abort.
		if ( 'page' != $post->post_type ) {
			return;
		}

		// If this is not a published page, abort. We don't want to add a "pending review" page to a
		// menu.
		$abort_due_to_post_status = ( 'publish' != $post->post_status );

		/**
		 * Determine if we should allow the menu management of this post type.
		 *
		 * See initial comment for $abort_due_to_post_status above. This filter allows other
		 * post_types, such as staging, to be added / removed from a menu.
		 *
		 * @since 1.0.8
		 *
		 * @param boolean $abort_due_to_post_status
		 *        	See above.
		 * @param object $post
		 *        	The post object.
		 */
		$abort_due_to_post_status = apply_filters(
			'boldgrid_save_post_auto_add_to_menu_abort_due_to_post_status',
			$abort_due_to_post_status, $post );

		if ( true == $abort_due_to_post_status ) {
			return;
		}

		// The save_post action can be triggered by an import, post/page edit form, xmlrpc, or post
		// by email.
		// @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
		// This method should only be ran when saving a page from the post/page edit form.
		// It is on this page that we add a hidden input variable,
		// boldgrid_auto_add_to_menu_page_id.
		// If this variable is not set within $_REQUEST, assume we are not saving from the form, and
		// abort.
		if ( empty( $_REQUEST['boldgrid_auto_add_to_menu_page_id'] ) ) {
			return;
		}

		// If the $post_id does not match the page we were editing, abort.
		// For example, the attribution page is saved when any page is saved. It also passes all of
		// the requirements above. This check is to ensure the attribution page, and other auto
		// updated pages, are not mistakenly added / removed from a menu. Only the page specified by
		// $_REQUEST['boldgrid_auto_add_to_menu_page_id'] can be modified here.
		if ( $_REQUEST['boldgrid_auto_add_to_menu_page_id'] != $post_id ) {
			return;
		}

		// This is an array of menu id's the user checked on the page/post editor.
		$new_menu_assignment = ( isset( $_REQUEST['boldgrid_auto_add_to_menus'] ) &&
			 is_array( $_REQUEST['boldgrid_auto_add_to_menus'] ) ? $_REQUEST['boldgrid_auto_add_to_menus'] : array () );

		// This is an array of menu id's the page currently belongs to.
		$current_menu_assignment = $this->get_menus_by_post_id( $post->ID );

		// Grab a list of all menus.
		// Example $nav_menus: http://pastebin.com/SExeMBT4
		$nav_menus = get_terms( 'nav_menu', array (
			'hide_empty' => false
		) );

		// Iterate through nav menus.
		foreach ( $nav_menus as $nav_menu ) {
			$menu_id = ( int ) $nav_menu->term_id;

			// If the menu does not exist, abort.
			if ( false == wp_get_nav_menu_object( $menu_id ) ) {
				continue;
			}

			// If this menu is already configured to automatically add new pages, abort.
			// We don't want the same menu item added twice.
			if ( true == $this->menu_configured_with_auto_add( $menu_id ) ) {
				continue;
			}

			// To make the logic easier to read below, assign the following two vars:
			$page_in_current_menu_assignment = in_array( $menu_id, $current_menu_assignment );
			$page_in_new_menu_assignment = in_array( $menu_id, $new_menu_assignment );

			/*
			 * Using the two vars above, the following scenarios exist:
			 * [X] current [ ] new = Remove from menu.
			 * [ ] current [X] new = Add to menu.
			 * [X] current [X] new = No action.
			 * [ ] current [ ] new = No action.
			 * Because only two of the four scenarios above require action, we will only check for
			 * those two.
			 */

			// The user no longer wants this page in the given menu, so remove it.
			// [X] current [ ] new = Remove from menu.
			if ( $page_in_current_menu_assignment && ! $page_in_new_menu_assignment ) {
				$menu_items = $this->get_menu_item_object_by_post_and_menu( $post_id, $menu_id );

				// If we did not find any menu items that match, abort.
				if ( empty( $menu_items ) ) {
					return;
				}

				// Loop through all the menus items (though there should only be one), and trash
				// them (IE remove the page from the menu).
				foreach ( $menu_items as $menu_item ) {
					wp_trash_post( $menu_item['ID'] );
				}
			}

			// The user is trying to add the page to selected menus.
			// [ ] current [X] new = Add to menu.
			if ( ! $page_in_current_menu_assignment && $page_in_new_menu_assignment ) {
				$menu_item_db_id = wp_update_nav_menu_item( $menu_id, 0,
					array (
						'menu-item-object-id' => $post->ID,
						'menu-item-parent-id' => 0,
						'menu-item-object' => 'page',
						'menu-item-type' => 'post_type',
						'menu-item-status' => 'publish'
					) );

				// Create this option to signify we have added a new page to a menu via this method.
				update_option( 'boldgrid_new_page_added_to_menu_via_auto_add', true );
			}
		}
	}
}
