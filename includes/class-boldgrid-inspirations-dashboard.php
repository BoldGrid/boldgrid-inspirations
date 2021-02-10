<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Dashboard
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Dashboard class.
 */
class Boldgrid_Inspirations_Dashboard extends Boldgrid_Inspirations {

	/**
	 * A link to the customizer.
	 *
	 * @since 1.2.12
	 * @var string
	 */
	public $link_to_customizer;

	/**
	 * Constructor.
	 *
	 * @since 1.2.12
	 */
	public function __construct() {
		$this->link_to_customizer = esc_url( add_query_arg( 'return', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'customize.php' ) );
		$this->api = new Boldgrid_Inspirations_Api( $this );
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		if ( is_admin() ) {
			Boldgrid_Inspirations_Feedback::enqueue_js();

			add_action( 'wp_dashboard_setup',
				array(
					$this,
					'add_dashboard_widget',
				)
			);

			// if in admin add CSS and JS to dashboard for widget and styling
			add_action( 'admin_enqueue_scripts',
				array(
					$this,
					'enqueue_script_dashboard',
				)
			);

			// If option is marked to rearrange admin menus.
			if ( Boldgrid_Inspirations_Config::use_boldgrid_menu() ) {
				/*
				 * Check if we are using multisite or not, then change our hook location and
				 * priority accordingly.
				 *
				 * @bugfix JIRA WPB-687.
				 */
				// If not using multisite.
				( ! is_multisite() ?

					// Remove WP core's editor submenu item via admin init.
					add_action( 'admin_init',
						array(
							$this,
							'boldgrid_remove_editor',
						),
						105
					) :

					// Or if using multisite, then remove the action before it happens on single site.
					remove_action( 'admin_menu',
						array(
							$this,
							'_add_themes_utility_last',
						),
						104
					)
				);

				// Then rearrange them.
				add_action( 'admin_menu',
					array(
						$this,
						'boldgrid_admin_menu',
					),
					1435
				);

				// And remove customizer submenu items from our packed array.
				add_action( 'admin_menu',
					array(
						$this,
						'boldgrid_remove_customizer',
					),
					999
				);

				// The 1000 priority below is a copy of the priority BoldGrid Staging is using.
				add_action( 'admin_menu', array( $this, 'customize_active_theme' ), 1000 );
			} else {
				// Create a single menu item.
				add_action( 'admin_menu',
					array(
						$this,
						'boldgrid_admin_one_menu_add',
					),
					999
				);
			}
		}
	}

	// Rearrange our plugin menu items into single menu item.
	public function boldgrid_admin_one_menu_add() {
		! Boldgrid_Inspirations_Config::use_boldgrid_menu() ? remove_menu_page( 'boldgrid-inspirations' ) : false;

		// Define our menu name.
		$top_level_menu = 'boldgrid-inspirations';

		// Add main boldgrid menu.
		add_menu_page(
			'Inspirations',
			'Inspirations',
			'manage_options', $top_level_menu,
			array(
				$this,
				'boldgrid_admin_one_menu_add',
			),
			'dashicons-lightbulb',
			'4.37'
		);

		// Add Inspirations as first child.
		add_submenu_page(
			$top_level_menu,
			'Inspirations',
			'Inspirations',
			'manage_options',
			$top_level_menu
		);

		// If the BoldGrid Staging plugin is not active, add "Customize Active" to the BoldGrid menu.
		if( ! is_plugin_active( 'boldgrid-staging/boldgrid-staging.php' ) ) {
			add_submenu_page(
				$top_level_menu,
				__( 'Customize Active', 'boldgrid-inspirations' ),
				__( 'Customize Active', 'boldgrid-inspirations' ),
				'edit_theme_options',
				$this->link_to_customizer
			);
		}

		// Add any bold grid
		global $boldgrid_inspiration_menu_items;

		if ( isset( $boldgrid_inspiration_menu_items[0] ) &&
		'Inspiration' === $boldgrid_inspiration_menu_items[0] ) {

			add_submenu_page(
				$top_level_menu,
				__( 'Install First Inspiration', 'boldgrid-inspirations' ),
				__( 'Install First Inspiration', 'boldgrid-inspirations' ),
				'manage_options',
				$top_level_menu
			);
		}
	}

	/**
	 * Reorder Menus for BoldGrid Admin Dash.
	 */
	public function boldgrid_admin_menu() {
		// WP global variable for menus.
		global $menu;

		// WP global variable for submenus.
		global $submenu;

		// Rename Posts menu item to Blog Posts.
		$menu[5][0] = 'Blog Posts';

		// Rename Appearance menu item to Customize
		if( current_user_can( 'edit_theme_options' ) ) {
			$menu[60][0] = 'Customize';
			$menu[60][6] = 'dashicons-admin-customize';
		}

		// Rename Reading Submenu item to Blog
		if( current_user_can( 'manage_options' ) ) {
			$submenu['options-general.php'][20][0] = 'Blog';
		}

		// Remove Background from Admin Menu.
		Boldgrid_Inspirations_Admin_Menu::remove_submenu_page(
			'themes.php',
			__( 'Background' )
		);

		// Remove Header submenu item from Appearances.
		Boldgrid_Inspirations_Admin_Menu::remove_submenu_page(
			'themes.php',
			__( 'Header' )
		);

		// Capability check.
		if ( current_user_can( 'manage_options' ) ) {

			// Activate custom menu order.
			add_filter( 'custom_menu_order',
				array(
					$this,
					'boldgrid_reorder_admin_menus',
				)
			);

			// Filter custom menu order to menu order.
			add_filter( 'menu_order',
				array(
					$this,
					'boldgrid_reorder_admin_menus',
				)
			);

			// Remove Themes submenu section.
			remove_submenu_page( 'themes.php', 'themes.php' );

			// Remove editor, which is added via menu API by WP core to submenu item under
			// Appearances.
			remove_action( 'admin_menu', '_add_themes_utility_last', 101 );

			// Remove Comments from menu since creating submenu for it under "Blog Posts" aka WP's
			// Posts.
			remove_menu_page( 'edit-comments.php' );

			// Add Comments as submenu item to Blog Posts (aka Posts).
			add_submenu_page(
				'edit.php',
				__( 'Comments' ),
				__( 'Comments' ),
				'moderate_comments',
				esc_url( 'edit-comments.php' )
			);

			// Add Change Themes submenu item.
			add_submenu_page(
				'themes.php',
				__( 'Change Themes', 'boldgrid-inspirations' ),
				__( 'Change Themes', 'boldgrid-inspirations' ),
				'edit_themes',
				esc_url( 'themes.php' )
			);
		}

		// Reorder Widgets Submenu item if it exists.
		if ( current_theme_supports( 'widgets' ) ) {

			// Remove Widgets option if permissions grant it.
			remove_submenu_page( 'themes.php', 'widgets.php' );

			// If WP Version 3.9.0 or higher is used.
			if ( version_compare( get_bloginfo( 'version' ), '3.9.0' ) >= 1 ) {
				add_theme_page(
					// Page Title.
					__( 'Widgets' ),

					// Menu Title.
					__( 'Widgets' ),

					// Give users access to this feature if they are capable of editing theme
					// options.
					'edit_theme_options',

					esc_url(
						add_query_arg(
							array(
								array(
									'autofocus' =>array(
										'panel' => 'widgets',
									),
								),
								'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
							),
							'customize.php'
						)
					)
				);

				// End of submenu item 'Widgets' to add under our 'Customize' menu item.
			} else {

				// Add our submenu item in.
				add_theme_page(
					__( 'Widgets' ),
					__( 'Widgets' ),
					'edit_theme_options',
					'widgets.php'
				);
				// End of adding Widgets submenu item.
			}
		}

		/*
		 * Build a link to 'menus' submenu item for new customizer menu management interface
		 * introduced in WP version 4.3.
		 *
		 * Escaped URL will build the link from whatever page user is on, and then our query will
		 * contain the return URL. This is important for the return path for when user leaves the
		 * customizer, so we don't inconveinece them by sending them back to the same static page
		 * each time.
		 *
		 * @urlencode: This function is convenient when encoding a string to be used in a query
		 * part of a URL, as a way to pass variables to the next page that will work properly with
		 * browsers.
		 *
		 * Since the link needs to be secure and escaped, we will remove the slashes properly with
		 * WP.
		 *
		 * @since 0.18
		 *
		 * @link https://codex.wordpress.org/Function_Reference/wp_unslash
		 */

		// Only apply this is the current theme supports menus.
		if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) ) {

			// Remove Menus option if permissions grant it.
			remove_submenu_page( 'themes.php', 'nav-menus.php' );

			// If user is using WordPress v 4.3+.
			if ( version_compare( get_bloginfo( 'version' ), '4.3.0' ) >= 1 ) {

				// Create our submenu item for Menus under Customize.
				add_theme_page(
					// Page Title.
					__( 'Menus' ),

					// Menu Title.
					__( 'Menus' ),

					// Give users access to this feature if they are capable of editing theme
					// options.
					'edit_theme_options',

					// Build URL and make sure it's escaped to avoid XSS attacks.
					esc_url(
						// Build our query.
						add_query_arg(

							// Pack it in an array.
							array(
								// Autofocus will open customizer and bring focus on to an element.
								array(
									'autofocus' =>
									// There we will bring focus to the actual menu panel in the
									// customizer.
									array(
										'panel' => 'nav_menus',
									),
								),
								// We want to get the proper URL encoded and without slashes since
								// we are escaping our URL.
								'return' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) )
							),
							// End of array.

							// root page to apply our query to
							'customize.php'
						)
					)
				);

				// End of our query argument.

				// End of escaped URL build.

				// End of submenu 'menus' to add under our 'Customize' menu item.
			} else {

				// Add submenu item Menus back into menu in our new order without building
				// customizer link.
				add_submenu_page(
					'themes.php',
					__( 'Menus' ),
					__( 'Menus' ),
					'edit_theme_options',
					'nav-menus.php'
				);
			}
		}

		// Add Editor into submenu renamed as CSS/HTML Editor.
		add_theme_page(
			__( 'CSS/HTML Editor', 'boldgrid-inspirations' ),
			__( 'CSS/HTML Editor', 'boldgrid-inspirations' ),
			'manage_options',
			esc_url( 'theme-editor.php' )
		);
	}

	/**
	 * Remove customizer.
	 */
	public function boldgrid_remove_customizer() {
		// Pack arrays for customizer URLs on various WP versions to remove.
		$customize_url_arr = array();

		$customize_url = add_query_arg(
			'return',
			urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
			'customize.php'
		);

		$customize_url_arr[] = $customize_url; // 4.0 & 4.1.

		if ( current_theme_supports( 'custom-header' ) && current_user_can( 'customize' ) ) {
			$customize_url_arr[] = add_query_arg(
				'autofocus[control]',
				'header_image',
				$customize_url
			); // 4.1.
			$customize_url_arr[] = 'custom-header'; // 4.0.
		}

		if ( current_theme_supports( 'custom-background' ) && current_user_can( 'customize' ) ) {
			$customize_url_arr[] = add_query_arg(
				'autofocus[control]',
				'background_image',
				$customize_url
			); // 4.1.
			$customize_url_arr[] = 'custom-background'; // 4.0.
		}

		foreach ( $customize_url_arr as $customize_url ) {
			remove_submenu_page( 'themes.php', $customize_url );
		}
	}

	/*
	 * Remove editor.
	 *
	 * Which is added via menu API by WP core to submenu item under Appearances.
	 */
	public function boldgrid_remove_editor() {
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
	}

	/*
	 * Add CSS and JS to admin dashboard.
	 */
	public function enqueue_script_dashboard( $hook ) {
		if ( 'index.php' === $hook ) {

			wp_register_style(
				'boldgrid-dashboard-css',
				plugins_url(
					'assets/css/boldgrid-dashboard.css', BOLDGRID_BASE_DIR . '/includes'
				),
				array(),
				BOLDGRID_INSPIRATIONS_VERSION
			);

			// The dashboard css contains styles for the feedback widget.
			wp_enqueue_style( 'boldgrid-dashboard-css' );
		}
	}

	// Reorder menu items if core plugin loaded and no staging.
	public function boldgrid_reorder_admin_menus( $menu_ord ) {

		// If called then return new array of menu items.
		if ( ! $menu_ord )
			return true;

		// Array of menu items to invoke and reorder.
		return array (
			'index.php', // Dashboard.
			'boldgrid-inspirations', // Inspirations.
			'themes.php', // Customize.
			'edit.php?post_type=page', // Pages.
			'upload.php', // Media.
			'edit.php', // Blog Posts.
			'separator1', // First Separator.
			'boldgrid-tutorials', // Tutorals.
			'plugins.php', // Plugins.
			'users.php', // Users.
			'tools.php', // Tools.
			'options-general.php', // Settings.
			'separator2', // Second separator.
			'boldgrid-transactions', // Receipts.
			'separator-last',
		); // Last separator.
	}

	/**
	 * Add "Active Theme" to Customizer menu.
	 *
	 * This is to ensure a consistent UI w/ and w/o the Staging plugin being installed. Either
	 * scenario, a "Customize > Active Theme" option will be in the menu.
	 *
	 * @since 1.2.12
	 */
	public function customize_active_theme() {
		// The BoldGrid Staging plugin is doing something similar. If Staging is active, abort.
		if( is_plugin_active( 'boldgrid-staging/boldgrid-staging.php' ) ) {
			return;
		}

		/*
		 * Add the menu item.
		 *
		 * This code is pretty much copied from the BoldGrid Staging plugin,
		 * class-boldgrid-staging-dashboard-menus.php.
		 */
		add_theme_page(
			__( 'Active Site', 'boldgrid-inspirations' ),
			__( 'Active Site', 'boldgrid-inspirations' ),
			'edit_theme_options',
			$this->link_to_customizer
		);
	}

	/**
	 * Creates the BoldGrid Feedback Widget in dashboard.
	 *
	 * @since 1.2.2
	 */
	public function boldgrid_feedback_widget() {
		// Get the admin email address.
		$user_email = '';

		if ( function_exists( 'wp_get_current_user' ) &&
		false !== ( $current_user = wp_get_current_user() ) ) {
			$user_email = $current_user->user_email;
		}

		include BOLDGRID_BASE_DIR . '/pages/templates/feedback-widget.php';
	}

	/**
	 * Adds the widgets we created to the WordPress dashboard.
	 *
	 * @since 1.2.2
	 */
	public function add_dashboard_widget() {
		if ( current_user_can( 'edit_dashboard' ) && ! class_exists( '\Boldgrid\Library\Library\Notice\KeyPrompt', false ) ) {
			wp_add_dashboard_widget(
				'boldgrid_feedback_widget',
				esc_html__( 'BoldGrid Feedback', 'boldgrid-inspirations' ),
				array(
					$this,
					'boldgrid_feedback_widget',
				)
			);
		}
	}
}
