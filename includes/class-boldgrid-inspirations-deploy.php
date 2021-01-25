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
 * BoldGrid Inspirations Deploy class.
 */
class Boldgrid_Inspirations_Deploy {
	/**
	 * BoldGrid configs array.
	 *
	 * @access protected
	 *
	 * @var array $configs
	 */
	protected $configs;

	/**
	 * A list of all the installed pages.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	protected $full_page_list;

	/**
	 * An instance of the Boldgrid_Inspirations_Deploy_Bps class.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy_Bps
	 */
	private $bps;

	/**
	 * Build profile id.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var int
	 */
	private $boldgrid_build_profile_id;

	/**
	 * Whether or not to create a preview site.
	 *
	 * Used only on the preview server.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var bool
	 */
	private $create_preview_site;

	/**
	 * Custom pages.
	 *
	 * Optional. Passing this array of page id's will install only these pages.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var bool
	 */
	private $custom_pages;

	/**
	 * An instance of Boldgrid_Inspirations_Installed.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Installed
	 */
	private $installed;

	/**
	 * New path.
	 *
	 * Required only on preview server.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var bool
	 */
	private $new_path;

	/**
	 * Page set version type.
	 *
	 * Such as 'active'.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var string
	 */
	private $page_set_version_type;

	/**
	 * Primary design elements.
	 *
	 * Used to get primary display elements.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var mixed
	 */
	private $pde;

	/**
	 * The time the deployment began.
	 *
	 * Start time is used to measure deployment time, which is returned to the asset server.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var int
	 */
	private $start_time;

	/**
	 * An instance of Boldgrid_Inspirations_Deploy_Status
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy_Status
	 */
	private $status;

	/**
	 * Theme version type.
	 *
	 * Such as 'active'.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var string
	 */
	private $theme_version_type;

	/**
	 * Ticket number.
	 *
	 * Only required on author server.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var int
	 */
	private $ticket_number;

	/**
	 * An instance of the Boldgrid_Inspirations_Deploy_Api class.
	 *
	 * @since 1.7.0
	 * @var Boldgrid_Inspirations_Deploy_Api
	 */
	public $api;

	/**
	 * The Boldgrid Inspirations Asset Manager class object.
	 *
	 * @var Boldgrid_Inspirations_Asset_Manager
	 */
	public $asset_manager;

	/**
	 * Asset user_id.
	 *
	 * @since 1.7.0
	 * @var int
	 */
	public $asset_user_id;

	/**
	 * The BoldGrid Forms class object.
	 *
	 * @since 1.4.8
	 *
	 * @var \Boldgrid\Library\Form\Forms
	 */
	public $bgforms;

	/**
	 * An instance of Deploy Cache.
	 *
	 * A class for installing a caching plugin.
	 *
	 * @since 2.5.0
	 * @var Boldgrid\Inspirations\Deploy\Cache
	 */
	public $cache;

	/**
	 * Coin budget.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var int
	 */
	public $coin_budget;

	/**
	 * Current build cost.
	 *
	 * @since 1.7.0
	 * @var int
	 */
	public $current_build_cost = 0;

	/**
	 * Class used to help deploy themes.
	 *
	 * @since 2.5.0
	 * @var Boldgrid_Inspirations_Deploy_Theme
	 */
	public $deploy_theme;

	/**
	 * Language id.
	 *
	 * Used when getting built_photos_search photos.
	 *
	 * @since 1.7.0
	 * @var int
	 */
	public $language_id;

	/**
	 * Install a sample blog.
	 *
	 * @since  1.3.6
	 * @access public
	 * @var    bool True to install a sample blog.
	 */
	public $install_blog = false;

	/**
	 * Install an invoicing plugin.
	 *
	 * @since  2.5.0
	 * @access public
	 * @var    bool True to install an invoicing plugin.
	 */
	public $install_invoice = false;

	/**
	 * Install a caching plugin.
	 *
	 * @since  2.5.0
	 * @access public
	 * @var    bool True to install a caching plugin.
	 */
	public $install_cache = false;

	/**
	 * An instance of Deploy Invoice.
	 *
	 * A class for helping to install an invoicing plugin.
	 *
	 * @since 2.5.0
	 * @var Boldgrid\Inspirations\Deploy\Invoice
	 */
	public $invoice;

	/**
	 * Is author.
	 *
	 * @since 1.7.0
	 * @var bool
	 */
	public $is_author;

	/**
	 * Is this a generic build?
	 *
	 * @var bool
	 */
	public $is_generic = false;

	/**
	 * Is this a preview server?
	 *
	 * @var bool
	 */
	public $is_preview_server = false;

	/**
	 * An instance of Boldgrid_Inspirations_Deploy_Messages
	 *
	 * @since 1.7.0
	 * @var Boldgrid_Inspirations_Deploy_Messages
	 */
	public $messages;

	/**
	 * Page set id to install.
	 *
	 * Required. This tells us which individual pages to download.
	 *
	 * @var int
	 */
	public $page_set_id;

	/**
	 * Default post status.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	public $post_status = 'publish';

	/**
	 * A variable to store the menu_id that we create using:
	 * $menu_id = wp_create_nav_menu( 'primary' );
	 *
	 * @access protected
	 *
	 * @var int
	 */
	public $primary_menu_id;

	/**
	 * As the installation process runs,
	 * we will record data about the plugins that are installed.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	public $plugin_installation_data = array();

	/**
	 * An instance of Social_Menu.
	 *
	 * @since 2.5.0
	 * @var Boldgrid\Inspirations\Deploy\Social_Menu
	 */
	public $social_menu;

	/**
	 * Does the user want to start over before deployment?
	 *
	 * @since 1.2.3
	 * @access public
	 */
	public $start_over = false;

	/**
	 * Subcategory ID.
	 *
	 * @var int
	 */
	public $subcategory_id = null;

	/**
	 * Instance of the Survey class.
	 *
	 * @since  1.3.6
	 * @access public
	 * @var    Boldgrid_Inspirations_Survey
	 */
	public $survey;

	/**
	 * Tags containing background images.
	 *
	 * When importing pages, certain tags will have background images set within their style that
	 * we'll need to download.
	 *
	 * @since  1.4.3
	 * @access public
	 * @var    array
	 */
	public $tags_having_background = array( 'div' );

	/**
	 * Theme id.
	 *
	 * Required.
	 *
	 * @since 1.7.0
	 * @var int
	 */
	public $theme_id;

	/**
	 * Constructor.
	 *
	 * @see \Boldgrid\Library\Form\Forms()
	 *
	 * @param array $configs BoldGrid configuration array.
	 */
	public function __construct( $configs ) {
		$this->start_time = time();

		$this->configs = $configs;

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-deploy-pages.php';

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-asset-manager.php';
		$this->asset_manager = new Boldgrid_Inspirations_Asset_Manager();

		$this->install_blog    = isset( $_REQUEST['install-blog'] ) && 'true' === $_REQUEST['install-blog'];
		$this->install_invoice = isset( $_REQUEST['install-invoice'] ) && 'true' === $_REQUEST['install-invoice'];
		$this->install_cache   = isset( $_REQUEST['install-cache'] ) && 'true' === $_REQUEST['install-cache'];

		$this->survey = new Boldgrid_Inspirations_Survey();

		$this->messages = new Boldgrid_Inspirations_Deploy_Messages( $this );

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-external-plugin.php';
		$this->external_plugin = new Boldgrid_Inspirations_External_Plugin();

		add_filter( 'http_request_host_is_external', array( $this, 'allow_downloads_over_the_backlan' ), 10, 3 );

		$this->bgforms = new Boldgrid\Library\Form\Forms();

		$deploy_image = new BoldGrid_Inspirations_Deploy_Image();
		$deploy_image->add_hooks();

		$this->api = new Boldgrid_Inspirations_Deploy_Api( $configs );

		$this->bps = new Boldgrid_Inspirations_Deploy_Bps( $this );

		$this->blog = new Boldgrid_Inspirations_Blog( $configs );

		$this->installed = new Boldgrid_Inspirations_Installed();

		$this->status = new Boldgrid_Inspirations_Deploy_Status();

		$this->deploy_theme = new Boldgrid_Inspirations_Deploy_Theme();
		$this->deploy_theme->set_deploy( $this );

		$this->social_menu = new Boldgrid\Inspirations\Deploy\Social_Menu( $this );
		$this->invoice     = new Boldgrid\Inspirations\Deploy\Invoice( $this );
		$this->cache       = new Boldgrid\Inspirations\Deploy\Cache( $this );
	}

	/**
	 * Get the api key hash.
	 *
	 * @since 1.7.0
	 *
	 * @todo This method can probably go into another class. Created during 1.7.0 while doing some
	 * code cleanup.
	 *
	 * @return string
	 */
	public function get_api_key_hash() {
		$api_key_hash = $this->asset_manager->api->get_api_key_hash();

		// If the hash is missing, then try getting it from the configs.
		if ( empty( $api_key_hash ) ) {
			$api_key_hash = isset( $this->configs['api_key'] ) ? sanitize_text_field( $this->configs['api_key'] ) : null;
		}

		// If the hash is still not found, then check $_REQUEST['key'].
		if ( empty( $api_key_hash ) && ! empty( $_REQUEST['key'] ) ) {
			$api_key_hash = sanitize_text_field( $_REQUEST['key'] );
		}

		return $api_key_hash;
	}

	/**
	 * Getter for configs array.
	 *
	 * @return array $configs
	 */
	public function get_configs() {
		return $this->configs;
	}

	/**
	 * Setter for configs array.
	 *
	 * @param array $configs
	 *
	 * @return bool
	 */
	public function set_configs( $configs ) {
		$this->configs = $configs;
		return true;
	}

	/**
	 * Get deploy details.
	 *
	 * Get all of the details needed so we can deploy a new website.
	 * For example, we need to know which theme to install, which category, etc.
	 *
	 * @todo We are hard coding the details below. In the future, the values
	 *       will be grabbed from the options table.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 */
	public function get_deploy_details() {
		$boldgrid_configs = $this->get_configs();

		$this->page_set_id = intval( $_POST['boldgrid_page_set_id'] );

		$this->set_site_hash();

		$this->theme_id = intval( $_POST['boldgrid_theme_id'] );

		$this->set_pde();

		$this->set_subcategory_id();

		$this->language_id = ( isset( $_POST['boldgrid_language_id'] ) ? intval( $_POST['boldgrid_language_id'] ) : null );

		$this->asset_user_id = ( isset( $_POST['asset_user_id'] ) ? intval( $_POST['asset_user_id'] ) : null );

		$this->new_path = ( isset( $_POST['boldgrid_new_path'] ) ? trim( $_POST['boldgrid_new_path'] ) : '' );

		$this->create_preview_site = ( isset( $_POST['create_preview_site'] ) && $_POST['create_preview_site'] ? true : false );

		$this->ticket_number = ( isset( $_POST['boldgrid_ticket_number'] ) ? sanitize_text_field( $_POST['boldgrid_ticket_number'] ) : false );

		$this->custom_pages = $this->get_pages_param();

		$is_preview_server        = $boldgrid_configs['preview_server'] == 'https://' . $_SERVER['SERVER_NAME'];
		$is_author_preview_server = $boldgrid_configs['author_preview_server'] == 'https://' . $_SERVER['SERVER_NAME'];
		$this->is_preview_server  = $is_preview_server || $is_author_preview_server;

		$this->current_build_cost = 0;

		$this->coin_budget = isset( $_POST['coin_budget'] ) ? intval( $_POST['coin_budget'] ) : 20;

		$this->theme_version_type = isset( $_POST['boldgrid_theme_version_type'] ) ? sanitize_text_field( $_POST['boldgrid_theme_version_type'] ) : 'active';

		$this->page_set_version_type = isset( $_POST['boldgrid_page_set_version_type'] ) ? sanitize_text_field( $_POST['boldgrid_page_set_version_type'] ) : 'active';

		$this->is_author = isset( $_POST['author_type'] ) ? true : false;

		$this->boldgrid_build_profile_id = isset( $_POST['boldgrid_build_profile_id'] ) ? intval( $_POST['boldgrid_build_profile_id'] ) : null;

		if( $this->is_preview_server && isset( $_POST['is_generic'] ) && '1' === $_POST['is_generic'] ) {
			$this->is_generic = true;
		}

		$this->start_over = ! empty( $_POST['start_over'] ) ? true : $this->start_over;

		/**
		 * Filter $this->tags_having_background.
		 *
		 * For example, authors should not process background images.
		 *
		 * @since 1.4.5
		 *
		 * @param array $this->tags_having_background
		 * @param bool  $this->is_author
		 */
		$this->tags_having_background = apply_filters( 'boldgrid_deploy_background_tags', $this->tags_having_background, $this->is_author );
	}

	/**
	 * Sets Deploy Options.
	 *
	 * Store these install options for later use.
	 */
	public function update_install_options() {
		$args = array(
			'author_type'           => isset( $_POST['author_type'] ) ? sanitize_text_field( $_POST['author_type'] ) : null,
			'language_id'           => isset( $_POST['language_id'] ) ? intval( $_POST['language_id'] ) : null,
			'theme_group_id'        => isset( $_POST['theme_group'] ) ? sanitize_text_field( $_POST['theme_group'] ) : null,
			'theme_id'              => intval( $this->theme_id ),
			'theme_version_type'    => sanitize_text_field( $this->theme_version_type ),
			'category_id'           => isset( $_POST['boldgrid_cat_id'] ) ? intval( $_POST['boldgrid_cat_id'] ) : null,
			'subcategory_id'        => intval( $this->subcategory_id ),
			'page_set_id'           => intval( $this->page_set_id ),
			'page_set_version_type' => sanitize_text_field( $this->page_set_version_type ),
			'pde'                   => $this->pde,
			'new_path'              => trim( $this->new_path ),
			'ticket_number'         => sanitize_text_field( $this->ticket_number ),
			'build_profile_id'      => intval( $this->boldgrid_build_profile_id ),
			'custom_pages'          => $this->custom_pages,
			'install_blog'          => $this->install_blog,
			'install_invoice'       => $this->install_invoice,
			'install_cache'         => $this->install_cache,
			'install_timestamp'     => time(),
		);

		$this->installed->update_install_options( $args );
	}

	/**
	 * Grab installation details from the asset server.
	 *
	 * This method is intended to retrieve options in bulk instead of retrieving install data
	 * 1 call at a time.
	 *
	 * @since 1.1.2
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 *
	 * @return array Array of pages.
	 */
	public function remote_install_options() {
		$boldgrid_install_options = $this->installed->get_install_options();

		$api_key_hash = $this->asset_manager->api->get_api_key_hash();

		$args = array(
			'subcategory_id' => $boldgrid_install_options['subcategory_id'],
			'page_set_id'    => $boldgrid_install_options['page_set_id'],
			'key'            => ! empty( $api_key_hash ) ? $api_key_hash : null,
		);

		$remote_options = $this->api->get_install_options( $args );

		$boldgrid_install_options = array_merge( $boldgrid_install_options, $remote_options );

		$this->installed->update_install_options( $boldgrid_install_options );
	}

	/**
	 * Get pages from POST request and return them in an array.
	 *
	 * @return array Array of pages.
	 */
	public function get_pages_param() {
		$pages = array ();

		if ( isset( $_POST['pages'] ) ) {
			$pages = is_array( $_POST['pages'] ) ? $_POST['pages'] : json_decode( stripslashes( trim( $_POST['pages'] ) ), true );
		}

		return $pages;
	}

	/**
	 * Set our site hash.
	 *
	 * @since 1.7.0
	 */
	public function set_site_hash() {
		$boldgrid_configs = $this->get_configs();

		$this->site_hash = ( isset( $_REQUEST['site_hash'] ) ? sanitize_title_with_dashes( trim( $_REQUEST['site_hash'] ) ) : null );

		$this->site_hash = ( ( null == $this->site_hash && isset( $boldgrid_configs['site_hash'] ) ) ? sanitize_title_with_dashes( $boldgrid_configs['site_hash'] ) : $this->site_hash );
	}

	/**
	 * Set subcategory id.
	 *
	 * @since 1.7.0
	 *
	 * @todo subcategory_id is used in deploy_page_sets to get homepage data. Should this actually
	 *       be category_id ?
	 */
	private function set_subcategory_id() {
		$this->subcategory_id = null;

		if ( ! empty( $_POST['boldgrid_sub_cat_id'] ) ) {
			// For most requests:
			$this->subcategory_id = intval( $_POST['boldgrid_sub_cat_id'] );
		} elseif ( ! empty( $_POST['subcategory_id'] ) ) {
			// For direct call to deploy_page_sets:
			$this->subcategory_id = intval( $_POST['subcategory_id'] );
		} else {
			// If subcategory is not available in POST, then try to get it from the install options:
			if ( $this->is_staging_install() ) {
				$install_options = get_option( 'boldgrid_staging_boldgrid_install_options' );
			} else {
				$install_options = $this->installed->get_install_options();
			}

			if ( ! empty ( $install_options['subcategory_id'] ) ) {
				$this->subcategory_id = $install_options['subcategory_id'];
			}
		}
	}

	/**
	 * Create a new site.
	 *
	 * If we're on the preview server, create a new site
	 */
	public function create_new_install() {
		if ( $this->is_preview_server && $this->create_preview_site ) {
			$blog_title = 'Company Name';

			// create the new blog
			$new_blog_id = wpmu_create_blog( $_SERVER['SERVER_NAME'], '/' . $this->new_path, $blog_title, get_current_user_id() );

			if ( is_object( $new_blog_id ) ) {
				echo '<pre>' . print_r( $new_blog_id, 1 ) . '</pre>';
			}

			switch_to_blog( $new_blog_id );

			// Set the blog's admin email address using the network admin email address.
			$email_address = get_site_option( 'admin_email' );

			update_option( 'admin_email' , $email_address );

			// If this is a generic build, then set an option to identify it later (purges, etc.).
			if ( $this->is_generic ) {
				update_option( 'is_generic_build', true );
			}

			// Ensure that we have the current boldgrid_asset information (should be empty).
			$this->asset_manager->get_wp_options_asset();

			// Site needs to be https.
			$path_to_new_blog = esc_url( 'https://' . $_SERVER['SERVER_NAME'] . '/' . $this->new_path );
			update_option( 'siteurl', $path_to_new_blog );
			update_option( 'home', $path_to_new_blog );
			update_option( 'upload_url_path', $path_to_new_blog . '/wp-content/uploads' );

			// Disable comments:
			update_option( 'default_comment_status', 'closed' );
		}
	}

	/**
	 * Delete sample pages.
	 *
	 * @since 1.7.0
	 *
	 * @link https://wordpress.org/support/topic/remove-default-pages-created-on-all-multisites
	 */
	private function delete_sample_pages() {
		$defaultPage = get_page_by_title( __( 'Sample Page' ) );
		if ( $defaultPage ) {
			wp_delete_post( $defaultPage->ID );
		}

		$defaultPage = get_page_by_title( __( 'Hello world!' ), OBJECT, 'post' );
		if ( $defaultPage ) {
			wp_delete_post( $defaultPage->ID );
		}
	}

	/**
	 * Update a site (network) option until successful or timeout.
	 * Same as update_site_option, except with a retry feature with a timeout.
	 * Also returns true if the old value matches the new value, instead of false.
	 *
	 * @param string $option Option name.
	 * @param mixed $value Option value.
	 * @param int $timeout A timeout in seconds. Default is 5 seconds.
	 *
	 * @return bool
	 */
	public function update_site_option_retry( $option = null, $value = null, $timeout = 5 ) {
		// Validate input:
		if ( empty( $option ) || empty( $value ) || ! is_numeric( $timeout ) || $timeout < 0 ) {
			return false;
		}

		// If the current value matches the new value, then return true:
		if ( get_site_option( $option, false, false ) == $value ) {
			return true;
		}

		$start_time = time();
		$success    = false;
		$deadline   = $start_time + $timeout;

		while ( ! $success && time() < $deadline ) {
			if ( update_site_option( $option, $value ) ) {
				// Success: Return true:
				return true;
			}

			usleep( rand( 150000, 250000 ) );
		}

		return false;
	}

	/**
	 * Download the theme chosen by the user and set it as the active theme
	 *
	 * @todo Refactor/rework this method. It should be moved to Boldgrid_Inspirations_Theme_Install.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 *
	 * @return string or false
	 */
	public function deploy_theme() {
 		$boldgrid_configs = $this->get_configs();

 		$api_key_hash = $this->asset_manager->api->get_api_key_hash();

		$args = array(
			'theme_id'           => $this->theme_id,
			'page_set_id'        => $this->page_set_id,
			'theme_version_type' => $this->theme_version_type,
			'is_preview_server'  => $this->is_preview_server,
			'build_profile_id'   => $this->boldgrid_build_profile_id,
			'is_staged'          => ! empty( $_POST['staging'] ) ? trim( $_POST['staging'] ) : null,
			'key'                => ! empty( $api_key_hash ) ? $api_key_hash : null,
			'site_hash'          => ! empty( $boldgrid_configs['site_hash'] ) ? $boldgrid_configs['site_hash'] : null,
			// Misc.
			'has_cache'          => $this->install_cache,
		);

		$this->theme_details = $this->api->get_theme_details( $args );

		if ( is_wp_error( $this->theme_details ) ) {
			$this->messages->print_notice( esc_html__( 'Error: Failed to retrieve theme!', 'boldgrid-inspirations' ) );

			return false;
		}

		if ( ! isset( $this->theme_details->status ) || 200 != $this->theme_details->status ) {
			$this->messages->print_notice( esc_html__( 'Error: Received an unsuccessful return code when retrieving theme information!', 'boldgrid-inspirations' ) );
			return;
		}

		$this->theme_details = $this->theme_details->result->data;

		/*
		 * Temporarily save the theme details.
		 *
		 * When the deployment script was initially written, $this->theme_details was always used
		 * under the assumption that there was no parent / child themes being used.
		 *
		 * $this->theme_details is used in several places within the deployment script. Other than
		 * this deploy_theme method, $this->theme_details is always referring to a single theme
		 * (ie not a parent and a child theme)
		 *
		 * Whew... So basically, if this is the child theme, store the theme_details temporarily.
		 * After the deploy_theme method is complete, set
		 * $this->theme_details = $this->child_theme_details
		 */
		$this->theme_details_original = $this->theme_details;

		$this->messages->print_theme( $this->theme_details );

		// If this is a site preview, set the site title to that of the theme.
		if( $this->is_preview_server && isset( $this->theme_details->themeRevision->Title ) ) {
			update_option( 'blogname', $this->theme_details->themeRevision->Title );
		}

		foreach ( array (
			'child',
			//'parent'
		) as $entity ) {
			if ( 'parent' == $entity ) {
				$this->theme_details = $this->theme_details->parent;
				// If parent doesnt exists, continue (skip this iteration)
				if ( empty( $this->theme_details ) ) {
					continue;
				}
			}

			$theme_folder_name = $this->deploy_theme->get_folder_name();

			// If Crio, reset the theme. V1 themes are reset via $this->start_over().
			if ( $this->deploy_theme->is_crio() ) {
				delete_option( 'theme_mods_' . $theme_folder_name );
			}

			$theme = wp_get_theme( $theme_folder_name );

			// Get the installed theme version timestamp from wp options:
			$theme_version_option_name = 'boldgrid_theme_revision_' .
				 $this->theme_details->themeRevision->Title;

			$theme_dir = ABSPATH . 'wp-content/themes/' . $theme_folder_name;
			$theme_dir_exists = is_dir( $theme_dir );

			if ( is_multisite() ) {
				$installed_theme_version = get_site_option( $theme_version_option_name, null,
					false );

				if ( $installed_theme_version && ! $theme_dir_exists ) {
					delete_site_option( $theme_version_option_name );

					$installed_theme_version = null;
				}
			} else {
				$installed_theme_version = get_option( $theme_version_option_name );

				if ( $installed_theme_version && ! $theme_dir_exists ) {
					delete_option( $theme_version_option_name );

					$installed_theme_version = null;
				}
			}

			/*
			 * If attempting to install over a .git directory, don't install theme. Only do this if
			 * is author because if a git is accidently commited, theme will not install for anyone.
			 */
			$is_git_theme = false;
			if ( $theme_dir_exists && $this->is_author && ! $this->is_preview_server ) {
				$is_git_theme = in_array( '.git', scandir( $theme_dir ) );
			}

			$incoming_theme_version   = $this->theme_details->themeRevision->RevisionNumber;
			$incoming_version_number  = ! empty( $this->theme_details->themeRevision->VersionNumber ) ? $this->theme_details->themeRevision->VersionNumber : null;
			$installed_version_number = is_object( $theme ) ? $theme->get('Version') : null;
			$is_version_change        = $incoming_version_number && ( $incoming_version_number != $installed_version_number );
			$install_this_theme       = ( $is_version_change || ! $theme_dir_exists ) && ! $is_git_theme;

			// Check if theme is already installed and the latest version:
			if ( $install_this_theme ) {
				$theme_url = $this->deploy_theme->get_download_link();

				$theme_installation_done = false;
				$theme_installation_failed_attemps = 0;

				while ( false == $theme_installation_done ) {
					if ( is_multisite() && $this->is_preview_server ) {
						global $wp_version;

						/*
						 * If WordPress >=4.4.0, flush the WordPress object cache, or rely on the
						 * 3rd parameter of get_site_option as false to disable cache.
						 */
						if ( version_compare( $wp_version, '4.4.0', '>=' ) ) {
							wp_cache_flush();
						}

						// Get the WP Option boldgrid_we_are_currently_installing_a_theme:
						$we_are_currently_installing_a_theme = get_site_option(
							'boldgrid_we_are_currently_installing_a_theme', false, false
						);

						if ( $theme_installation_failed_attemps >
							$boldgrid_configs['installation']['max_num_install_attempts'] ) {

							$this->messages->print_notice( esc_html__( 'Error: Exceeded max theme install attempts!', 'boldgrid-inspirations' ) );

							return false;
						}

						/*
						 * Should we install this theme?
						 *
						 * For example, if we're already installing a different theme, then we'll
						 * want to wait before that completes before we install this theme.
						 */
						$theme_install_wait_time = 20;

						if ( false == $we_are_currently_installing_a_theme ) {
							$installed_theme_version = get_site_option( $theme_version_option_name, null, false );

							// Check the current theme version against the incoming version:
							$install_this_theme = ( $installed_theme_version != $incoming_theme_version );

							// Latest theme already installed, so break out of the while loop.
							if ( ! $install_this_theme ) {
								break;
							}
						} elseif ( time() - $we_are_currently_installing_a_theme <
							 $theme_install_wait_time ) {
							// The last install was initiated within the last 20 seconds,
							// so wait a little longer.
							$install_this_theme = false;
						} else {
							/*
							 * The last install theme install fatally failed and/or was more than 20
							 * seconds ago. Either way, try to install this theme.
							 */
							$install_this_theme = true;
						}

						/*
						 * If we do have the 'go ahead' to install a this theme right now, let's try
						 * to 'lock it' so that other themes aren't installed this very moment.
						 */
						if ( true == $install_this_theme ) {
							if ( ! $this->update_site_option_retry( 'boldgrid_we_are_currently_installing_a_theme', time() ) ) {
								$install_this_theme = false;
							} else {
								$this->update_site_option_retry( 'boldgrid_we_are_currently_installing_this_theme', $this->theme_details->theme->Name );
							}
						}

						/*
						 * Multiple themes could be locking at the same time. Let's make sure the
						 * current theme is the the theme that set the lock.
						 */
						if ( true == $install_this_theme ) {
							$we_are_currently_installing_this_theme = get_site_option( 'boldgrid_we_are_currently_installing_this_theme', false, false );
							if ( $this->theme_details->theme->Name != $we_are_currently_installing_this_theme ) {
								$install_this_theme = false;
							}
						}
					} else {
						// Else, we are not on a multisite, so go ahead and try to install the theme.
						$install_this_theme = true;
					}

					/*
					 * If we ultimately decided not to attempt theme installation at this second,
					 * sleep for a bit and try again.
					 */
					if ( false == $install_this_theme ) {
						sleep( 1 );

						$theme_installation_failed_attemps += 0.5;
					} else {
						// Delete the old theme, if exists. We'll update to the latest copy.
						if ( $theme->exists() ) {
							delete_theme( $theme_folder_name );
						}

						// Install the theme:
						include_once ABSPATH . 'wp-admin/includes/file.php';
						include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

						$upgrader = new Theme_Upgrader( new Theme_Installer_Skin() );

						// Download and install the theme:
						$wp_theme_install_success = $upgrader->install( $theme_url,
							array (
								'clear_destination' => true,
								'abort_if_destination_exists' => false
							) );

						// Take action based on whether or not the theme installed.
						if ( ! $wp_theme_install_success || is_wp_error( $wp_theme_install_success ) ) {
							delete_theme( $this->theme_details->theme->Name );

							$theme_installation_failed_attemps ++;

							$this->messages->print_notice( esc_html__( 'Error: Exiting theme deployment.', 'boldgrid-inspirations' ) );

							// On multisite, remove locks.
							if ( is_multisite() ) {
								$we_are_currently_installing_this_theme = get_site_option( 'boldgrid_we_are_currently_installing_this_theme', false, false );

								if ( $this->theme_details->theme->Name === $we_are_currently_installing_this_theme ) {
									delete_site_option( 'boldgrid_we_are_currently_installing_this_theme' );
									delete_site_option( 'boldgrid_we_are_currently_installing_a_theme' );
								}
							}

							return false;
						} else {
							// Looks like the theme was installed successfully.
							$theme_installation_done = true;

							// Set wp options to mark the newly-installed them version.
							if ( is_multisite() ) {
								$this->update_site_option_retry( $theme_version_option_name, $incoming_theme_version );
							} else {
								update_option( $theme_version_option_name, $incoming_theme_version );
							}
						}

						// Regardless of whether we failed or succeeded, we're no longer installing.
						if ( is_multisite() ) {
							delete_site_option( 'boldgrid_we_are_currently_installing_a_theme' );
							delete_site_option( 'boldgrid_we_are_currently_installing_this_theme' );
						}
					}
				} // End of while.
			}

			// Enable Theme Sitewide.
			$allowed_themes = get_site_option( 'allowedthemes' );
			$allowed_themes[$theme_folder_name] = true;
			$this->update_site_option_retry( 'allowedthemes', $allowed_themes );

			if ( 'child' == $entity ) {
				// Save the theme id as a theme mod.
				$this->set_theme_mod_id( $theme_folder_name, $this->theme_details->theme->Id );

				$activation_theme = $theme_folder_name;

				// For authors, activate the git repo instead of the theme.
				if ( $this->is_author && ! empty( $this->theme_details->theme->GitRepoUrl ) ) {
					$repo_name = basename( $this->theme_details->theme->GitRepoUrl );
					if ( file_exists( get_theme_root() . '/' . $repo_name ) ) {
						$activation_theme = $repo_name;
					}
				}

				// Activate the theme.
				switch_theme( $activation_theme );
				update_option( Boldgrid_Inspirations_Deploy_Theme::$theme_deployed, $activation_theme );
			}

			// Enable theme options:
			if ( isset( $this->theme_details->options ) ) {
				foreach ( $this->theme_details->options as $option_obj ) {
					update_option( $option_obj->name, $option_obj->value, '', $option_obj->autoload );
				}
			}

			// Set theme mods.
			foreach ( $this->theme_details->theme_mods as $theme_mod ) {
				set_theme_mod( $theme_mod->name, $theme_mod->value );
			}

			/*
			 * Download any plugins included with this theme.
			 *
			 * For example, the Crio theme may include the Crio Premium plugin.
			 */
			foreach ( $this->theme_details->plugins as $plugin ) {
				$this->download_and_install_plugin(
					$plugin->plugin_zip_url,
					$plugin->plugin_activate_path,
					$plugin->version,
					$plugin
				);
			}

		} // foreach( array ( 'child', 'parent' ) as $entity )

		if ( $this->deploy_theme->is_crio() ) {
			$this->social_menu->deploy();
		}

		// Reset the $this->theme_details variable. Refer to loooon comment above as to why.
		$this->theme_details = $this->theme_details_original;

		do_action( 'boldgrid_deployment_deploy_theme_pre_return', $theme_folder_name );

		return $theme_folder_name;
	}

	/**
	 * Set the theme id of the given theme, as a theme mod
	 *
	 * @param string $theme_name
	 * @param int    $theme_id
	 */
	public function set_theme_mod_id( $theme_name, $theme_id ) {
		$theme_mods = get_option( 'theme_mods_' . $theme_name );
		if ( ! $theme_mods ) {
			$theme_mods = array ();
		}

		$theme_mods['_boldgrid_theme_id'] = $theme_id;
		update_option( 'theme_mods_' . $theme_name, $theme_mods );
	}

	/**
	 * Start over before deployment.
	 *
	 * @since 1.2.3
	 */
	public function start_over() {
		if( ! $this->start_over ) {
			return;
		}

		check_admin_referer( 'deploy', 'deploy' );

		$start_over = new BoldGrid_Inspirations_Start_over();

		$start_over->start_over_active = ( false === $this->is_staging_install() );

		$start_over->start_over_staging = (true === $this->is_staging_install() );

		$start_over->delete_forms = false;

		$start_over->delete_pages = false;

		$start_over->delete_themes = false;

		$start_over->start_over();
	}

	/**
	 * When we install a page we attach post meta data to indicate that it is a boldgrid page
	 * This function returns all pages that are still installed on the users wordpress that were
	 * created by boldgrid
	 *
	 * @return array
	 */
	public function get_existing_pages() {
		$previous_install_options = Boldgrid_Inspirations_Built::find_all_install_options();

		if ( true == $this->is_staging_install() ) {
			$installed_pages = $previous_install_options['boldgrid_staging_options']['installed_pages'];
		} else {
			$installed_pages = $previous_install_options['active_options']['installed_pages'];
		}

		return is_array( $installed_pages ) ? $installed_pages : array ();
	}

	/**
	 * Create pages.
	 *
	 * Download and import the page set the user selected
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 * @link http://codex.wordpress.org/Function_Reference/wp_insert_post
	 */
	public function deploy_page_sets() {
		$pages_created = 0;

		// Set the menu name
		$menu_name = 'primary';

		// Allow plugins, like BoldGrid Staging, to create 'primary-staging' instead of 'primary'.
		$menu_name = apply_filters( 'boldgrid_deployment_primary_menu_name', $menu_name );

		// We want to start fresh, so if the menu exists, delete it.
		$menu_exists = wp_get_nav_menu_object( $menu_name );
		if ( true == $menu_exists ) {
			wp_delete_nav_menu( $menu_name );
		}

		// Create the menu
		$menu_id = wp_create_nav_menu( $menu_name );
		$this->primary_menu_id = $menu_id;

		$this->assign_menu_id_to_all_locations( $menu_id );

		if( $this->install_blog ) {
			$this->blog->create_category();
			$this->set_permalink_structure( '/%category%/%postname%/' );
			$this->blog->create_menu_item( $this->primary_menu_id, 150 );
		}

		// Determine the release channel:
		$options = get_option( 'boldgrid_settings' );

		$release_channel = isset( $options['release_channel'] ) ? $options['release_channel'] : 'stable';

		// Get the theme id, category id, etc.
		$this->get_deploy_details();

		$args = array(
			'page_set_id'           => $this->page_set_id,
			'theme_id'              => $this->theme_id,
			'subcategory_id'        => $this->subcategory_id,
			'page_set_version_type' => $this->page_set_version_type,
			'custom_pages'          => $this->custom_pages,
			'homepage_only'         => false,
			'channel'               => $release_channel,
		);

		$api_key_hash = $this->asset_manager->api->get_api_key_hash();
		if ( ! empty( $api_key_hash ) ) {
			$args['key'] = $api_key_hash;
		}

		$json_response = $this->api->get_page_set( $args );

		// Check response:
		if ( is_wp_error( $json_response ) ) {
			$error_message = $json_response->get_error_message();
			$this->messages->print_notice( esc_html__( 'WP ERROR', 'boldgrid-inspirations' ) . ': ' . esc_html( $error_message ) );
		}

		if ( 200 != $json_response->status ) {
			$this->messages->print_notice( esc_html__( 'Error: Asset server did not return HTTP 200 OK!', 'boldgrid-inspirations' ) );
		}

		if ( empty( $json_response->result->data ) ) {
			$this->messages->print_notice( esc_html__( 'Error: Asset server returned an empty data set!', 'boldgrid-inspirations' ) );
		}

		// Download Plugins needed for pages.
		if ( isset( $json_response->result->data->plugins ) ) {
			foreach ( $json_response->result->data->plugins as $plugin ) {
				$this->download_and_install_plugin(
					$plugin->plugin_zip_url,
					$plugin->plugin_activate_path,
					$plugin->version,
					$plugin
				);

				// If the we have defined configurations for this plugin, configure it.
				if ( ! empty( $plugin->config_script ) ) {
					// Passing page_id to config script.
					$this->plugin_installation_data[ $plugin->plugin_activate_path ];

					// Configure Plugin.
					if ( file_exists( BOLDGRID_BASE_DIR . '/includes/configure_plugin/' . $plugin->config_script ) ) {
							require_once BOLDGRID_BASE_DIR . '/includes/configure_plugin/' . $plugin->config_script;
					}
				}
			}
		}

		// Save the parent category name, if available:
		if ( ! empty( $json_response->result->data->parent_category_name ) ) {
			$this->update_existing_install_options(
				array (
					'parent_category_name' => $json_response->result->data->parent_category_name
				) );
		}

		$pages_in_pageset = isset( $json_response->result->data->pages ) ? $json_response->result->data->pages : array ();
		$additional_pages = ! empty( $json_response->result->data->additional_pages ) ? $json_response->result->data->additional_pages : array ();

		/*
		 * This is a list of the pages that the user requested as well additional pages included in
		 * their category that will later be used to create grid blocks and pages.
		 */
		$this->full_page_list = array (
			'pages' => array (
				'pages_in_pageset' => $pages_in_pageset,
				'additional'       => $additional_pages,
			),
		);

		$this->installed_page_ids = array ();

		$boldgrid_installed_pages_metadata = array ();

		$existing_pages_from_meta_data = $this->get_existing_pages();

		foreach ( $pages_in_pageset as $page_v ) {
			if ( ! is_object( $page_v ) ) {
				continue;
			}

			$is_blog_post = ( isset( $page_v->is_blog_post ) && '1' === $page_v->is_blog_post );

			if( $is_blog_post && ! $this->install_blog ) {
				continue;
			}

			$this->messages->print_page( $page_v );

			/*
			 * Prevent the user from installing the same page twice.
			 *
			 * This was put in place in order to prevent homepages that were installed automatically
			 * from being installed multiple times.
			 */
			if ( in_array( $page_v->id, $existing_pages_from_meta_data ) ) {
				continue;
			}

			$post = array();

			$post['post_content']   = $page_v->code;
			$post['post_name']      = $page_v->page_slug;
			$post['post_title']     = $page_v->page_title;
			$post['post_status']    = $this->post_status;
			$post['post_type']      = $page_v->post_type;
			$post['comment_status'] = 'closed';

			// Allow other plugins to modify the post.
			$post = apply_filters( 'boldgrid_deployment_pre_insert_post', $post );

			$post_id = wp_insert_post( $post );

			// store the pages we created for later use
			$this->installed_page_ids[$page_v->id] = $post_id;

			// Store additional info about the pages.
			$boldgrid_installed_pages_metadata[$post_id] = array(
				'is_readonly' => $page_v->is_readonly,
				'post_type'   => $post['post_type'],
				'post_status' => $post['post_status'],
			);

			// Assign this blog post to our blog category.
			if( $is_blog_post && $this->install_blog ) {
				wp_set_post_categories( $post_id, array( $this->blog->category_id ) );
			}

			// add page to menu
			if ( '1' == $page_v->in_menu ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-object-id' => $post_id,
					'menu-item-parent-id' => 0,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish'
				) );
			}

			// Steps to take if this is the homepage.
			if ( $page_v->homepage_theme_id ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $post_id );

				if ( $this->deploy_theme->is_crio() ) {
					// Don't show the page title.
					add_post_meta( $post_id, 'boldgrid_hide_page_title', 0 );
				}
			}

			/*
			 * Determine if there is post meta to be set.
			 *
			 * This code block below only assigns page templates to pages. Page templates meaning
			 * page-full.php, page-inside.php, etc.
			 */
			if ( isset( $this->theme_details->postmeta ) ) {
				foreach ( $this->theme_details->postmeta as $post_meta ) {
					/*
					 * The column_name will always be "layout". This is because 'layout' is the only
					 * distinct value for columnName currently in the table.
					 */
					$column_name = $post_meta->ColumnName;

					if ( $page_v->$column_name == $post_meta->ColumnValue ) {
						add_post_meta( $post_id, $post_meta->KeyName, $post_meta->KeyValue );
					}
				}
			}

			// Take action if we have a featured image.
			if ( $page_v->featured_image_asset_id ) {
				$this->asset_manager->download_and_attach_asset( $post_id, true, $page_v->featured_image_asset_id );
			}

			// If this page has theme mods, set them.
			if ( ! empty( $page_v->theme_mods ) ) {
				$theme_mods = json_decode( $page_v->theme_mods, true );
				foreach ( $theme_mods as $name => $value ) {
					// Theme mods shouldn't have menu locations. If they do, don't skip them.
					$skips = [ 'nav_menu_locations' ];

					if ( in_array( $name, $skips, true ) ) {
						continue;
					}

					set_theme_mod( $name, $value );
				}
			}

			$pages_created ++;

			// Add the page id so that we can recognize it
			add_post_meta( $post_id, 'boldgrid_page_id', $page_v->id );
		}

		update_option( 'blogdescription', '' );

		// Store the pages we created:
		update_option( 'boldgrid_installed_page_ids', $this->installed_page_ids );
		update_option( 'boldgrid_installed_pages_metadata', $boldgrid_installed_pages_metadata );

		$this->delete_sample_pages();

		// Setup our custom homepage (per theme_id and homepage).
		if ( isset( $this->theme_details->homepage ) ) {
			$this->set_custom_homepage();
		}

		// If we're installing the "Invoice" feature, do all the things now.
		if ( $this->install_invoice ) {
			$this->invoice->deploy( array( 'menu_id' => $menu_id ) );
		}
	}

	/**
	 * Setup primary design elements.
	 *
	 * Primary Design Elements (pde) vary based upon theme group. This function will download and
	 * setup the appropriate pde's.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 */
	public function deploy_pde( $params = array() ) {
		$defaults = array(
			'update_current_themes_mods' => true,
		);

		$params = wp_parse_args( $params, $defaults );

		if ( is_array( $this->pde ) ) {
			foreach ( $this->pde as $pde ) {

				if ( 'header_image' == $pde['pde_type_name'] ||
					 'background_image' == $pde['pde_type_name'] ) {
					/**
					 * ********************************************************
					 * Step 1: Get the asset id of the pde
					 * ********************************************************
					 */
					// get curated item object
					$boldgrid_configs = $this->get_configs();

					$get_curated_url = $boldgrid_configs['asset_server'] .
						 $boldgrid_configs['ajax_calls']['get_curated'];

					$arguments = array (
						'method' => 'POST',
						'body' => array (
							'curated_id' => $pde['pde_curated_id']
						)
					);

					// Get the API key hash.
					$api_key_hash = $this->asset_manager->api->get_api_key_hash();

					if ( ! empty( $api_key_hash ) ) {
						$arguments['body']['key'] = $api_key_hash;
					}

					$response = wp_remote_post( $get_curated_url, $arguments );

					if ( $response instanceof WP_Error ) {
						throw new Exception( esc_html__( 'Error downloading asset.', 'boldgrid-inspirations' ) );
					}

					$data = json_decode( $response['body'] );

					$asset_id = $data->result->data->asset_id;

					/**
					 * ********************************************************
					 * Step 2: Download and attach this asset_id
					 * ********************************************************
					 */
					// Set the last argument to true in order to 'add_meta_data'.
					// This is because the attribution class looks for thumbnails.
					$pde_url = $this->asset_manager->download_and_attach_asset( false, false,
						$asset_id, 'url', true );

					/**
					 * ********************************************************
					 * Step 3: Set the theme mod
					 * ********************************************************
					 */
					if ( $params['update_current_themes_mods'] ) {
						set_theme_mod( 'default_' . $pde['pde_type_name'], $pde_url );
					}

					/*
					 * There may be times we don't want to update the theme mods for the current
					 * theme. If we're using Inspiration's "install new themes", let's save this
					 * theme mod to the new theme's theme_mods, which will become activated once
					 * the user enables the new theme. If we actually DID set the theme mod (done
					 * above), it would affect the user's current live site (IE change their
					 * background / header image).
					 */
					if ( ! $params['update_current_themes_mods'] &&
						 isset( $params['stylesheet'] ) ) {
						$staging_prefix = $this->is_staging_install() ? 'boldgrid_staging_' : '';

						// Create the name of the option we'll be working with.
						$option_name = $staging_prefix . 'theme_mods_' . $params['stylesheet'];

						// If this theme already has theme_mods, get them.
						$theme_mods_for_new_theme = get_option( $option_name );

						// Set the theme mod.
						$theme_mods_for_new_theme[$pde['pde_type_name']] = $pde_url;

						// Save the theme mod. It will take effect if/when the user enables this
						// theme.
						update_option( $option_name, $theme_mods_for_new_theme );
					}
				}
			}
		}
	}

	/**
	 * This function exists and does nothing.
	 *
	 * This is because we're using get_shortcode_regex and we need "imhwpb"
	 * added to the regex list.
	 * In order to do this, we need to use add_shortcode, which requires a
	 * function.
	 *
	 * http://codex.wordpress.org/Function_Reference/add_shortcode
	 * http://codex.wordpress.org/Function_Reference/get_shortcode_regex
	 */
	public function dummy_shortcode_imhwpb() {
	}

	/**
	 * Finish deployment by making sure all after theme switch hooks are fired.
	 *
	 * Reaches out and hits the front end site, which fires all after theme switch hooks.
	 *
	 * For this call, we do not want to fire any crons, this may trigger the framework resetting twice.
	 * # We are sending this via POST because wp-cron.php aborts if $_POST has data.
	 * # We are sending doing_wp_cron because the cron will not fire if that $_GET var exists.
	 *
	 * @since 1.7.0
	 */
	private function after_theme_switch() {
		wp_remote_post(
			get_site_url() . '?doing_wp_cron=fire-after-theme-switch-hooks',
			array(
				'timeout' => 30,
				'method'  => 'POST',
				'body'    => array(
					'dummy_post_data' => 'Dummy post data',
					'install_cache'   => $this->install_cache,
				),
			)
		);
	}

	/**
	 * Allow downloads over the backlan.
	 *
	 * WordPress blocks 10.x.x.x connections.
	 *
	 * @thanks http://www.emanueletessore.com/wordpress-download-failed-valid-url-provided/
	 */
	public function allow_downloads_over_the_backlan( $allow, $host, $url ) {
		$boldgrid_configs = $this->get_configs();

		if ( $host == str_replace( 'https://', '', $boldgrid_configs['asset_server'] ) ) {
			$allow = true;
		}

		return $allow;
	}

	/**
	 * Fail deployment
	 *
	 * @param string $message
	 */
	public function fail_deployment( $message ) {
		$message = esc_html__( 'Deployment failed. We\'re sorry but unfortunately the site deployment failed with the following message:', 'boldgrid-inspirations' ) . ' ' . esc_html( $message );
		$this->messages->print_notice( $message );
	}

	/**
	 * Add to list of allowed attributes.
	 *
	 * @since 1.3.6
	 *
	 * @param  array $allowed
	 * @param  array $context
	 * @return array
	 */
	public function filter_allowed_html( $allowed, $context ) {
		if ( is_array( $context ) ) {
			return $allowed;
		}

		if ( 'post' === $context || 'page' === $context ) {
			$allowed['iframe'] = array(
				'frameborder' => true,
				'src' => true,
				'style' => true,
			);
		}

		return $allowed;
	}

	/**
	 * This function handles things to do after the deployment is done.
	 */
	public function finish_deployment() {
		// This may not be our first deployment. If we have prior kitchen sink data, remove it.
		delete_transient( 'boldgrid_inspirations_kitchen_sink' );

		// Log how long deployment took.
		$install_time = time() - $this->start_time;
		$this->deploy_results['install_time_in_seconds'] = $install_time;

		$this->deploy_results['built_photo_search_placement'] = ! empty( $this->bps->built_photo_search_placement ) ? $this->bps->built_photo_search_placement : null;

		$this->deploy_results['built_photo_search_log'] = $this->bps->built_photo_search_log;

		// This option is used by Boldgrid_Inspirations_Installed::has_built_site().
		update_option( 'boldgrid_has_built_site', 'yes' );

		update_option( 'boldgrid_show_tip_start_editing', 'yes' );

		if ( $this->create_preview_site ) {
			update_option( 'boldgrid_built_as_preview_site', 'yes' );
		}

		// Grab the total coin cost to purchase for publish.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-purchase-for-publish.php';
		$purchase_for_publish = new Boldgrid_Inspirations_Purchase_For_Publish( array (
				'configDir' => BOLDGRID_BASE_DIR . '/includes/config',
		) );

		$this->deploy_results['total_cost_to_purchase_for_publish'] = $purchase_for_publish->get_total_cost_to_purchase_for_publishing();

		/**
		 * After the deployment process is complete. Fire off a completion event.
		 *
		 * @since 1.5.5
		 */
		do_action( 'boldgrid_inspirations_deploy_complete', get_option( 'boldgrid_install_options', array() ) );

		/*
		 * We inteded for the preview server to return a json string. This was not possible however
		 * because WordPress has data echoing that cannot be canceled.
		 *
		 * For example, WordPress prints a "status log" as it's installing a theme / plugin.
		 * This printing cannot be disabled.
		 *
		 * To work around this, We are surrounding our json data with "[RETURN_ARRAY]". We can use
		 * explode to then get the data we need.
		 */
		if ( $this->is_preview_server ) {
			echo '[RETURN_ARRAY]' . json_encode( $this->deploy_results ) . '[RETURN_ARRAY]';
		}

		// Important. This method must be triggered immediately before the after_theme_switch call below.
		$this->status->stop();

		$this->messages->print_heading( 'finish', __( 'Wrapping things up...', 'boldgrid-inspirations' ) );
		$this->after_theme_switch();

		$this->messages->print_complete();

		/*
		 * New licenses may have been added to the account during the deployment. IE a Crio install
		 * may have added a Crio Premium service for the user. Delete license data so it can be refreshed.
		 */
		Boldgrid_Inspirations_Update::delete_license();
	}

	/**
	 * Install sitewide plugins.
	 *
	 * This plugin requests a list of sitewide plugins to be installed, and then installs them.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash().
	 */
	public function install_sitewide_plugins() {
		$options = get_option( 'boldgrid_settings' );

		$args = array(
			'channel' => isset( $options['release_channel'] ) ? $options['release_channel'] : 'stable',
		);

		$api_key_hash = $this->asset_manager->api->get_api_key_hash();
		if ( ! empty( $api_key_hash ) ) {
			$args['key'] = $api_key_hash;
		}

		$plugin_list = $this->api->get_plugins( $args );

		if ( $plugin_list instanceof WP_Error ) {
			throw new Exception( esc_html__( 'Error downloading plugin list.', 'boldgrid-inspirations' ) );
		}

		$plugin_list = isset( $plugin_list->result->data ) ? $plugin_list->result->data : array ();

		// Print plugins we've already installed.
		$this->messages->print_plugins();

		if ( count( $plugin_list ) ) {
			foreach ( $plugin_list as $plugin_list_v ) {
				$slug = explode( '/', $plugin_list_v->plugin_activate_path );
				$slug = $slug[0];
				$this->messages->print_plugin( $plugin_list_v->plugin_title, $slug );

				$this->download_and_install_plugin( $plugin_list_v->plugin_zip_url, $plugin_list_v->plugin_activate_path, $plugin_list_v->version, $plugin_list_v );
			}
		}

		if ( $this->install_cache ) {
			$this->cache->install();
		}
	}

	/**
	 * Determine if this install is for a staging site
	 *
	 * @return bool
	 */
	public function is_staging_install() {
		return ( isset( $_POST['staging'] ) && 1 == $_POST['staging'] );
	}

	/**
	 * If we activated any existing plugins on behalf of the user, print this notices
	 */
	public function get_plugin_activation_notices() {
		$plugin_titles = array ();

		$notices = '';

		foreach ( $this->plugin_installation_data as $plugin ) {
			if ( ! empty( $plugin['forked_plugin_activated'] ) && ! empty( $plugin['full_data']->plugin_title ) ) {
				$plugin_titles[] = $plugin['full_data']->plugin_title;
			}
		}

		if ( count( $plugin_titles ) ) {
			$notices = '<div class="updated auto-updated-plugins"><p>' . esc_html__( 'The following existing plugins where activated for use on your new BoldGrid site:', 'boldgrid-inspirations' ) . '</p><ul>';

			foreach ( $plugin_titles as $plugin_title ) {
				$notices .= "<li>{$plugin_title}</li>";
			}

			$notices .= '</ul></div>';
		}

		return $notices;
	}

	/**
	 * Download and activate a plugin.
	 *
	 * @see Boldgrid_Inspirations_Api::get_api_key_hash()
	 * @see \Boldgrid\Library\Form\Forms::get_preferred_slug()
	 * @see \Boldgrid\Library\Form\Forms::check_forms()
	 * @see \Boldgrid\Library\Form\Forms::install()
	 *
	 * @param string $url A URL such as "https://downloads.wordpress.org/plugin/quick-cache.140829.zip".
	 * @param string $activate_path A plugin path such as "quick-cache/quick-cache.php".
	 * @param string $version Version number.
	 * @param object $full_plugin_data Plugin details.
	 */
	public function download_and_install_plugin( $url, $activate_path, $version, $full_plugin_data ) {
		$installing_form_plugin = preg_match( '/^(wpforms|weforms)/', $activate_path );

		if ( ! $installing_form_plugin ) {
			$this->messages->add_plugin( $full_plugin_data );
		}

		if ( $installing_form_plugin ) {
			// Prevent PHP notice before trying to run a config script.
			$this->plugin_installation_data[ $activate_path ] = null;

			if ( $this->bgforms->get_preferred_slug() ) {
				$result = $this->bgforms->install();

				$this->bgforms->check_forms();

				/*
				 * If we have a $result, a forms plugin is installed and activated. Otherwise, a BoldGrid
				 * form plugin is already installed.
				 */
				if ( $result ) {
					$this->messages->add_plugin_weforms();
				}

				if ( ! $this->bgforms->activate_preferred_plugin() ) {
					$this->messages->print_notice( esc_html__( 'Error: Form plugin activation failed!', 'boldgrid-inspirations' ) );
				}

				return;
			}

			$this->messages->add_plugin_weforms();

			// If $result, then a forms plugin was installed successfully.
			$result = $this->bgforms->install();

			if ( ! $result ) {
				$this->messages->print_notice( esc_html__( 'Error: Plugin installation failed!', 'boldgrid-inspirations' ) );
			}

			return;
		}

		$plugin_path = ABSPATH . 'wp-content/plugins/';

		// Get BoldGrid data for checking wporg plugins.
		$boldgrid_api_data = get_site_transient( 'boldgrid_api_data' );

		// If an old plugin is installed, then do not install the new.  Ensure activation.
		if ( $boldgrid_api_data && ! empty( $boldgrid_api_data->result->data->wporg_plugins ) ) {
			foreach ( $boldgrid_api_data->result->data->wporg_plugins as $wporg_plugin ) {
				$old_plugin_file = $wporg_plugin->old_slug . '/' . $wporg_plugin->old_slug . '.php';

				// The plugin already exists.
				if ( false !== strpos( $activate_path, $wporg_plugin->slug ) &&
					file_exists( $plugin_path . $old_plugin_file ) ) {

						// Activate, if needed.
						if ( ! $this->external_plugin->is_active( $old_plugin_file ) ) {
							$result = activate_plugin( $old_plugin_file );

							if ( is_wp_error( $result ) ) {
								$this->messages->print_notice( esc_html__( 'Plugin activation failed.', 'boldgrid-inspirations' ) );
							}
						}

						return;
				}
			}
		}

		$boldgrid_configs = $this->get_configs();

		// If ASSET_SERVER in plugin url name, then replace it from configs.
		if ( false !== strpos( $url, 'ASSET_SERVER' ) ) {
			// Replace ASSET_SERVER with the asset server name
			$url = str_replace( 'ASSET_SERVER', $boldgrid_configs['asset_server'], $url );

			$api_key_hash = $this->asset_manager->api->get_api_key_hash();

			// Attach the api key:
			if ( ! empty( $api_key_hash ) ) {
				$url .= '&key=' . $api_key_hash;
			}
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// Check if the version we are trying to install.
		$plugin_version_already_exists = false;

		$absolute_activation_path = $plugin_path . $activate_path;

		if ( file_exists( $absolute_activation_path ) ) {
			$plugin_version_already_exists = true;

			/*
			 * For reference of $comparison:
			 *
			 * -1: An older version of the plugin is installed. Update version using WordPress Updates.
			 *  0: Plugin version is already installed.
			 *  1: A newer version (%1$s) of the plugin is already installed.
			 */
			// $plugin_data = get_plugin_data( $absolute_activation_path );
			// $comparison = version_compare( $plugin_data['Version'], $version );
		}

		// If the user already has the parent plugin skip this installation, init settings:
		$this->plugin_installation_data[$activate_path] = array (
			'forked_plugin_exists'    => false,
			'forked_plugin_active'    => false,
			'forked_plugin_activated' => false,
			'full_data'               => $full_plugin_data
		);

		// Do not install plugins if the forked plugin exists:
		$original_active_path = $activate_path; // <-- overwriting if activating forked plugin

		$forked_plugin_active = false;

		// Check for a forked version.
		if ( ! empty( $full_plugin_data->forked_plugin_path ) &&
			file_exists( $plugin_path . $full_plugin_data->forked_plugin_path ) ) {
				$forked_plugin_active = $this->external_plugin->is_active( $full_plugin_data->forked_plugin_path );

				$this->plugin_installation_data[$activate_path] = array (
					'forked_plugin_active' => $forked_plugin_active,
					'forked_plugin_exists' => true,
				);
		}

		// If the plugin still needs to be installed, then do it.
		if ( ! $plugin_version_already_exists ) {
			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin() );

			/*
			 * Install the plugin.
			 *
			 * There's an issue if:
			 * 1. is_object( $upgrader->skin->result ) &&
			 * 2. ( is_wp_error( $upgrader->skin->result ) || false == $upgrader->skin->result )
			 *
			 * Plugin installation not complete. Either the destination folder already exists, or
			 * the plugin files already exist. Review $upgrader->skin->result->get_error_message().
			 */
			$upgrader->install( $url );
		}

		$boldgrid_plugin_active = $this->external_plugin->is_active( $activate_path );

		// Activate the plugin, if the BoldGrid or forked plugins are not already active.
		if ( ! $boldgrid_plugin_active && ! $forked_plugin_active ) {
			$result = activate_plugin( $activate_path );

			// Check for activation error.
			if ( is_wp_error( $result ) ) {
				$this->messages->print_notice( esc_html__( 'Error: Plugin activation failed!', 'boldgrid-inspirations' ) );
			} elseif ( $this->plugin_installation_data[$original_active_path]['forked_plugin_exists'] && false == $forked_plugin_active ) {
				/*
				 * In the case that the activation of this plugin was a success and the plugin was a
				 * fork, set "forked_plugin_activated" so that we can display a message to the user
				 */
				$this->plugin_installation_data[$original_active_path]['forked_plugin_activated'] = true;
			}
		}
	}

	/**
	 * Include grid system css.
	 *
	 * @return null
	 */
	public function add_grid_system() {
		// Before we can include the grid system, we need to allow css files to be uploaded.
		// @thanks http://www.paulund.co.uk/change-wordpress-upload-mime-types
		add_filter( 'upload_mimes', 'boldgrid_add_custom_mime_types' );

		/**
		 *
		 * @param array $mimes An associative array of mime types
		 *
		 * @return array Merged associative array of mime types.
		 */
		function boldgrid_add_custom_mime_types( $mimes ) {
			return array_merge( $mimes, array (
				'css' => 'text/css'
			) );
		}
	}

	/**
	 * Set custom homepage
	 */
	public function set_custom_homepage() {
		$homepage_var = '';

		foreach ( $this->theme_details->homepage as $homepage_step_obj ) {
			switch ( $homepage_step_obj->action ) {
				case 'page' :

					$page_type = $homepage_step_obj->page->post_type;

					// only create the page if it doesn't already exist
					$existing_page = get_page_by_title( $homepage_step_obj->page->page_title,
						OBJECT, $page_type );

					if ( null === $existing_page ) {
						// Insert the page.
						$post = array(
							'post_content' => $homepage_step_obj->page->code,
							'post_name'    => $homepage_step_obj->page->page_slug,
							'post_title'   => $homepage_step_obj->page->page_title,
							'post_status'  => 'publish',
							'post_type'    => $page_type,
						);

						$post_id = wp_insert_post( $post );
					} else
						$post_id = $existing_page->ID;

					// Take action if we have any featured images.
					if ( 0 != $homepage_step_obj->page->featured_image_asset_id ) {
						$this->asset_manager->download_and_attach_asset( $post_id, true,
							$homepage_step_obj->page->featured_image_asset_id );
					}

					if ( ! empty( $homepage_step_obj->return_value_save_as ) ) {
						switch ( $homepage_step_obj->return_value_save_as ) {
							case 'array_item' :
								$homepage_var[$homepage_step_obj->return_value_save_to][] = $post_id;

								break;

							case 'option' :
								update_option( $homepage_step_obj->return_value_save_to, $post_id );

								break;

							default :
								// if there is a ':' after 'option'
								if ( substr( $homepage_step_obj->return_value_save_as, 0, 7 ) == 'option:' ) {
									$exploded_return_value_save_as = explode( ':', $homepage_step_obj->return_value_save_as );

									$option_value_key = $exploded_return_value_save_as[1];

									// if the key ends in "[]"
									if ( substr( $option_value_key, - 2 ) == '[]' ) {
										$option_value_key = str_replace( '[]', '', $option_value_key );

										$current_option = get_option( $homepage_step_obj->return_value_save_to );

										$current_option[$option_value_key][] = $post_id;

										update_option( $homepage_step_obj->return_value_save_to, $current_option );
									}
								}

								break;
						}
					}

					break;

				case 'option' :

					// update sting option
					if ( empty( $homepage_step_obj->option->value_key ) ) {
						update_option( $homepage_step_obj->option->name, $homepage_step_obj->option->value );
					} else {
						$current_option = get_option( $homepage_step_obj->option->name );

						$current_option[$homepage_step_obj->option->value_key] = $homepage_step_obj->option->value_value;

						update_option( $homepage_step_obj->option->name, $current_option );
					}

					break;

				case 'process_return_value' :
					update_option( $homepage_step_obj->return_value_save_to, $homepage_var[$homepage_step_obj->return_value_save_to] );

					break;

				case 'download_asset' :
					$url_to_uploaded_asset = $this->asset_manager->download_and_attach_asset( false, false, $homepage_step_obj->action_id );

					if ( substr( $homepage_step_obj->return_value_save_as, 0, 7 ) == 'option:' ) {
						$exploded_return_value_save_as = explode( ':', $homepage_step_obj->return_value_save_as );

						$option_value_key = $exploded_return_value_save_as[1];

						$current_option = get_option( $homepage_step_obj->return_value_save_to );

						$current_option[$option_value_key] = $url_to_uploaded_asset;

						update_option( $homepage_step_obj->return_value_save_to, $current_option );
					}

					break;

				case 'get_permalink' :
					$existing_page = get_page_by_title( $homepage_step_obj->page->page_title, OBJECT, $homepage_step_obj->page->post_type );

					$post_id = $existing_page->ID;

					$permalink = get_permalink( $post_id );

					update_option( $homepage_step_obj->return_value_save_to, $permalink );

					break;

				case 'add_widget_text' :
					// Add the widget to the database.
					$widget = array(
						'title'        => $homepage_step_obj->widget_text->title,
						'text'         => $homepage_step_obj->widget_text->text,
						'filter'       => $homepage_step_obj->widget_text->filter,
						'_multiwidget' => $homepage_step_obj->widget_text->_multiwidget,
					);

					// get current widgets
					$current_widgets = get_option( 'widget_text' );

					// add our new text widget
					$current_widgets[] = $widget;

					end( $current_widgets );
					$widget_key = key( $current_widgets );

					// update widgets
					update_option( 'widget_text', $current_widgets );

					// Update the sidebar widget.
					$current_sidebar_widgets                                             = get_option( 'sidebars_widgets' );
					$current_sidebar_widgets[$homepage_step_obj->return_value_save_to][] = "text-" . $widget_key;
					update_option( 'sidebars_widgets', $current_sidebar_widgets );

					break;
			}
		}
	}

	/**
	 * Change the permalink structure.
	 *
	 * @since 1.3.6
	 *
	 * @global object $wp_rewrite.
	 *
	 * @param string $structure
	 */
	public function set_permalink_structure( $structure ) {
		global $wp_rewrite;

		$set_permalinks = true;

		/**
		 * Continue with setting permalink structure.
		 *
		 * Filter to allow a plugin to determine whether or not to proceed with this request to
		 * set new permalinks.
		 *
		 * @since 1.3.6
		 *
		 * @param bool $set_permalinks On true, continue on to setting permalinks.
		 */
		$set_permalinks = apply_filters( 'pre_set_permalinks', $set_permalinks );

		if( ! $set_permalinks ) {
			return;
		}

		$wp_rewrite->set_permalink_structure( $structure );

		update_option( 'category_base', '.' );

		/*
		 * We need to make sure that a .htaccess file is being created. If it is not, 404 pages may
		 * be handled by .htaccess rules set in higher directories.
		 *
		 * The parameter we're passing, $hard, is defined by WordPress as so:
		 * Whether to update .htaccess (hard flush) or just update rewrite_rules
		 * transient (soft flush).
		 */
		flush_rewrite_rules( true );
	}

	/**
	 * Set the pde.
	 *
	 * @since 1.7.0
	 */
	private function set_pde() {
		if ( isset( $_POST['boldgrid_pde'] ) ) {
			if ( is_array( $_POST['boldgrid_pde'] ) ) {
				$this->pde = is_array( $_POST['boldgrid_pde'] ) ? $_POST['boldgrid_pde'] : null;
			} else {
				$this->pde = json_decode( stripslashes( $_POST['boldgrid_pde'] ), true );
			}
		} else {
			$this->pde = null;
		}
	}

	/**
	 * Assign a menu_id to all locations
	 */
	public function assign_menu_id_to_all_locations( $menu_id ) {
		/*
		 * Get the current assignment of menu_id's to theme menu locations. Generally will be blank
		 * at this point.
		 */
		$locations = get_theme_mod( 'nav_menu_locations' );

		/*
		 * Get the nav menus registered by current theme.
		 *
		 * $registered_nav_menus = Array
		 * (
		 * * [primary] => Primary Menu
		 * )
		 *
		 * The keys are the locations, while the values are the descriptions.
		 */
		$registered_nav_menus = get_registered_nav_menus();

		/*
		 * There may be a timing issue related to this that we'll need to resolve in the future.
		 * We're trying to get registered_nav_menus before the theme has been able to register them.
		 * If this is the case, the theme data from the asset server may need to contain the
		 * registered nav menus.
		 */
		$additional_menus_to_register = array (
			'primary' => 'Primary menu'
		);

		foreach ( $additional_menus_to_register as $name => $description ) {
			if ( ! isset( $registered_nav_menus[$name] ) ) {
				$registered_nav_menus[$name] = $description;
			}
		}

		/*
		 * Assign this new menu_id to those locations if they're not already set.
		 *
		 * # primary Needed for all v1 themes.
		 * # main    Added to accomdate Crio.
		 */
		if ( $this->deploy_theme->is_crio() ) {
			$locations['main'] = $menu_id;
		} else {
			$locations['primary'] = $menu_id;
		}

		/*
		 * We've finished updating $locations, it now looks like this:
		 *
		 * $locations = Array
		 * (
		 * * [primary] => 2
		 * )
		 */
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Build the attribution page.
	 */
	public function build_attribution_page() {
		// create the attribution page
		$settings = array (
			'configDir' => BOLDGRID_BASE_DIR . '/includes/config',
			'menu_id'   => $this->primary_menu_id
		);

		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-attribution.php';

		$attribution = new Boldgrid_Inspirations_Attribution( $settings );

		$attribution->build_attribution_page();
	}

	/**
	 * Full deployment.
	 *
	 * @return boolean
	 */
	public function full_deploy() {
		$this->status->start();

		$this->create_new_install();

		$this->update_install_options();

		/*
		 * Pass the requested install options to the asset server and return install
		 * options that will be stored in the users WP.
		 */
		$this->remote_install_options();

		// Install the selected theme.
		$deploy_theme_success = $this->deploy_theme();

		// If theme deployemnt fails, then show a message to choose a different theme.
		if ( ! $deploy_theme_success ) {
			// Add info to the deployment log.
			$this->messages->print_notice( esc_html__( 'Theme deployment failed.  Please choose another theme.', 'boldgrid-inspirations' ) );

			return false;
		}

		// import the selected page set.
		$this->deploy_page_sets();

		$boldgrid_inspiration_deploy_pages = new Boldgrid_Inspirations_Deploy_Pages( array (
			'post_status' => $this->post_status,
		) );

		// Create temp pages in order to force image creation.
		$this->installed_page_ids = $boldgrid_inspiration_deploy_pages->deploy_temp_pages( $this->full_page_list, $this->installed_page_ids );

		// Download / setup the images required for each page/post.
		$this->bps->deploy();

		// Remove Temp pages that were created in order to force image creation.
		$boldgrid_inspiration_deploy_pages->cleanup_temp_pages( $this->full_page_list, $this->installed_page_ids );

		// download / setup the primary design elements.
		$this->deploy_pde();

		// create the attribution page.
		$this->build_attribution_page();

		// Install Site Wide Plugins.
		if ( false == $this->is_preview_server ) {
			$this->install_sitewide_plugins();
		}

		$this->finish_deployment();

		return true;
	}

	/**
	 * Update existing install options
	 *
	 * @param array $options
	 */
	public function update_existing_install_options( $options = array() ) {
		( $existing_options = get_option( 'boldgrid_install_options' ) ) ||
			 ( $existing_options = get_option( 'imhwpb_install_options' ) ) ||
			 ( $existing_options = array () );

		$options_merged = array_merge( $existing_options, $options );

		update_option( 'boldgrid_install_options', $options_merged );
	}

	/**
	 * Check the permalink structure, if no active site, set to "/%postname%/" if needed
	 */
	private function check_permalink_structure() {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( '/%postname%/' != $permalink_structure && ! Boldgrid_Inspirations_Built::has_active_site() ) {
			 $this->set_permalink_structure( '/%postname%/' );
		}
	}

	/**
	 * Deployment
	 */
	public function do_deploy() {
		$boldgrid_configs = $this->get_configs();

		// Set the PHP max_execution_time to 120 seconds (2 minutes):
		@ini_set( 'max_execution_time', 120 );

		// Start XHProf.
		if ( ! empty( $boldgrid_configs['xhprof'] ) && extension_loaded( 'xhprof' ) ) {
			xhprof_enable( XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY );
		}

		$this->get_deploy_details();

		$this->check_permalink_structure();

		$this->start_over();

		$this->survey->deploy();

		/*
		 * During deployment only, allow iframes (Google map iframe). This seems to be required
		 * on multisite / preview servers.
		 */
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_allowed_html', ), 10, 2 );

		$success = $this->full_deploy();

		/*
		 * Add a hidden var to show whether or not deployment was a success. Inspirations by default
		 * redirects the user to the "My Inspirations" page after the deployment is finished. But, if
		 * the install failed, it shouldn't redirect so we can see error messages.
		 */
		echo '<input type="hidden" name="deployment_success" value="' . ( $success ? 1 : 0 ) . '" />';

		// Save report to the log.
		if ( ! empty( $boldgrid_configs['xhprof'] ) && extension_loaded( 'xhprof' ) ) {
			$xhprof_data = xhprof_disable();

			$xhprof_utils_path = '/usr/share/pear/xhprof_lib/utils';

			if ( file_exists( $xhprof_utils_path . '/xhprof_lib.php' ) &&
				 file_exists( $xhprof_utils_path . '/xhprof_runs.php' ) ) {
				require_once $xhprof_utils_path . '/xhprof_lib.php';
				require_once $xhprof_utils_path . '/xhprof_runs.php';

				$xhprof_runs = new XHProfRuns_Default();
				$run_id = $xhprof_runs->save_run( $xhprof_data, 'xhprof_testing' );

				error_log(
					__METHOD__ . ': https://' . $_SERVER['HTTP_HOST'] . '/xhprof/index.php?run=' .
						 $run_id . '&source=xhprof_testing' );
			}
		}
	}
}
