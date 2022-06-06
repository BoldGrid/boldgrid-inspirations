<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspirations class.
 */
class Boldgrid_Inspirations {
	/**
	 * Array of BoldGrid specific configs.
	 *
	 * @access protected
	 * @var array
	 */
	protected $configs = null;

	/**
	 * Class property for $is_preview_server.
	 *
	 * @var bool
	 */
	public $is_preview_server = false;

	/**
	 * Asset user id.
	 *
	 * @var int
	 */
	public $asset_user_id;

	/**
	 * Set the required PHP version.
	 *
	 * @access private
	 * @var string
	 * @staticvar
	 */
	private static $required_php_version = '5.3';

	/**
	 * Set the required WordPress version.
	 *
	 * @access private
	 * @var string
	 * @staticvar
	 */
	private static $required_wp_version = '4.2';

	/**
	 * The api class object.
	 *
	 * @since 1.2.2
	 * @access public
	 * @var Boldgrid_Inspirations_Api
	 */
	public $api;

	/**
	 * Was this class every instantiated?
	 *
	 * @since 1.2.3
	 * @access private
	 * @var bool
	 * @staticvar
	 */
	private static $was_loaded = false;

	/**
	 * Constructor.
	 *
	 * @see Boldgrid_Inspirations::load_dependencies().
	 * @see Boldgrid_Inspirations_Api::__construct().
	 * @see Boldgrid_Inspirations::configure().
	 * @see Boldgrid_Inspirations_Branding::__construct().
	 * @see Boldgrid_Inspirations_Branding::add_hooks().
	 * @see Boldgrid_Inspirations::check_auto_update().
	 * @see Boldgrid_Inspirations::queue_hooks().
	 */
	public function __construct() {
		if ( ! self::$was_loaded ) {
			// Load dependencies.
			$this->load_dependencies();

			// Branding.
			$branding = new Boldgrid_Inspirations_Branding();
			$branding->add_hooks();

			// If DOING_CRON, then check for auto-updates.
			$this->check_auto_update();

			// Add actions and filters.
			$this->queue_hooks();
		}

		// Instantiate Boldgrid_Inspirations_Api and set the object as a class property.
		$this->api = new Boldgrid_Inspirations_Api( $this );

		// Configure.
		$this->configure();

		// Marks as loaded.
		self::$was_loaded = true;
	}

	/**
	 * Load dependencies.
	 *
	 * @since 1.2.3
	 * @access private
	 */
	private function load_dependencies() {
		// Include the Boldgrid_Inspiration_Config class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-config.php';

		// Include the Boldgrid_Inspiration_Utility class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-utility.php';

		// Include the Boldgrid_Inspiration_Api class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-api.php';

		// Include the Boldgrid_Inspirations_Branding class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-branding.php';

		// The Boldgrid_Inspirations_Theme_Install class is instantiated in later hook.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-theme-install.php';

		// If not on a network admin page.
		if ( ! is_network_admin() ) {
			// Include the Boldgrid_Inspirations_Theme_Install class.
			require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-theme-install.php';
		}

	}

	/**
	 * Configure.
	 *
	 * @since 1.2.3
	 * @access private
	 *
	 * @see Boldgrid_Inspirations_Config::get_format_configs().
	 * @see Boldgrid_Inspirations_Theme_Install::universal_framework_configs().
	 * @see Boldgrid_Inspirations::check_reseller_data().
	 * @see Boldgrid_Inspirations_Config::check_api_availability().
	 * @see Boldgrid_Inspirations_Theme_Install::apply_theme_framework_configs().
	 * @see Boldgrid_Inspirations::set_is_preview_server().
	 * @see Boldgrid_Inspirations::set_asset_user_id().
	 */
	private function configure() {
		// Get configs and set in a class property.
		$this->set_configs( Boldgrid_Inspirations_Config::get_format_configs() );

		// Set some class properties.
		$this->set_is_preview_server();
		$this->set_asset_user_id();

		if ( ! self::$was_loaded ) {
			// Apply BoldGrid theme config modifications.
			Boldgrid_Inspirations_Theme_Install::universal_framework_configs();

			// Check reseller data.
			$this->check_reseller_data();

			// Check API availability.
			$this->check_api_availability();

			// If not on a network admin page on a preview server, then apply theme framework configs.
			if ( $this->is_preview_server && ! is_network_admin() ) {
				Boldgrid_Inspirations_Theme_Install::apply_theme_framework_configs();
			}
		}
	}

	/**
	 * Check API availability.
	 *
	 * @since 1.2.3
	 * @access private
	 *
	 * @see Boldgrid_Inspirations_Api::verify_api_key().
	 * @see Boldgrid_Inspirations_Api::set_is_asset_server_available().
	 * @link https://developer.wordpress.org/reference/functions/wp_get_current_user/
	 * @link https://developer.wordpress.org/reference/functions/get_user_meta/
	 */
	private function check_api_availability() {
		// Initialize $is_asset_server_available; set class property from transient.
		$is_asset_server_available = (bool) get_site_transient( 'boldgrid_available' );

		// If we had communication issues, then check now; it may be better.
		if ( ! $is_asset_server_available ) {
			// Verify API key, which connects to the asset server and sets the status.
			$this->api->verify_api_key();
		} else {
			Boldgrid_Inspirations_Api::set_is_asset_server_available( true );

			// Ensure all activation data was sent.
			if ( function_exists( 'wp_get_current_user' ) &&
				false !== ( $current_user = wp_get_current_user() ) ) {
					$first_login_ts = strtotime(
						get_user_meta( $current_user->ID, 'first_login', true ) );

					// If the first login was made in the last 30 seconds, then verify activation.
					if ( $first_login_ts + 30 > time() ) {
						$_GET['force-check'] = 1;
						$this->api->verify_api_key();
					}
				}
		}
	}

	/**
	 * Instantiate a new instance of Boldgrid_Inspirations_Update().
	 *
	 * Previously, this method also handled auto updates based on the settings
	 * in the boldgrid_options settings. This functionality however has been
	 * moved to the library.
	 *
	 * @since 1.2.3
	 * @access private
	 *
	 * @see Boldgrid_Inspirations_Update::__construct().
	 * @see Boldgrid_Inspirations_Update::wp_update_this_plugin().
	 *
	 * @return null
	 */
	private function check_auto_update() {
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}

		// Get BoldGrid settings.
		$boldgrid_settings = get_option( 'boldgrid_settings' );

		// Ensure required definitions for pluggable.
		if ( ! defined( 'AUTH_COOKIE' ) ) {
			define( 'AUTH_COOKIE', null );
		}

		if ( ! defined( 'SECURE_AUTH_COOKIE' ) ) {
			define( 'SECURE_AUTH_COOKIE', null );
		}

		if ( ! defined( 'LOGGED_IN_COOKIE' ) ) {
			define( 'LOGGED_IN_COOKIE', null );
		}

		// Load the pluggable class, if needed.
		require_once ABSPATH . 'wp-includes/pluggable.php';

		// Include the update class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-update.php';

		// Instantiate the update class.
		$plugin_update = new Boldgrid_Inspirations_Update( null );

		// Check and update plugins.
		$plugin_update->wp_update_this_plugin();

		return;
	}

	/**
	 * Add actions and filters.
	 *
	 * @since 1.2.3
	 * @access private
	 */
	private function queue_hooks() {
		// When this plugin is activated, trigger additional operations.
		register_activation_hook( BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php',
			array(
				$this,
				'boldgrid_activate',
			)
		);

		// Update user metadata for last login.
		add_action( 'wp_login',
			array(
				$this,
				'update_last_login',
			)
		);

		// After plugins have been loaded, load the textdomain.
		add_action( 'plugins_loaded',
			array(
				$this,
				'boldgrid_load_textdomain',
			)
		);

		// Hide form notices on the Inpirations pages.
		add_action( 'load-admin_page_my-inspiration',
			[
				$this,
				'hide_form_notices',
			]
		);

		add_action( 'load-toplevel_page_boldgrid-inspirations',
			[
				$this,
				'hide_form_notices',
			]
		);

		// Add a filter for html.
		add_filter( 'wp_kses_allowed_html',
			array(
				$this,
				'filter_allowed_html',
			), 10, 2
		);
	}

	/**
	 * Load plugin textdomain (translation files).
	 *
	 * @since 1.2.3
	 */
	public function boldgrid_load_textdomain() {
		load_plugin_textdomain( 'boldgrid-inspirations', false, BOLDGRID_BASE_DIR . '/languages/' );
	}

	/**
	 * Update last login in user metadata
	 *
	 * @param string $login
	 *        	WordPress login username passed by wp_login action.
	 */
	public function update_last_login( $login ) {
		$current_user = get_user_by( 'login', $login );
		$user_metadata = get_user_meta( $current_user->ID, 'last_login', true );
		if ( empty( $user_metadata ) ) {
			update_user_meta( $current_user->ID, 'first_login', current_time( 'mysql', true ) );
		}
		update_user_meta( $current_user->ID, 'last_login', current_time( 'mysql', true ) );

		// Update mobile login ratio.
		// Format of ratio: mobile:total logins.
		$mobile_ratio = get_site_option( 'boldgrid_mobile_ratio' );

		if ( ! empty( $mobile_ratio ) ) {
			$mobile_ratio_array = explode( ':', $mobile_ratio );
			$mobile_ratio_array[1] ++;
			if ( wp_is_mobile() ) {
				$mobile_ratio_array[0] ++;
			}
			$mobile_ratio = implode( ':', $mobile_ratio_array );
		} else {
			$mobile_ratio = ( wp_is_mobile() ? 1 : 0 ) . ':1';
		}

		$mobile_ratio = update_site_option( 'boldgrid_mobile_ratio', $mobile_ratio );
	}

	/**
	 * On activation of BoldGrid, check Welcome Panel exists and make it show if not.
	 *
	 * @since 1.2.3
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
	 */
	public function boldgrid_activate() {
		// If not on a network admin page, then reset the welcome panel and create an attribution page.
		if ( ! is_network_admin() ) {
			// Get the current user id.
			$user_id = get_current_user_id();

			// check to see if Welcome Panel is hidden, if it is show it.
			if ( 1 !== get_user_meta( $user_id, 'show_welcome_panel', true ) ) {
				update_user_meta( $user_id, 'show_welcome_panel', 1 );
			}
		}

		// Get the current plugin version.
		$plugin_data = get_plugin_data( BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php', false );

		// Record the activated and current plugin version options.
		update_site_option( 'boldgrid_inspirations_activated_version', $plugin_data['Version'] );
		update_site_option( 'boldgrid_inspirations_current_version', $plugin_data['Version'] );

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-attribution.php';
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-attribution-page.php';

		Boldgrid_Inspirations_Attribution_Page::on_activate();
	}

	/**
	 * Check and retrieve reseller data.
	 *
	 * @since 1.2.3
	 * @access private
	 *
	 * @see Boldgrid_Inspirations_Update::update_api_data().
	 */
	private function check_reseller_data() {
		// Ensure there is reseller info, if available.
		if ( ! get_option( 'boldgrid_reseller' ) ) {

			// Include the update class.
			require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-update.php';

			// Call the update_api_data method, to get the latest data and set the reseller option.
			Boldgrid_Inspirations_Update::update_api_data();
		}
	}

	/**
	 * Get configuration settings.
	 *
	 * @return array Configuration array.
	 */
	public function get_configs() {
		return $this->configs;
	}

	/**
	 * Set configuration settings.
	 *
	 * @param array $configs Configuration array.
	 *
	 * @return bool
	 */
	public function set_configs( $configs ) {
		$this->configs = $configs;

		return true;
	}

	/**
	 * Get the asset server grid file styles url
	 *
	 * @return string
	 */
	public function get_grid() {
		$configs = $this->get_configs();
		return $configs['asset_server'] . '/static/grid.css';
	}

	/**
	 * Enqueue grid css style.
	 */
	public function enqueue_site_grid() {
		wp_register_style( 'grid-system-imhwpb', $this->get_grid(), array (),
			BOLDGRID_INSPIRATIONS_VERSION );
		wp_enqueue_style( 'grid-system-imhwpb' );
	}

	/**
	 * Set asset user id.
	 */
	public function set_asset_user_id() {
		$this->asset_user_id = ( isset( $_POST['asset_user_id'] ) ? intval(
			$_POST['asset_user_id'] ) : null );
	}

	/**
	 * Set is preview server.
	 */
	public function set_is_preview_server() {
		$boldgrid_configs = $this->get_configs();
		$host = ! empty( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';

		$this->is_preview_server = ( $boldgrid_configs['preview_server'] == "https://" . $host ||
			 $boldgrid_configs['author_preview_server'] == "https://" . $host );
	}

	/**
	 * Add to list of allowed attributes
	 *
	 * @param array $allowed
	 * @param array $context
	 * @return array
	 */
	public function filter_allowed_html( $allowed, $context ) {
		if ( is_array( $context ) ) {
			return $allowed;
		}

		if ( 'post' === $context || 'page' === $context ) {
			// Example case
			$allowed['img']['data-imhwpb-asset-id'] = true;
			$allowed['img']['data-imhwpb-built-photo-search'] = true;
			$allowed['img']['data-image-provider-id'] = true;
			$allowed['img']['data-id-from-provider'] = true;
		}

		return $allowed;
	}

	/**
	 * Allow Filter data attibutes
	 *
	 * @param string $context
	 * @return mixed
	 */
	public function wp_kses_allowed_html( $context = '' ) {
		global $allowedposttags, $allowedtags, $allowedentitynames;

		if ( is_array( $context ) )
			return apply_filters( 'wp_kses_allowed_html', $context, 'explicit' );

		switch ( $context ) {
			case 'post' :
				return apply_filters( 'wp_kses_allowed_html', $allowedposttags, $context );
				break;

			case 'user_description' :

			case 'pre_user_description' :
				$tags = $allowedtags;
				$tags['a']['rel'] = true;
				return apply_filters( 'wp_kses_allowed_html', $tags, $context );
				break;

			case 'strip' :
				return apply_filters( 'wp_kses_allowed_html', array (), $context );
				break;

			case 'entities' :
				return apply_filters( 'wp_kses_allowed_html', $allowedentitynames, $context );
				break;

			case 'data' :
			default :

				return apply_filters( 'wp_kses_allowed_html', $allowedtags, $context );
		}
	}

	/**
	 * Check PHP version.
	 *
	 * @since 1.2.2
	 * @static
	 *
	 * @return bool Whether or not the current PHP version is supported.
	 */
	public static function is_php_compatible() {
		if ( version_compare( phpversion(), self::$required_php_version, '<' ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check PHP and WordPress versions for compatibility
	 *
	 * @static
	 *
	 * @see self::is_php_compatible().
	 * @see $this->deactivate().
	 * @global string $wp_version The WordPress version string.
	 */
	public static function check_php_wp_version() {
		// Check that PHP is installed at our required version or deactivate and die.
		if ( ! self::is_php_compatible() ) {
			self::deactivate(
				'<p><center>' . sprintf(
					// translators: 1 opening strong tag, 2 closing strong tag, 3 required php version for BoldGrid Inspirations plugin.
					esc_html__(
						'%1$sBoldGrid Inspirations%2$s requires PHP %3$s or greater.',
						'boldgrid-inspirations'
					),
					'<strong>',
					'</strong>',
					self::$required_php_version
				) . '</center></p>',
				esc_html__( 'Plugin Activation Error', 'boldgrid-inspirations' ),
				array (
					'response' => 200,
					'back_link' => TRUE
				)
			);
		}

		// Check to see if WordPress version is installed at our required minimum or deactivate and
		// die.
		global $wp_version;

		if ( version_compare( $wp_version, self::$required_wp_version, '<' ) ) {
			self::deactivate(
				'<p><center>' . sprintf(
				// translators: 1 opening strong tag, 2 closing strong tag, 3 required WordPress version for BoldGrid Inspirations plugin.
					esc_html__(
						'%1$sBoldGrid Inspirations%2s requires WordPress %3$s or higher.',
						'boldgrid-inspirations'
					),
					'<strong>',
					'</strong>',
					self::$required_wp_version
				) . '</center></p>',
				esc_html__( 'Plugin Activation Error', 'boldgrid-inspirations' ),
				array (
					'response' => 200,
					'back_link' => TRUE
				)
			);
		}
	}

	/**
	 * Deactivate and die.
	 *
	 * Used if PHP or WordPress version check fails.
	 *
	 * @since 1.2.2
	 * @access private
	 * @static
	 *
	 * @param string $message A message for wp_die to display.
	 * @param string $title A title for wp_die to display.
	 * @param array  $args A control array for wp_die.
	 */
	private static function deactivate( $message = '', $title = '', $args = array() ) {
		// Deactivate the plugin.
		deactivate_plugins( BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php' );

		// If there is no message, then supply one.
		if ( empty( $message ) ) {
			$message = 'BoldGrid Inspirations ' . esc_html__(
				'has been deactivated.', 'boldgrid-inspirations'
			);
		}

		// If there is no title, then supply one.
		if ( empty( $title ) ) {
			$title = 'BoldGrid Inspirations ' . esc_html__(
				'Deactivated', 'boldgrid-inspirations'
			);
		}

		// If the array of arguments is empty, then create it.
		if ( empty( $args ) ) {
			$args = array (
				'response' => 200,
				'back_link' => TRUE
			);
		}

		wp_die( $message, $title, $args );
	}

	/**
	 * Is feedback opt-out.
	 *
	 * Check the BoldGrid Settings (a WP Option) to see if this site has opted-out for feedback.
	 *
	 * @since 1.0.9
	 * @static
	 *
	 * @return bool
	 */
	public static function is_feedback_optout() {
		// Get BoldGrid settings.
		$options = get_option( 'boldgrid_settings' );

		// Get feedback option.
		$boldgrid_feedback_optout = (
			isset( $options['boldgrid_feedback_optout'] ) ?
			$options['boldgrid_feedback_optout'] : false
		);

		// Return the result:
		return (bool) $boldgrid_feedback_optout;
	}

	/**
	 * Check if is a network admin update page.
	 *
	 * @since 1.2.3
	 *
	 * @global $pagenow The WordPress global for the current page filename.
	 *
	 * @return bool
	 */
	public function is_network_update_page() {
		// If not a network admin page, then return FALSE.
		if ( ! is_network_admin() ) {
			return false;
		}

		// Import global $pagenow.
		global $pagenow;

		// Make an array of update pages.
		$update_pages = array(
			'update-core.php',
			'plugins.php',
			'plugin-install.php',
			'themes.php'
		);

		// Is page admin-ajax.php and action update-plugin?
		$is_adminajax_update = ( 'admin-ajax.php' === $pagenow &&
			'update-plugin' === $_REQUEST['action']
		);

		// Is page update.php and action upgrade-theme?
		$is_upgrade_theme = ( 'update.php' === $pagenow &&
			'upgrade-theme' === $_REQUEST['action']
		);

		// If on pages dealing with updates, then return TRUE.
		if ( in_array( $pagenow, $update_pages, true ) || $is_adminajax_update ||
		$is_upgrade_theme ) {
			return true;
		}

		// The page is not a network admin update page.
		return false;
	}

	/**
	 * Hide form notices on certain pages.
	 *
	 * Run on a hook such as "load-admin_page_{$page}".
	 *
	 * @since 2.3.0
	 *
	 * @see \Boldgrid\Library\Form\Forms::hide_notices()
	 */
	public function hide_form_notices() {
		$bgforms = new \Boldgrid\Library\Form\Forms();
		$bgforms->hide_notices();
	}
}
