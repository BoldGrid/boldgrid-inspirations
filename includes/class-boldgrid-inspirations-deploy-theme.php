<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Inspirations_Deploy_Theme
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid <support@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Deploy Theme class.
 *
 * @since 1.5.1
 */
class Boldgrid_Inspirations_Deploy_Theme {

	/**
	 * Option name signifying we've installed and switch_theme'd during an
	 * Inspirations install.
	 *
	 * @since 1.5.1
	 */
	public static $theme_deployed = 'boldgrid_inspirations_theme_deployed';

	/**
	 * Add hooks.
	 *
	 * @since 1.5.1
	 */
	public function add_hooks() {
		add_action( 'after_switch_theme', array( $this, 'wp_menus_changed' ), 9 );
	}

	/**
	 * Our deploy class.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy
	 */
	private $deploy;

	/**
	 * Get an attribute from the theme details.
	 *
	 * @since SINCEVERSION
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get_attribute( $key, $default = false ) {
		return isset( $this->deploy->theme_details->theme->$key ) ? $this->deploy->theme_details->theme->$key : $default;
	}

	/**
	 * Get the url to download a theme.
	 *
	 * @since SINCEVERSION
	 */
	public function get_download_link() {
		/*
		 * The if conditional handles themes with a download url set (originally added with the introduction
		 * of Crio). The else section implements the original logic (prior to Crio).
		 */
		if ( ! empty( $this->deploy->theme_details->theme->DownloadUrl ) ) {
			$theme_url = $this->deploy->theme_details->theme->DownloadUrl;
		} else {
			$boldgrid_configs = $this->deploy->get_configs();
			$api_key_hash     = $this->deploy->asset_manager->api->get_api_key_hash();

			$theme_url = $boldgrid_configs['asset_server'] .
				$boldgrid_configs['ajax_calls']['get_asset'] . '?id=' .
				$this->deploy->theme_details->themeRevision->AssetId;

			if ( ! empty( $api_key_hash ) ) {
				$theme_url .= '&key=' . $api_key_hash;
			}

			// If this is a user environment, install from repo.boldgrid.com.
			if ( ! $this->deploy->is_preview_server ) {
				$theme_url = $this->deploy->theme_details->repo_download_link;
			}
		}

		return $theme_url;
	}

	/**
	 * Get the theme folder name.
	 *
	 * The theme folder name is the same as the theme name.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string
	 */
	public function get_folder_name() {
		/*
		 * When adding Crio to Inspirations, we've began setting a download url. If we have a download
		 * url, such as: https://downloads.wordpress.org/theme/crio.latest-stable.zip
		 * Then, convert "crio.latest-stable.zip" to "crio".
		 *
		 * The else statement implements the original functionality.
		 */
		if ( ! empty( $this->deploy->theme_details->theme->DownloadUrl ) ) {
			$to_strip = [ '.latest-stable', '.zip' ];

			$theme_folder_name = wp_basename( $this->deploy->theme_details->theme->DownloadUrl );

			foreach ( $to_strip as $strip ) {
				$theme_folder_name = str_replace( $strip, '', $theme_folder_name );
			}
		} else {
			$theme_folder_name = $this->deploy->theme_details->theme->Name;

			if ( $this->deploy->is_preview_server ) {
				// Use the random filename instead.
				$theme_folder_name = wp_basename( $this->deploy->theme_details->themeAssetFilename, '.zip' );
			}
		}

		return $theme_folder_name;
	}

	/**
	 * Get the url to the theme's screenshot.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string
	 */
	public function get_screenshot_url() {
		if ( $this->is_repo_theme() ) {
			$screenshot = $this->get_theme_information()->screenshot_url;
		} else {
			$screenshot = unserialize( $this->deploy->theme_details->theme->Meta )['Screenshot'];
		}

		return $screenshot;
	}

	/**
	 * Get our theme.
	 *
	 * @since SINCEVERISON
	 *
	 * @return WP_Theme
	 */
	public function get_theme() {
		return wp_get_theme( $this->get_folder_name() );
	}

	/**
	 * Get theme info from the WordPress api.
	 *
	 * @since SINCEVERSION
	 *
	 * @return mixed False on failure, object on success. Example: https://pastebin.com/Z9qt3KFF
	 */
	public function get_theme_information() {
		$request = wp_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $this->get_folder_name() );

		if( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		return json_decode( $body );
	}

	/**
	 * Determine whether or not this theme is crio.
	 *
	 * @since SINCEVERSION
	 *
	 * @return bool
	 */
	public function is_crio() {
		return 'crio' === $this->get_folder_name();
	}

	/**
	 * Determine whether or not this is a WordPress repo theme.
	 *
	 * @since SINCEVERSION
	 */
	public function is_repo_theme() {
		$prefix = 'https://downloads.wordpress.org/theme/';

		return ! empty( $this->deploy->theme_details->theme->DownloadUrl ) &&
			substr( $this->deploy->theme_details->theme->DownloadUrl, 0, strlen( $prefix ) ) === $prefix;
	}

	/**
	 * Set our deploy class.
	 *
	 * @since SINCEVERISON
	 *
	 * @param Boldgrid_Inspirations_Deploy $deploy
	 */
	public function set_deploy( $deploy ) {
		$this->deploy = $deploy;
	}

	/**
	 * Remove WordPress' _wp_menus_changed action after deployment.
	 *
	 * As of WordPress 4.9, WordPress tries to match up your old menu locations
	 * to your new menu locations after a theme switch. We do not need this to
	 * happen because we are handling the menu setup during Inspirations.
	 *
	 * If we didn't do this, menus assignments set during an Inspirations
	 * install would be overwritten by _wp_menus_changed.
	 *
	 * @since 1.5.1
	 *
	 * @link https://core.trac.wordpress.org/ticket/39692
	 * @link https://core.trac.wordpress.org/attachment/ticket/39692/39692.diff
	 */
	public function wp_menus_changed() {
		$theme_deployed = get_option( self::$theme_deployed );

		if( ! empty( $theme_deployed ) ) {
			remove_action( 'after_switch_theme', '_wp_menus_changed', 10 );
		}

		delete_option( self::$theme_deployed );
	}
}
