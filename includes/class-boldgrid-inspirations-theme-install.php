<?php
/**
 * Theme installation and presentation related processes
 *
 * Methods that involve theme installation, activation and presentation in the Admin Interface should
 * be added here.
 *
 * @package Boldgrid_Inspirations
 * @since 1.0.0
 */

/**
 * Theme installation and presentation related processes.
 *
 * This class handles theme installs via Add New and inspirations.
 *
 * @since 1.0.0
 */
class Boldgrid_Inspirations_Theme_Install {

	/**
	 * BoldGrid Configs.
	 *
	 * @since 2.7.6
	 *
	 * @var array
	 */
	public $configs = array();

	public function __construct( $configs ) {
		$this->configs = $configs;
	}

	/**
	 * Checks a string to see if boldgrid- is in the string.
	 *
	 * @since 1.0.11
	 *
	 * @return boolean is boldgrid- is in string
	 */
	public static function is_boldgrid_theme( $theme_slug ) {
		return ( false !== stripos( $theme_slug, 'boldgrid-' ) );
	}

	/**
	 * Check if this is after the user installs a theme through inspiration.
	 *
	 * @since 1.0.11
	 *
	 * @return boolean.
	 */
	public function is_inspiration_post_install() {
		global $pagenow;

		// If the users task is theme success
		$theme_success = ( ! empty( $_GET['task'] ) && 'theme-install-success' == $_GET['task'] );

		// If the user has the inspiration install = 1
		$inspiration_install = ( ! empty( $_GET['inspiration-install'] ) &&
			1 == $_GET['inspiration-install'] );

		return $theme_success && $inspiration_install && 'themes.php' == $pagenow;
	}

	/**
	 * Get a users saved theme release channel.
	 *
	 * @since 1.1.6
	 *
	 * @return string $theme_channel.
	 */
	public static function fetch_theme_channel() {
		$boldgrid_settings = get_option( 'boldgrid_settings' );

		$theme_channel = ! empty( $boldgrid_settings['theme_release_channel'] ) ?
			$boldgrid_settings['theme_release_channel'] : 'stable';

		return $theme_channel;
	}

	/**
	 * Make and API call to the asset server to get Wordpress data for all BG themes.
	 *
	 * WP Data includes num downloaded, ratings, version, screenshot url, download link, ect.
	 *
	 * @since 1.0.11
	 *
	 * @param string $theme_name
	 * @return array List of themes and associated WP data
	 */
	public function get_theme_info( $theme_name = null, $args = array() ) {
		$configs = $this->configs;

		// Make request to asset server for theme data.
		$request_url = $configs['asset_server'] . $configs['ajax_calls']['get_theme_info'];
		$raw_response = wp_remote_post( $request_url, array(
			'body' => array(
				'key' => $configs['api_key'],
				'theme-name' => $theme_name,
				'channel' => self::fetch_theme_channel(),
				'args' => $args
			)
		) );

		$response_body = wp_remote_retrieve_body ( $raw_response );

		$response_decoded = json_decode( $response_body );

		/*
		 * Change $theme->author to $theme->author['display_name'].
		 *
		 * This change occurred in WordPress around Feb 2018.
		 *
		 * @link https://github.com/WordPress/WordPress/commit/1e5629d1f1440b56d77e2f8cb77cbffd4faf6d49#diff-d2aa2a8fe3d41f489aaaa80a3b74b2fbL3221
		 */
		if ( ! empty( $response_decoded->result->data[0]->author ) && is_string( $response_decoded->result->data[0]->author ) ) {
			$response_decoded->result->data[0]->author = array(
				'display_name' => $response_decoded->result->data[0]->author,
			);
		}

		return ! empty( $response_decoded->result->data ) && is_array( $response_decoded->result->data )
			? $response_decoded->result->data : array();
	}

	/**
	 * Interrupt the API call out to WP when requesting a single theme that we interpret as
	 * a BoldGrid theme. Return data from our servers instead.
	 *
	 * @since 1.0.11
	 *
	 * @param $result.
	 * @param $action Optional.
	 * @param $args Optional.
	 * @return array List of themes and associated WP data.
	 */
	public function query_themes_single(  $result = null, $action = null, $args = array()  ) {

		// This only runs on the query_themes action.
		if ( 'query_themes' != $action ) {
			return $result;
		}

		// Grab relevant args.
		$theme  = ! empty( $args->theme ) ? $args->theme : null;
		if ( !$theme || false == self::is_boldgrid_theme( $theme ) ) {
			return $result;
		}

		$themes = $this->get_theme_info( $theme );

		$result = new stdClass();
		$result->themes = $themes;
		$result->info = (object) array(
			'page' => 1,
			'pages' => 1,
			'results' => 1
		);

		return $result;

	}

	/**
	 * Filters the results of themes_api to return BG theme data when on theme_information action.
	 *
	 * @since 1.0.11
	 *
	 * @param $override.
	 * @param $action Optional.
	 * @param $args Optional.
	 * @return array List of theme data needed for theme information results.
	 */
	public function theme_information( $override = false, $action = null, $args = null ) {

		// This only runs on the theme_information action.
		if ( 'theme_information' != $action ) {
			return $override;
		}
		// Validate theme slug.
		$theme = ! empty( $args->slug ) ? $args->slug : null;
		if ( ! $theme || ! self::is_boldgrid_theme( $theme ) ) {
			return $override;
		}

		// Get theme info for given theme.
		$themes = $this->get_theme_info( $theme );

		if ( !empty( $themes[0] ) ) {
			$response_data = $themes[0];
		} else {
			$response_data = $override;
		}

		return $response_data;
	}

	/**
	 * Filters the results of themes_api to return BG theme data.
	 *
	 * @since 1.0.11
	 *
	 * @param $result.
	 * @param $action Optional.
	 * @param $args Optional.
	 * @return array List of theme data needed for query search results.
	 */
	public function query_themes( $result = null, $action = null, $args = array() ) {

		// This only runs on the query_themes action.
		if ( 'query_themes' != $action ) {
			return $result;
		}

		// Grab relevant args.
		$page   = ! empty( $args->page ) ? $args->page : null;
		$browse = ! empty( $args->browse ) ? $args->browse : null;
		$tags   = ! empty( $args->tag ) ? $args->tag : null;
		$search = isset( $args->search ) ? $args->search : null;

		$accepted_browse_filters = array(
			'featured',
			'popular',
			'new',
		);

		if ( ! empty( $args->theme ) || false == in_array( $browse, $accepted_browse_filters ) ) {
			if ( ! $tags && null === $search ) {
				return $result;
			}
		}

		$themes = array();
		if ( ! $page ) {
			// Make an API call to grab the theme information.
			$themes = $this->get_theme_info( null, $args );
		}

		$wp_themes = ! empty( $result->themes ) ? $result->themes : array();
		$themes_response = array_merge( $themes, $wp_themes );

		// Update the results of the API call to include our results at the top.
		$result->themes = $themes_response;
		$results_count = ! empty ( $result->info['results'] ) ? $result->info['results'] : 0;
		$result->info['results'] = $results_count + count( $themes );
		if ( $results_count && 'featured' == $browse ) {
			$result->info['results'] = sizeof ( $themes_response );
		}

		return $result;
	}

	/**
	 * Print a notice that recommends that users install BoldGrid themes.
	 *
	 * @since 1.0.11
	 *
	 * @param $install_tabs.
	 * @return $install_tabs array of tabs to show at top of theme-install.php.
	 */
	public function print_notice( $install_tabs ) {
		?>
	<div id='recommend-boldgrid' class="notice notice-success">
		<p>
			<span class="boldgrid-cert"></span>
			<span class='recommend-boldgrid-text'>
				<?php esc_html_e( 'We recommend choosing a BoldGrid Theme. Our themes have theme specific content available through Inspirations and include more features while customizing.', 'boldgrid-inspirations' ); ?>
			</span>
		</p>
	</div>
	<?php
		return $install_tabs;
	}

	/**
	 * Enqueue styles for theme install page.
	 *
	 * @since 1.1
	 */
	public function enqueue_styles() {
		$query_args = array(
			'family' => 'Josefin+Sans',
			'subset' => 'latin,latin-ext',
		);

		wp_enqueue_style( 'boldgrid-inspiration-theme-install', add_query_arg( $query_args, "//fonts.googleapis.com/css" ) );
	}

	/**
	 * All actions that should occur on theme-install.php.
	 *
	 * @since 1.0.11
	 */
	public function load_theme_install() {
		add_action( 'install_themes_tabs', array( $this, 'print_notice' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_styles' ) );
	}

	/**
	 * All hooks to be added
	 */
	public function add_hooks() {

		// Actions only to occur on theme-install.php.
		add_action( 'load-theme-install.php', array( $this, 'load_theme_install' ) );

		// When installing theme that begins with boldgrid-, make check api.
		add_filter( 'themes_api', array( $this, 'theme_information' ), 10, 3 );

		// Return BoldGrid themes that match a users request params.
		add_filter( 'themes_api', array( $this, 'query_themes_single' ), 10, 3 );
		add_filter( 'themes_api_result', array( $this, 'query_themes' ), 10, 3 );

		if ( $this->is_inspiration_post_install() ) {
			add_action( 'admin_footer', array (
				$this,
				'wp_footer'
			) );

			add_action( 'admin_enqueue_scripts', array (
				$this,
				'enqueue_scripts'
			) );
		}

		// Hook for migrating theme mods
		// add_action( 'after_switch_theme', array (
		// $this,
		// 'transfer_theme_mods'
		// ), 10, 2 );
	}

	/**
	 * Overrides configs made to a theme.
	 *
	 * Removes footer widget.
	 *
	 * @since 1.0.9
	 *
	 * @return Array $boldgrid_theme_configs Theme Configuraitons To Override.
	 */
	public static function universal_framework_configs() {
		add_filter(
			'boldgrid_theme_framework_config',
			function ( $boldgrid_theme_configs ) {
				$boldgrid_install_options = get_option( 'boldgrid_install_options' );

				$is_base_pageset = false;
				if ( isset( $boldgrid_install_options['is_base_pageset'] ) ) {
					// If base pageset bool is passed use it to determine if address widgets.
					$is_base_pageset = (bool) $boldgrid_install_options['is_base_pageset'];
				} elseif ( ! empty( $boldgrid_install_options['page_set_id'] ) ) {
					/*
					 * Backwards compatibility: pre 1.1.2 inspirations install.
					 * Lookup ids if setting is not passed.
					 */
					$default_pagesets = array(
						7,
						8,
						11,
						15,
						16,
						17,
						18,
					);

					$page_set_id = $boldgrid_install_options['page_set_id'];
					$is_base_pageset = in_array( $page_set_id, $default_pagesets, true );
				}

				if ( $is_base_pageset ) {
					$widget_instances = $boldgrid_theme_configs['widget']['widget_instances'];
					if ( ! empty( $widget_instances ) && ! empty( $widget_instances['boldgrid-widget-3'] ) ) {
						$company_details_widget = $widget_instances['boldgrid-widget-3'];
						$index = array_values( $company_details_widget );
						$boldgrid_theme_configs['widget']['widget_instances']['footer-company-details'] = $index[0];
						unset( $boldgrid_theme_configs['widget']['widget_instances']['boldgrid-widget-3'] );
					}
				}

				return $boldgrid_theme_configs;
			}
		);
	}

	/**
	 * Overrides configs made to a theme.
	 *
	 * Override specific configs in a theme, so the previews can be dynamically
	 * generated. We add the CSS via inline in the head here, instead of waiting
	 * for the new CSS to be generated and compiled from SCSS on theme activation.
	 * This is useful for the color palettes being able to be unique in categories.
	 *
	 * @since 1.0.9
	 *
	 * @return Array $boldgrid_theme_configs Theme Configuraitons To Override.
	 */
	public static function apply_theme_framework_configs() {
		add_filter( 'boldgrid_theme_framework_config',
			function ( $boldgrid_theme_configs ) {
				$boldgrid_theme_configs['framework']['inline_styles'] = true;
				return $boldgrid_theme_configs;
			} );
	}

	/**
	 * Print the template
	 */
	public function wp_footer() {
		include BOLDGRID_BASE_DIR . '/pages/templates/template-post-theme-install.php';
	}

	/**
	 * Enqueue The scripts
	 */
	public function enqueue_scripts() {

		wp_register_script( 'boldgrid-inspiration-theme-install',
			plugins_url( 'assets/js/theme-install.js',
				BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' ), array (
				'jquery'
			), BOLDGRID_INSPIRATIONS_VERSION, true );

		wp_localize_script ( 'boldgrid-inspiration-theme-install', 'BoldgridInspirationThemeInstall', array(
			'isInspirationPostInstall' => $this->is_inspiration_post_install()
		));

		wp_enqueue_script ( 'boldgrid-inspiration-theme-install' );
	}

	/**
	 * Transfer theme mods that were stored when the user deactivated the old theme
	 */
	public function transfer_theme_mods( $old_theme, $wp_old_theme = null ) {
		// These theme mods are not copied over
		$do_not_transfer_settings = array (
			'sidebars_widgets',
			'transferred_theme_mods'
		);

		$old_theme_mods = array ();
		if ( is_object( $wp_old_theme ) ) {
			$old_theme_mods = get_option( 'theme_mods_' . $wp_old_theme->get_stylesheet(),
				array () );
		}

		$new_theme = wp_get_theme();
		if ( sizeof( $old_theme_mods ) ) {
			// If is a boldgrid theme
			if ( is_a( $new_theme, 'WP_Theme' ) &&
				 strpos( $new_theme->get( 'TextDomain' ), 'boldgrid' ) !== false ) {
				$theme_mods = get_option( 'theme_mods_' . get_stylesheet(), array () );

				// If theme Mods Exists
				if ( $theme_mods !== false ) {

					foreach ( $old_theme_mods as $old_theme_mod_name => $old_theme_mod ) {
						// If theme mod not already set for theme

						if ( ! isset( $theme_mods[$old_theme_mod_name] ) ) {
							if ( ! in_array( $old_theme_mod_name,
								$do_not_transfer_settings ) ) {
								$theme_mods[$old_theme_mod_name] = $old_theme_mod;
								// Set this theme mod which will tell the customizer to display
								// a message letting the user now that they can revert
								// The user will revert this array of theme mods
								$theme_mods['transferred_theme_mods'][] = $old_theme_mod_name;
							}
						}
					}

					update_option( 'theme_mods_' . get_stylesheet(), $theme_mods );
				}
			}
		}
	}
}
