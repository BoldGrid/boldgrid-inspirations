<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Config
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Config class.
 */
class Boldgrid_Inspirations_Config {

	/**
	 * Class property for asset server availability
	 *
	 * @since 1.2.2
	 * @access private
	 * @var array
	 * @static
	 */
	private static $configs = array();


	/**
	 * Check if a feature has been enabled for a users release channel.
	 *
	 * @since 1.10
	 *
	 * @return boolean Whether or not the feature is enabled on given branch.
	 */
	public static function has_feature( $feature, $configs ) {
		$has_feature = false;
		$channels = ! empty( $configs['features'][ $feature ] ) ? $configs['features'][ $feature ] : false;

		if ( $channels ) {
			$boldgrid_settings = get_option( 'boldgrid_settings', array() );

			$release_channel = ! empty( $boldgrid_settings['release_channel'] ) ?
				$boldgrid_settings['release_channel'] : 'stable';

			if ( in_array( $release_channel, $channels ) ) {
				$has_feature = true;
			}

		}

		return $has_feature;
	}

	/**
	 * Get formated configurations.
	 *
	 * This static function is used by boldgrid-inspirations and other BoldGrid plugins.
	 *
	 * @return array
	 */
	public static function get_format_configs() {
		// If configs were already read, then just return the array.
		if ( ! empty( self::$configs)) {
			return self::$configs;
		}

		// Set the configuration directory.
		$config_dir = BOLDGRID_BASE_DIR . '/includes/config';

		// Set the path to the global configuration file.
		$global_configs = require $config_dir . '/config.plugin.php';

		// Initialize $local_configs.
		$local_configs = array();

		// If local file exists, then read it.
		if ( file_exists( $local_config_filename = $config_dir . '/config.local.php' ) ) {
			$local_configs = require $local_config_filename;
		}

		// If the user has an api key stored in their database, then set it as the global api_key.
		$api_key_from_database = get_option( 'boldgrid_api_key' );

		if ( ! empty( $api_key_from_database ) ) {
			$global_configs['api_key'] = $api_key_from_database;
		}

		// Check for site hash in WP Options, if present, add to config array.
		$site_hash = get_option( 'boldgrid_site_hash' );

		if ( ! empty( $site_hash ) ) {
			$global_configs['site_hash'] = $site_hash;
		}

		// Add the siteurl to the array.
		$global_configs['site_url'] = get_site_url();

		// Merge global and local configs.
		if ( ! empty( $local_configs ) ) {
			$formated_configs = array_merge( $global_configs, $local_configs );
		} else {
			$formated_configs = $global_configs;
		}

		// Add boldgrid_settings to our configs.
		// @since 1.0.10
		if ( ! isset( $formated_configs['settings'] ) ) {
			$formated_configs['settings'] = get_option( 'boldgrid_settings' );
		}

		$formated_configs['settings'] = self::set_default_settings( $formated_configs['settings'] );

		/**
		 * Allow filtering of configs.
		 *
		 * @since 1.3.6
		 *
		 * @param array $formated_configs
		 */
		$formated_configs = apply_filters( 'boldgrid_inspirations_configs', $formated_configs );

		// Save the config array.
		self::$configs = $formated_configs;

		// Return the configuration array.
		return $formated_configs;
	}

	/**
	 * Reset our configs.
	 *
	 * This is useful for pages where the configs are changed mid page load. This class caches the
	 * configs (see the first lines of self::get_format_configs()). If the configs are changed mid
	 * page load, we'll need to reset that cache.
	 *
	 * @since SINCEVERSION
	 */
	public static function reset_configs() {
		self::$configs = array();
	}

	/**
	 * Configure default settings.
	 *
	 * Pass in an array of BoldGrid settings. For our default settings, set those defaults in the
	 * array if they don't already have a value.
	 *
	 * You may be missing default settings if you've never gone to the settings page and clicked
	 * save to save them, or you may have deleted your boldgrid_settings option.
	 *
	 * @since 1.2.10
	 *
	 * @param  array $settings An array of BoldGrid settings.
	 * @return array $settings.
	 */
	public static function set_default_settings( $settings = array() ) {
		if( ! is_array( $settings ) ) {
			$settings = array();
		}

		$defaults = array(
			// By default, do not rearrange the dashboard menus.
			'boldgrid_menu_option' => '0',
  			'boldgrid_feedback_optout' => '0',
  			'release_channel' => 'stable',
  			'theme_release_channel' => 'stable',
  			'plugin_autoupdate' => '0',
  			'theme_autoupdate' => '0',
		);

		foreach( $defaults as $setting => $value ) {
			if( ! isset( $settings[ $setting ] ) ) {
				$settings[ $setting ] = $value;
			}
		}

		return $settings;
	}

	/**
	 * Get a setting from $configs['settings'].
	 *
	 * @since 1.7.0
	 *
	 * @param  string $key The setting's key.
	 * @param  mixed  $default The default value to return if setting doesn't exist.
	 * @return mixed
	 */
	public static function get_setting( $key, $default = false ) {
		$configs = self::get_format_configs();

		return isset( $configs['settings'][ $key ] ) ? $configs['settings'][ $key ] : $default;
	}

	/**
	 * Get whether or not we're using the BoldGrid menu system.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public static function use_boldgrid_menu() {
		$menu_option = self::get_setting( 'boldgrid_menu_option' );

		return ! empty( $menu_option );
	}
}
