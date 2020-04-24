<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Built
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Inspiration Built class.
 */
class Boldgrid_Inspirations_Built {

	/**
	 * An instance of Boldgrid_Inspirations_Inspiration.
	 *
	 * @var Inspiration
	 */
	protected $inspiration;

	/**
	 * The data gathered about the users scenario.
	 *
	 * @var array
	 */
	protected $mode_data;

	/**
	 * The users installation settings.
	 *
	 * @var array
	 */
	protected $install_options;

	/**
	 * Bool that checks if staging plugin is active.
	 *
	 * @var Bool
	 */
	protected $staging_plugin_active = false;

	/**
	 * Array of theme names.
	 *
	 * @var array
	 */
	protected $current_theme_names;

	/**
	 * Take in the main plugin as a param.
	 *
	 * @param Boldgrid_Inspirations_Inspiration $inspiration
	 */
	public function __construct( $inspiration ) {
		$this->inspiration = $inspiration;
	}

	public $design_first = true;

	/**
	 * Add actions/hooks
	 */
	public function add_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/*
		 * The Inspirations left menu items in the dashboard have menu items, "Inspirations" and
		 * "My Inspiration". We don't want to show "My Inspirations" however as a menu item.
		 * Therefore, if the user is trying to access "Inspirations" and they've already deployed
		 * a site, redirect them to "My Inspiration".
		 */
		if ( self::is_inspirations_page() && Boldgrid_Inspirations_Installed::has_built_site() && empty( $_GET['force'] ) ) {
			Boldgrid_Inspirations_My_Inspiration::redirect();
		}

		if ( self::is_inspirations() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts', ) );

			add_filter( 'Boldgrid\Library\Library\Notice\KeyPrompt_display', '__return_true' );

			// Add a custom header to the key prompt, just for Inspirations.
			add_filter( 'Boldgrid\Library\Views\KeyPrompt\header', function() {
				return __( 'We\'re almost ready to start your site!', 'boldgrid-inspirations' );
			} );

			/*
			 * On the Inspirations page, do not show the key prompt by default. It will get shown when
			 * the time is right, otherwise the user will see the key prompt flash on the screen and
			 * then disappear.
			 */
			add_filter( 'Boldgrid\Library\Views\KeyPrompt\classes', function( array $classes ) {
				$classes[] = 'hidden';
				return $classes;
			} );

			/*
			 * Make Inspirations full screen by adding the .bginsp-full-screen class to the body. If we
			 * did this via js, the normal dashboard would show and then flash to the full screen mode,
			 * giving a bad user experience.
			 */
			add_filter( 'admin_body_class', function( $classes ) {
				$classes .= ' bginsp-full-screen';
				return $classes;
			} );

			/*
			 * If we're on the Inspirations page, the "Get a key" link should be modified so that
			 * after the user gets a key, they are redirected back to the Inspirations process.
			 */
			add_filter( 'Boldgrid\Library\Key\returnUrl', function() {
				return admin_url( 'admin.php?page=boldgrid-inspirations&force=1&section=design' );
			} );
		}
	}

	/**
	 * Adds inline CSS to admin_head using the active color palette.
	 *
	 * @since xxx
	 */
	public function admin_colors() {
		global $_wp_admin_css_colors;
		$this->admin_colors = $_wp_admin_css_colors;
		$user = get_user_option( 'admin_color' );
		echo '<style>
			.pageset-option.active,.coin-option.active,.sub-category.active,.pageset-option.blue,.coin-option.blue,.blue { background-color:' . $this->admin_colors[ $user ]->colors[3] . ' !important; }
			.devices button:focus { border-bottom-color: ' . $this->admin_colors[ $user ]->colors[3] . '; }
			</style>';
	}

	/**
	 * Generate the scenario data and add the users menu items.
	 */
	public function admin_menu() {
		$this->staging_plugin_active = $this->check_staging_plugin();

		$this->add_top_menu_item( 'boldgrid-inspirations' );

		self::add_sub_menu_items( 'boldgrid-inspirations' );
	}

	/**
	 * Checks to see if the staging plugin is active.
	 *
	 * @return boolean
	 */
	public function check_staging_plugin() {
		$staging_plugin_active = is_plugin_active( 'boldgrid-staging/boldgrid-staging.php' );

		return ( $staging_plugin_active && class_exists( 'Boldgrid_Staging_Page_And_Post_Staging' ) );
	}

	/**
	 * Returns the name of a theme if and only if the theme is a boldgrid theme.
	 *
	 * @param WP_Theme $wp_theme
	 *
	 * @return string
	 */
	public static function get_boldgrid_theme_name( $wp_theme ) {
		$current_boldgrid_theme = '';

		$current_theme = $wp_theme;

		if ( is_a( $current_theme, 'WP_Theme' ) &&
			 strpos( strtolower( $current_theme->get( 'TextDomain' ) ), 'boldgrid' ) !== false ) {
			$current_boldgrid_theme = $current_theme->get( 'Name' );
		}

		return $current_boldgrid_theme;
	}

	/**
	 * Get all pages by status.
	 *
	 * @param string $post_status
	 *
	 * @return array
	 */
	public static function get_installed_pages( $post_status ) {
		$all_pages = get_pages(
			array(
				'post_status' => array(
					$post_status,
					'draft',
				),
			) );

			if ( false == is_array( $all_pages ) ) {
				$all_pages = array();
			}

			$boldgrid_pages = array();

			foreach ( $all_pages as $page ) {
				$post_meta = get_post_meta( $page->ID );
				if ( isset( $post_meta['boldgrid_page_id'] ) ) {
					$boldgrid_pages[] = $post_meta['boldgrid_page_id'][0];
				}
			}

			return $boldgrid_pages;
	}

	/**
	 * Find the users installation data.
	 *
	 * @return array
	 */
	public static function find_all_install_options() {
		// Get Installed Settings.
		( $active_install_options = get_option( 'boldgrid_install_options' ) ) ||
			 ( $active_install_options = array() );

			 $active_install_options['installed_pages'] = self::get_installed_pages( 'publish' );
			 $active_install_options['theme_stylesheet'] = get_stylesheet();
			 $active_install_options['theme_name'] = self::get_boldgrid_theme_name( wp_get_theme() );

			 $install_options['active_options'] = $active_install_options;

			 ( $staging_install_options = get_option( 'boldgrid_staging_boldgrid_install_options' ) ) ||
			 ( $staging_install_options = array() );

			 $staging_install_options['installed_pages'] = self::get_installed_pages( 'staging' );
			 $staging_install_options['theme_name'] = self::get_boldgrid_theme_name(
			 $staging_theme = self::get_staging_theme() );

			 if ( $staging_theme ) {
				 $staging_install_options['theme_stylesheet'] = $staging_theme->get_stylesheet();
				}

				$install_options['boldgrid_staging_options'] = $staging_install_options;
				$install_options['theme_release_channel'] = Boldgrid_Inspirations_Theme_Install::fetch_theme_channel();

				return $install_options;
	}

	/**
	 * Check to see if the user has a staged site.
	 *
	 * @return boolean
	 */
	public function has_staged_site() {
		$has_staged_site = false;

		if ( $this->staging_plugin_active ) {
			$staged_pages = Boldgrid_Staging_Page_And_Post_Staging::get_all_staged_pages();
			$staged_pages = is_array( $staged_pages ) ? $staged_pages : array();
			$has_staged_site = ( bool ) count( $staged_pages );
		}

		return $has_staged_site;
	}

	/**
	 * Whether or not we are currently deploying.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public static function is_deploy() {
		return self::is_inspirations() && isset( $_POST['task'] ) && 'deploy' == $_POST['task'];
	}

	/**
	 * Whether or not we are on the Inspirations page.
	 *
	 * Technically, this method returns true if we're on the Inspirations page (IE the wizard) or
	 * if we're deploying a site (which is also the inspirations page).
	 *
	 * For further specification, please see:
	 * self::is_inspirations_page()
	 * self::is_deploy()
	 *
	 * @since 1.7.0
	 *
	 * @global string $pagenow The current admin page.
	 *
	 * @return bool
	 */
	public static function is_inspirations() {
		global $pagenow;

		return 'admin.php' === $pagenow && ! empty( $_GET['page'] ) && 'boldgrid-inspirations' === $_GET['page'];
	}

	/**
	 * Whether or not we are on the Inspirations page with the wizard.
	 *
	 * We're either on the wizard page or on the deployment page. Please see self::is_inspiration.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public static function is_inspirations_page() {
		return self::is_inspirations() && ! self::is_deploy();
	}

	/**
	 * Our active site was installed by BoldGrid.
	 *
	 * @return bool
	 */
	public function has_active_bg_site( $install_options ) {
		$installed_pages = get_option( 'boldgrid_installed_page_ids', array() );

		if ( empty( $installed_pages ) ) {
			return false;
		}

		// Generate a CSV of pages installed by BoldGrid.
		$installed_pages = implode( ',', $installed_pages );

		$pages = get_pages( array(
			'include' => $installed_pages,
			'post_status' => 'publish',
		));

		// If we have at least one 'BoldGrid installed page' published, return true.
		return ( ! empty( $pages ) );
	}

	/**
	 * Check to see if the user has an active site.
	 *
	 * @return boolean
	 */
	public static function has_active_site() {
		// Get all pages.
		$pages = get_pages();

		// If there are no pages, then return false.
		if ( empty( $pages ) ) {
			return false;
		}

		// Get default, attribution, and coming soon pages.
		$default_page = get_page_by_title( __( 'Sample Page' ) );
		$attribution_page = get_page_by_title( 'Attribution' );
		$coming_soon_page = get_page_by_title( __( 'WEBSITE COMING SOON' ) );

		// Initialize $ids_to_remove.
		$ids_to_filter = array();

		// Get the boldgrid_attribution option data.
		$attribution = get_option( 'boldgrid_attribution' );

		// If there is attribution data, then add the page id to $ids_to_filter.
		if ( ! empty( $attribution ) && isset( $attribution['page']['id'] ) ) {
			$ids_to_filter[] = $attribution['page']['id'];
		}

		// Add the page ids of the default, attribution, and coming soon pages from title match,
		// to the array.
		foreach ( array(
			$default_page,
			$attribution_page,
			$coming_soon_page,
		) as $page ) {
			if ( null !== $page ) {
				$ids_to_filter[] = $page->ID;
			}
		}

		// Build an array of page objects that do not match page ids in $ids_to_filter.
		$active_pages = array();

		foreach ( $pages as $page ) {
			if ( ! in_array( $page->ID, $ids_to_filter ) ) {
				$active_pages[] = $page;
			}
		}

		// Return whether or not we have any pages in the array.
		return ! empty( $active_pages );
	}

	/**
	 *
	 */
	public static function has_blank_active_site() {
		/*
		 * Get a list of all active pages.
		 *
		 * If we have an active Attribution page, exclude that from the list.
		 */
		$attribution_page = get_page_by_title( 'Attribution' );

		if ( is_object( $attribution_page ) ) {
			$exclude = $attribution_page->ID;
		} else {
			$exclude = '';
		}

		$pages = get_pages( array(
			'exclude' => $exclude,
			'post_status' => 'publish',
		));

		// If there are no active pages, return true.
		if ( empty( $pages ) ) {
			return true;
		}

		// Get default pages we're expecting.
		$default_pages = array(
			'sample' => get_page_by_title( __( 'Sample Page' ) ),
			'coming_soon' => get_page_by_title( __( 'WEBSITE COMING SOON' ) ),
		);

		// How many of our default pages were found.
		$default_pages_found = 0;
		foreach ( $default_pages as $page ) {
			if ( is_object( $page ) ) {
				$default_pages_found++;
			}
		}

		/*
		 * If the count of our pages found is the same as our count of default pages found, then we
		 * have a blank site.
		 */
		if ( $default_pages_found == count( $pages ) ) {
			return true;
		}
		/*
		 * If the number of pages we have is <= the number of default pages found, then we have
		 * a blank site.
		 *
		 * EQUALS
		 * If we have 2 pages and we have 2 default_pages_found, then every page we have is a default
		 * page.
		 *
		 * LESS THAN
		 * If we have 1 page and we have 2 default pages, then one of our default pages is the current
		 * page.
		 */
		if ( count( $pages ) <= $default_pages_found ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the staging theme name from the staging plugin.
	 *
	 * @return WP_Theme | null
	 */
	public static function get_staging_theme() {
		return class_exists( 'Boldgrid_Staging_Theme' ) ? Boldgrid_Staging_Theme::get_staging_theme() : null;
	}

	/**
	 * Get the menu slug needed to make sure that the first item has the same slug as the primary.
	 *
	 * @param string $top_level
	 *
	 * @return string
	 */
	public static function get_menu_slug( &$top_level ) {
		if ( $top_level ) {
			$slug = $top_level;
			$top_level = '';
		} else {
			$slug = '';
		}

		return $slug;
	}

	/**
	 * Add the styles and the scripts.
	 */
	public function enqueue_scripts() {
		// If we are deploying, enqueue the following and then return;
		if ( self::is_deploy() ) {
			$this->enqueue_inspirations_css();
			$this->enqueue_inspirations_js( false );
			return;
		}

		if ( isset( $_REQUEST['task'] ) ) {
			return;
		}

		// Add active color palette css.
		add_action('admin_head',
			array(
				$this,
				'admin_colors',
			)
		);

		add_thickbox();

		$this->enqueue_inspirations_css();

		wp_enqueue_style( 'boldgrid-inspirations-font-awesome' );

		wp_enqueue_style( 'dashicons' );

		$this->enqueue_inspirations_js();

		/*
		 * Add Fancybox.
		 *
		 * This includes both js and css.
		 */
		wp_register_style(
			'boldgrid-inspirations-fancybox',
			plugins_url(
				'/assets/css/fancybox.css',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
			),
			array(),
			BOLDGRID_INSPIRATIONS_VERSION
		);

		wp_enqueue_style( 'boldgrid-inspirations-fancybox' );

		wp_enqueue_script( 'boldgrid-inspirations-fancybox',
			plugins_url(
				'assets/js/fancybox.js',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
			),
			array(
				'jquery',
			),
			BOLDGRID_INSPIRATIONS_VERSION,
			true
		);

		$this->enqueue_jquery_toggles();

		// Js.
		wp_enqueue_script( 'boldgrid-lazyload',
			plugins_url(
				'assets/js/lazyload.js',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
			),
			array( 'jquery' ),
			BOLDGRID_INSPIRATIONS_VERSION,
			true
		);
	}

	/**
	 * Enqueue css for the Inspirations process.
	 *
	 * @since 1.7.0
	 */
	public function enqueue_inspirations_css() {
		wp_register_style(
			'boldgrid-inspirations-css',
			plugins_url( '/assets/css/boldgrid-inspirations.css', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
			array(),
			BOLDGRID_INSPIRATIONS_VERSION
		);

		wp_enqueue_style( 'boldgrid-inspirations-css' );
	}

	/**
	 * Enqueue js for the Inspirations process.
	 *
	 * @since 1.7.0
	 */
	public function enqueue_inspirations_js( $in_footer = true ) {
		$handle = 'boldgrid-inspirations';

		wp_register_script( $handle,
			plugins_url( 'assets/js/boldgrid-inspirations.js', BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ),
			array(
				'wp-util',
			),
			BOLDGRID_INSPIRATIONS_VERSION,
			$in_footer
		);

		wp_localize_script(
			$handle,
			'Inspiration',
			array(
				'active'                  => 'Active',
				'staging'                 => 'Staging',
				'coins'                   => __( 'Coins', 'boldgrid-inspirations' ),
				'isDeploy'                => self::is_deploy(),
				'fetchingThemes'          => __( 'Fetching themes...', 'boldgrid-inspirations' ),
				'fetchingCategories'      => __( 'Fetching categories...', 'boldgrid-inspirations' ),
				'errorFetchingThemes'     => __( 'There was an error fetching themes.', 'boldgrid-inspirations' ),
				'errorFetchingCategories' => __( 'There was an error fetching categories.', 'boldgrid-inspirations' ),
				'errorFetchingPagesets'   => __( 'There was an error fetching pagesets.', 'boldgrid-inspirations' ),
				'errorBuildingPreview'    => __( 'There was an error building your custom website preview.', 'boldgrid-inspirations' ),
				'inspirationsVersion'     => BOLDGRID_INSPIRATIONS_VERSION,
				'myInspirationUrl'        => Boldgrid_Inspirations_My_Inspiration::get_url( true ),
				'previewTimeout'          => __( 'Connection timed out when attempting to load custom website preview.', 'boldgrid-inspirations' ),
				'select'                  => __( 'Select', 'boldgrid-inspirations' ),
				'tryFewMinutes'           => __( 'Please try again in a few minutes.', 'boldgrid-inspirations' ),
				'tryFewSeconds'           => __( 'Please try again in a few seconds.', 'boldgrid-inspirations' ),
				'tryAgain'                => __( 'Try again', 'boldgrid-inspirations' ),
			)
		);

		wp_enqueue_script( $handle );
	}

	/**
	 * Enqueue jQuery Toggles.
	 *
	 * This is used to add the toggles under Features within step 2 of Inspirations.
	 *
	 * @since 1.3.7
	 */
	public function enqueue_jquery_toggles() {
		wp_register_style(
			'boldgrid-inspirations-toggles',
			plugins_url(
				'/assets/css/jquery-toggles/toggles-full.css',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
			),
			array(),
			BOLDGRID_INSPIRATIONS_VERSION
		);

		wp_enqueue_style( 'boldgrid-inspirations-toggles' );

		wp_enqueue_script( 'boldgrid-inspirations-toggles',
			plugins_url(
				'assets/js/jquery-toggles/toggles.js',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
			),
			array( 'jquery', ),
			BOLDGRID_INSPIRATIONS_VERSION,
			true
		);
	}

	/**
	 * Add the top level menui item "Inspirations".
	 *
	 * @param unknown $top_level
	 */
	public function add_top_menu_item( $top_level ) {
		add_menu_page( 'Inspirations', 'Inspirations', 'manage_options', $top_level,
			array(
				$this,
				'inspiration_page',
			), 'dashicons-lightbulb', '21.36' );
	}

	/**
	 * Add Layouts Menu Item after pages.
	 */
	public static function add_sub_menu_items( $top_level ) {
		$slug = self::get_menu_slug( $top_level );

		add_submenu_page( $top_level,
			'Install New Site',
			'Install New Site',
			'manage_options',
			$slug ? $slug : 'admin.php?page=boldgrid-inspirations'
		);
	}

	/**
	 * Callback that will render the Boldgrid Inspiration phase.
	 *
	 * @see           Boldgrid_Inspirations_Api::boldgrid_api_call().
	 * @global string $user_email.
	 *
	 * @return null
	 */
	public function inspiration_page() {
		global $user_email;

		$prompting_for_key = class_exists( '\Boldgrid\Library\Library\Notice\KeyPrompt', false );

		$boldgrid_configs = Boldgrid_Inspirations_Config::get_format_configs();

		$api_call_results = Boldgrid_Inspirations_Api::boldgrid_api_call(
			$boldgrid_configs['ajax_calls']['get_version']
		);

		if ( is_null( $api_call_results ) ) {
			error_log( __METHOD__ . ': Error getting BoldGrid version.' );

			wp_die();
		}

		if ( isset( $_POST['task'] ) && 'deploy' == $_POST['task'] ) {
			// Check nonce.
			check_admin_referer( 'deploy', 'deploy' );

			$this->inspiration->deploy_script();
		} else {
			$theme_channel = Boldgrid_Inspirations_Theme_Install::fetch_theme_channel();

			$mode_data = $this->generate_scenarios();

			// Required for toggling of "Coin Budget" help.
			wp_enqueue_script( 'image-edit' );

			// Underscores Templates.
			include BOLDGRID_BASE_DIR . '/pages/templates/boldgrid-inspirations.php';

			// Page template.
			include BOLDGRID_BASE_DIR . '/pages/boldgrid-inspirations.php';
		}

		return;
	}

	/**
	 * Determine the user's scenario.
	 *
	 * # The user has a blank website.							has_blank_active_site()
	 * # The user has an active site built with BoldGrid.		has_active_bg_site()
	 * # The user has an active site not build with BoldGrid.	Deduced by has_blank_active_site()
	 * 															and has_active_bg_site().
	 * # The user has a staging site.
	 *
	 * @return array
	 */
	public function generate_scenarios() {
		$this->install_options = self::find_all_install_options();

		$scenarios = array(
			'has_active_bg_site'    => $this->has_active_bg_site( $this->install_options ),
			'has_staged_site'       => $this->has_staged_site(),
			'has_blank_active_site' => self::has_blank_active_site(),
			'open-section'          => ( ! empty( $_GET['force-section'] ) ) ? sanitize_text_field( $_GET['force-section'] ) : '',
			'staging_active'        => $this->check_staging_plugin(),
			'staging_installed'     => file_exists( WP_PLUGIN_DIR . '/boldgrid-staging' ),
			'url'                   => get_admin_url() . 'admin.php?page=boldgrid-inspirations',
		);

		$scenarios['has_any_site'] = ! empty( $scenarios['has_active_bg_site'] ) || empty( $scenarios['has_blank_active_site'] );

		return $scenarios;
	}
}
