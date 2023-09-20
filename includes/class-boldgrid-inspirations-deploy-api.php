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
 * BoldGrid Inspirations Deploy API class.
 *
 * This class is responsible for making api calls related to the deployment process.
 *
 * @since 1.7.0
 */
class Boldgrid_Inspirations_Deploy_Api {

	/**
	 * An array of configs.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var array
	 */
	private $configs;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param array $configs {
	 *     An array of configs.
	 *
	 *     @type string $asset_server Without trailing slash.
	 *     @type array  $ajax_calls   An array of ajax calls.
	 * }
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
	}

	/**
	 * Get install options.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type int    $subcategory_id Example: 32
	 *     @type int    $page_set_id    Example: 7
	 *     @type string $key            32 char md5 hash.
	 * }
	 * @return array Example return value: https://pastebin.com/pP9AiXT6
	 */
	public function get_install_options( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['get_install_details'];

		$remote_post_args = array (
			'method'  => 'POST',
			'body'    => $args,
			'timeout' => 20,
		);

		$response = wp_remote_retrieve_body( wp_remote_post( $api_url, $remote_post_args ) );
		$response = json_decode( $response ?  : '', true );

		$remote_options = ( ! empty( $response['result']['data'] ) ? $response['result']['data'] : array() );

		return $remote_options;
	}

	/**
	 * Get Build Profile.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args Array of arguments
	 *
	 * @return array
	 */
	public function get_build_profile( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['get_build_profile'];

		$remote_post_args = array(
			'method'  => 'POST',
			'body'    => $args,
			'timeout' => 20,
		);

		$response = wp_remote_retrieve_body( wp_remote_post( $api_url, $remote_post_args ) );
		$response = json_decode( $response ? $response : '', true );

		$build_profile = ( ! empty( $response['result']['data']['profile'] ) ? $response['result']['data']['profile'] : null );

		return $build_profile;
	}

	/**
	 * Get page set.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type int    $theme_id              Example: 48
	 *     @type int    $page_set_id           Example: 7
	 *     @type int    $subcategory_id        Example: 32
	 *     @type string $page_set_version_type Example: stable
	 *     @type mixed  $custom_pages          Example: An array or null.
	 *     @type bool   $homepage_only         Example: false
	 *     @type string $channel               Example: stable
	 *     @type string $key                   32 char md5 hash.
	 * }
	 * @return stdClass Example return: https://pastebin.com/B7a4k9U6
	 */
	public function get_page_set( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['get_page_set'];

 		$remote_post_args = array(
 			'method' => 'POST',
 			'body'   => $args,
 		);

 		$response = wp_remote_post( $api_url, $remote_post_args );

 		$page_set = is_wp_error( $response ) ? $response : json_decode( $response['body'] );

 		return $page_set;
	}

	/**
	 * Get list of plugins.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $channel Example: stable
	 *     @type string $key     32 char md5 hash.
	 * }
	 * @return stdClass Example return: https://pastebin.com/KQuzvQMF
	 */
	public function get_plugins( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['get_plugins'];

		$remote_post_args = array(
			'method' => 'POST',
			'body'   => $args,
		);

		$response = wp_remote_post( $api_url, $remote_post_args );

		$plugin_list = $response instanceof WP_Error ? $response : json_decode( $response['body'] );

		return $plugin_list;
	}

	/**
	 * Get photos.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type bool   $is_generic                        Example: false
	 *     @type int    $coin_budget                       Example: 20
	 *     @type string $key                               32 char md5 hash.
	 *     @type string @image_placeholders_needing_images A json encoded array with image details
	 *                                                     Example: https://pastebin.com/TPNPb8CB
	 * }
	 * @return array Example return: https://pastebin.com/SJTLfZuM
	 */
	public function get_photos( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['bps-get-photos'];

		$response = wp_remote_post( $api_url, array(
			'body'    => $args,
			'timeout' => 60,
		) );

		$body = json_decode( $response['body'], true );

		return $body;
	}

	/**
	 * Get theme details.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type int    $theme_id           Example: 48
	 *     @type int    $page_set_id        Example: 7
	 *     @type string $theme_version_type Example: stable
	 *     @type bool   $is_preview_server  Example: false
	 *     @type int    $build_profile_id   Example: 123456
	 *     @type bool   $is_staged          Example: false
	 *     @type string $key                32 char md5 hash.
	 *     @type string $site_hash          32 char md5 hash.
	 * }
	 * @return object Example return: https://pastebin.com/9gYqTRvM
	 */
	public function get_theme_details( $args = array() ) {
		$api_url = $this->configs['asset_server'] . $this->configs['ajax_calls']['get_theme_details'];

		$remote_post_args = array (
			'method'  => 'POST',
			'body'    => $args,
			'timeout' => 20,
		);

		$response = wp_remote_post( $api_url, $remote_post_args );

		$theme_details = is_wp_error( $response ) ? $response : json_decode( $response['body'] );

		return $theme_details;
	}
}
