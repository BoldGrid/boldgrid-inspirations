<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Api
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspiration Api class.
 *
 * @since 1.2.2
 */
class Boldgrid_Inspirations_Api {
	/**
	 * The core BoldGrid Inspirations class object.
	 *
	 * @since 1.2.2
	 * @access public
	 * @var Boldgrid_Inspirations
	 */
	public $core;

	/**
	 * Class property for the API key hash.
	 *
	 * @since 1.2.2
	 * @access private
	 * @staticvar
	 * @var string
	 */
	private static $api_key_hash = '';

	/**
	 * Class property for the site hash.
	 *
	 * @since 1.2.3
	 * @access private
	 * @staticvar
	 * @var string
	 */
	private static $site_hash = '';

	/**
	 * Class property for asset server availability.
	 *
	 * @since 1.2.2
	 * @access private
	 * @var bool
	 * @staticvar
	 */
	private static $is_asset_server_available = false;

	/**
	 * Boolean that identifies whether or not the use has passed api key validation.
	 *
	 * @since 1.2.2
	 * @access private
	 * @var bool
	 */
	private $passed_key_validation = false;

	/**
	 * Last API status code.
	 *
	 * @since 1.2.3
	 * @access private
	 * @var int
	 * @staticvar
	 */
	private static $last_api_status = 0;

	/**
	 * Constructor.
	 *
	 * @since 1.2.2
	 *
	 * @param Boldgrid_Inspirations $core BoldGrid Inspirations class object.
	 */
	public function __construct( $core ) {
		// Save the Boldgrid_Inspirations object as a class property.
		$this->core = $core;

		// Set the API Key hash.
		$this->set_api_key_hash();

		// Set the site hash.
		$this->set_site_hash();
	}

	/**
	 * Get the value of the class property $is_asset_server_available.
	 *
	 * @since 1.2.2
	 *
	 * @return bool
	 */
	public static function get_is_asset_server_available() {
		return 0 !== self::$is_asset_server_available;
	}

	/**
	 * Set the value of the class property $is_asset_server_available.
	 *
	 * @since 1.2.2
	 *
	 * @param bool $is_asset_server_available Whether or not the asset server is available.
	 * @return bool
	 */
	public static function set_is_asset_server_available( $is_asset_server_available ) {
		// Validate $is_asset_server_available.
		$is_asset_server_available = (bool) $is_asset_server_available;

		// Set the property.
		self::$is_asset_server_available = $is_asset_server_available;

		// Save the WP Option.
		set_site_transient( 'boldgrid_available', (int) $is_asset_server_available, 2 * MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Check the connection to the asset server, and report results back to the AJAX caller.
	 *
	 * @since 1.2.2
	 *
	 * @see Boldgrid_Inspirations_Feedback::add_feedback()
	 * @see Boldgrid_Inspirations_Api::verify_api_key()
	 * @see Boldgrid_Inspirations_Api::get_is_asset_server_available()
	 *
	 * @uses $_POST['data'] Array of data to log.
	 */
	public function check_asset_server_callback() {
		// If you are not at least a Contributer, there's no need to be making api calls.
		if( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		// Log any reported data.
		if ( isset( $_POST['data'] ) ) {
			Boldgrid_Inspirations_Feedback::add_feedback( 'ajax_error', $_POST['data'], false );
		}

		// Verify API key, which connects to the asset server and sets the status.
		$response = $this->verify_api_key();

		// Send a JSON response and die.
		self::get_is_asset_server_available() ?
			wp_send_json_success( $response ) : wp_send_json_error( $response );
	}

	/**
	 * Accessor for $this->passed_key_validation.
	 *
	 * @since 1.2.2
	 *
	 * @return bool
	 */
	public function get_passed_key_validation() {
		return $this->passed_key_validation;
	}

	/**
	 * Setter for $this->passed_key_validation.
	 *
	 * @since 1.2.2
	 * @access private
	 *
	 * @param bool $passed Whether or not the key passed validation.
	 * @return bool
	 */
	private function set_passed_key_validation( $passed = false ) {
		$this->passed_key_validation = $passed;

		return true;
	}

	/**
	 * Accessor for self::$last_api_status.
	 *
	 * @since 1.2.3
	 *
	 * @return int
	 */
	public function get_last_api_status() {
		return self::$last_api_status;
	}

	/**
	 * Try to get our key from certain $_POST / $_GET values.
	 *
	 * If that fails, try to get our key from the configs.
	 *
	 * This set of logic was originally contained within the boldgrid_api_call() static method, but
	 * was separated and improved (check for 32 char length of key) as of 1.6.5.
	 *
	 * @since 1.6.5
	 *
	 * @return string Our api key hash.
	 */
	public static function maybe_get_key() {
		$configs = Boldgrid_Inspirations_Config::get_format_configs();

		$key_len = 32;

		switch( true ) {
			// On activation. self::hash_api_key will validate the key.
			case ! empty( $_POST['api_key'] ) && $api_key_hash = self::hash_api_key( $_POST['api_key'] ):
				break;
			// POST of the hash.
			case ! empty( $_POST['key'] ) && $key_len === strlen( $_POST['key'] ):
				$api_key_hash = sanitize_text_field( $_POST['key'] );
				break;
			// GET of the hash.
			case ! empty( $_GET['key'] ) && $key_len === strlen( $_GET['key'] ):
				$api_key_hash = sanitize_text_field( $_GET['key'] );
				break;
			// From configs.
			default:
				$api_key_hash = isset( $configs['api_key'] ) ? $configs['api_key'] : '';
		}

		return $api_key_hash;
	}

	/**
	 * API key requirement check.
	 *
	 * If required, verify the stored API key with the asset server.
	 *
	 * @since 1.2.2
	 * @see Boldgrid_Inspirations_Api::set_passed_key_validation().
	 * @see Boldgrid_Inspirations_Config::get_format_configs().
	 * @see Boldgrid_Inspirations_Api::boldgrid_api_call().
	 *
	 * @param bool $api_key_required Whether or not the API key is required to pass the check.
	 * @param bool $is_boldgrid_api_data_new Whether or not the API data was just retrieved.
	 * @return bool
	 */
	public function passes_api_check( $api_key_required = false, $is_boldgrid_api_data_new = false ) {
		// If key is not required, then mark as validated and return true.
		if ( ! $api_key_required ) {
			$this->set_passed_key_validation( true );

			return true;
		}

		// Get the BoldGrid configuration array.
		$configs = Boldgrid_Inspirations_Config::get_format_configs();

		// Check for api data transient.
		$boldgrid_api_data = get_site_transient( 'boldgrid_api_data' );

		// If there is no transient data, then retrieve it from the asset server.
		if ( empty( $boldgrid_api_data ) ) {
			$boldgrid_api_data = self::boldgrid_api_call( $configs['ajax_calls']['get_version'] );

			$is_boldgrid_api_data_new = true;
		}

		// Check if we have valid API data.
		if ( isset( $boldgrid_api_data->status ) && 200 === $boldgrid_api_data->status ) {
			// Set the last API status code.
			self::$last_api_status = $boldgrid_api_data->status;

			// If we did not have a site hash, but got one in the return, then save it.
			if ( empty( self::$site_hash ) &&
			! empty( $boldgrid_api_data->result->data->site_hash ) ) {
				// Update the WP option.
				update_option( 'boldgrid_site_hash', $boldgrid_api_data->result->data->site_hash );

				// Update the class property.
				self::$site_hash = $boldgrid_api_data->result->data->site_hash;
			}

			// If we just retrieved new data, then update reseller option.
			if ( $is_boldgrid_api_data_new || isset( $_REQUEST['force-check'] ) ) {
				$boldgrid_reseller_array = array();

				foreach ( $boldgrid_api_data->result->data as $key => $value ) {
					if ( 1 === preg_match( '/^reseller_/', $key ) ) {
						$boldgrid_reseller_array[ $key ] = $boldgrid_api_data->result->data->$key;
					}
				}

				// Set the reseller option from api data, or delete if no reseller data.
				if ( count( $boldgrid_reseller_array ) ) {
					update_option( 'boldgrid_reseller', $boldgrid_reseller_array );
				} else {
					delete_option( 'boldgrid_reseller' );
				}
			}

			// Mark as validated and return true.
			$this->set_passed_key_validation( true );

			return true;
		} else {
			// API key did not verify or received a bad response, so fail.
			$this->set_passed_key_validation( false );

			// Set the last API status code.
			self::$last_api_status = (
				isset( $boldgrid_api_data->status ) ? $boldgrid_api_data->status : 0
			);

			return false;
		}
	}

	/**
	 * Connects to the BoldGrid API and returns the response in an array.
	 *
	 * @since 1.2.2
	 * @see Boldgrid_Inspirations_Config::get_format_configs().
	 * @see Boldgrid_Inspirations_Api::hash_api_key().
	 * @see Boldgrid_Inspirations_Api::set_is_asset_server_available().
	 *
	 * @param string $api_path The API path to call.
	 * @param bool   $json_array The return format; object (default) or array.
	 * @param string $params_array An optional array of parameters to include.
	 * @param string $method The request method; GET (default) or POST.
	 * @return object|array|false
	 */
	public static function boldgrid_api_call( $api_path, $json_array = false, $params_array = array(), $method = 'GET' ) {
		// If this is a BoldGrid Inspirations plugin version check, then check if we already have recent data,
		// return it if so, and not a force-check.
		if ( '/api/plugin/check-version' === $api_path ) {
			// Get api data transient.
			$boldgrid_api_data = get_site_transient( 'boldgrid_api_data' );

			// If the API data was just retrieved (last 5 seconds) and is ok, then just return it.
			if ( ! empty( $boldgrid_api_data ) &&
			! (
				isset( $_GET['force-check'] ) && isset( $boldgrid_api_data->updated ) &&
				$boldgrid_api_data->updated < time() - 5
			) ) {
				return $boldgrid_api_data;
			}
		}

		// Get the BoldGrid configuration array.
		$configs = Boldgrid_Inspirations_Config::get_format_configs();

		$api_key_hash = self::maybe_get_key();

		// Build the GET parameters.
		if ( ! empty( $api_key_hash ) ) {
			$params_array['key'] = $api_key_hash;
		} else if ( '/api/plugin/check-version' === $api_path ) {
			// Abort if there is no Connect Key for an authenticated API call.
			return false;
		}

		if ( ! empty( self::$site_hash ) ) {
			$params_array['site_hash'] = self::$site_hash;
		}

		// If getting plugin version information, include other parameters.
		if ( 1 === preg_match( '/(check|get-plugin)-version/', $api_path ) ) {
			// Get BoldGrid settings.
			$options = get_option( 'boldgrid_settings' );

			// Include update release and theme channels.
			$params_array['channel'] = (
				! empty( $options['release_channel'] ) ?
				$options['release_channel'] : 'stable'
			);

			$params_array['theme_channel'] = (
				! empty( $options['theme_release_channel'] ) ?
				$options['theme_release_channel'] : 'stable'
			);

			// If get_plugin_data does not exist, then load it.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Get the installed plugin data.
			$plugin_data = get_plugin_data( BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php', false, false );

			$params_array['installed_core_version'] = $plugin_data['Version'];

			// Get the WordPress version.
			global $wp_version;

			$params_array['installed_wp_version'] = $wp_version;

			// Get PHP version.
			$params_array['installed_php_version'] = phpversion();

			// Include feedback opt-out setting.
			$params_array['feedback_optout'] = (
				isset( $options['boldgrid_feedback_optout'] ) ?
				$options['boldgrid_feedback_optout'] : '0'
			);

			// If allowed, then include feedback info.
			if ( empty( $params_array['feedback_optout'] ) ) {
				// Include activation/update information.
				if ( function_exists( 'wp_get_current_user' ) &&
				false !== ( $current_user = wp_get_current_user() ) ) {
					$params_array['first_login'] = get_user_meta( $current_user->ID, 'first_login',
						true
					);
					$params_array['last_login'] = get_user_meta( $current_user->ID, 'last_login',
						true
					);
					$params_array['user_login'] = $current_user->user_login;
					$params_array['user_email'] = $current_user->user_email;
				}

				// Mobile ratio.
				$mobile_ratio = get_site_option( 'boldgrid_mobile_ratio' );

				if ( ! empty( $mobile_ratio ) ) {
					$params_array['mobile_ratio'] = $mobile_ratio;
				}
			}
		}

		// Set the complete URL.
		$url = $configs['asset_server'] . $api_path;

		// Make a call to the asset server.
		if ( 'POST' === $method ) {
			$boldgrid_api_data = wp_remote_retrieve_body(
				wp_remote_post( $url,
					array(
						'body' => $params_array,
					)
				)
			);
		} else {
			// Convert the params array into a query string.
			if ( ! empty( $params_array ) ) {
				$params = http_build_query( $params_array );
			}

			// Append the params query string to the URL.
			$url .= '?' . $params;

			// Make the call.
			$boldgrid_api_data = wp_remote_retrieve_body( wp_remote_get( $url ) );
		}

		// Decode the JSON returned into an object.
		$boldgrid_api_data_object = json_decode( $boldgrid_api_data );

		// Check asset server availability.
		if ( isset( $boldgrid_api_data_object->status ) ) {
			Boldgrid_Inspirations_Api::set_is_asset_server_available( true );
		} else {
			Boldgrid_Inspirations_Api::set_is_asset_server_available( false );

			// Notify that there is a connection issue.
			add_action( 'admin_notices',
				function () {
					$notice_template_file = BOLDGRID_BASE_DIR .
					'/pages/templates/boldgrid-connection-issue.php';

					if ( ! in_array( $notice_template_file, get_included_files(), true ) ) {
						include $notice_template_file;
					}
				}
			);

			// Log.
			error_log( __METHOD__ . ': Asset server is unavailable.' );
		}

		// Decode the JSON data.
		$boldgrid_api_data = json_decode( $boldgrid_api_data, $json_array );

		// If this was a BoldGrid Inpirations plugin version check, then store only valid data.
		if ( '/api/plugin/check-version' === $api_path && isset( $boldgrid_api_data->status ) &&
		200 === $boldgrid_api_data->status ) {
			// Add the current timestamp (in seconds).
			$boldgrid_api_data->updated = time();

			// Set api data transient, expired in 8 hours.
			delete_site_transient( 'boldgrid_api_data' );
			set_site_transient( 'boldgrid_api_data', $boldgrid_api_data, 8 * HOUR_IN_SECONDS );
		}

		// Set the last API status code.
		self::$last_api_status = (
			isset( $boldgrid_api_data->status ) ? $boldgrid_api_data->status : 0
		);

		// Return the object.
		return $boldgrid_api_data;
	}

	/**
	 * Validates the API key and returns details on if it is valid as well as version.
	 *
	 * @since 1.2.2
	 * @see Boldgrid_Inspirations_Update::update_api_data().
	 * @see Boldgrid_Inspirations_Api::passes_api_check().
	 *
	 * @return object|string The BoldGrid API Data object or a message string on failure.
	 */
	public function verify_api_key() {
		// Include the update class.
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-update.php';

		// Make an API call for API data.
		$boldgrid_api_data = Boldgrid_Inspirations_Update::update_api_data();

		// Handle the response.
		if ( false !== $boldgrid_api_data ) {
			// Check response.
			if ( 200 === $boldgrid_api_data->status && 'OK' === $boldgrid_api_data->message ) {
				$boldgrid_api_data->license_status = true;

				// Process post api key verification checks.
				$this->passes_api_check( true, true );
			} elseif ( 'Unauthorized' === $boldgrid_api_data->message ) {
				$boldgrid_api_data->license_status = false;
			} else {
				// Log.
				error_log(
					__METHOD__ .
					': Error: Error when getting version information.  $boldgrid_api_data: ' .
					print_r( $boldgrid_api_data, true )
				);

				return 'Error when getting version info';
			}
		} else {
			return 'api call failed';
		}

		return $boldgrid_api_data;
	}

	/**
	 * Store the user's api_key as wp_option.
	 *
	 * This function is called via ajax.
	 *
	 * @since 1.2.2
	 * @see Boldgrid_Inspirations_Api::hash_api_key().
	 * @see Boldgrid_Inspirations_Api::verify_api_key().
	 */
	public function set_api_key_callback() {
		// Set messages.
		$messages = array(
			'success' => esc_html__(
				'Your api key has been saved successfully.'
				, 'boldgrid-inspirations'
			),
			'invalid_key' => sprintf(
				// translators: A line break / br tag.
				esc_html__(
					'Your API key appears to be invalid!%1$sPlease try to enter your BoldGrid Connect Key again.'
					, 'boldgrid-inspirations'
				),
				'<br />'
			),
			'error_saving_key' => sprintf(
			// translators: A line break / br tag.
				esc_html__(
					'There was an error saving your key.%1$sPlease try entering your BoldGrid Connect Key again.'
					, 'boldgrid-inspirations'
				),
				'<br />'
			),
			'nonce_failed' => esc_html__(
				'Security violation (invalid nonce).'
				, 'boldgrid-inspirations'
			),
			'insufficient_permissions' => __( 'BoldGrid API keys can only be saved by Admins. Please contact your WordPress Admin for assistance with saving your key.', 'boldgrid-inspirations' ),
		);

		// If the current user cannot manage options, they do not have permission to set the api key.
		if( ! current_user_can( 'manage_options' ) ) {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'insufficient_permissions',
					'message' => $messages['insufficient_permissions'],
				)
			);

			wp_die();
		}

		// Verify nonce.
		if ( ! isset( $_POST['set_key_auth'] ) ||
		1 !== check_ajax_referer( 'boldgrid_set_key', 'set_key_auth', false ) ) {
			echo $messages['nonce_failed'];

			wp_die();
		}

		// Check input API key.
		if ( empty( $_POST['api_key'] ) ) {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'invalid_key',
					'message' => $messages['invalid_key'],
				)
			);

			wp_die();
		}

		$api_key_hash = self::hash_api_key( $_POST['api_key'] );

		if ( empty( $api_key_hash ) ) {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'invalid_key',
					'message' => $messages['invalid_key'],
				)
			);

			wp_die();
		}

		// Delete the boldgrid_api_data transient.
		delete_site_transient( 'boldgrid_api_data' );

		// Verify the key.
		$boldgrid_api_data = $this->verify_api_key();

		// Interpret result.
		if ( isset( $boldgrid_api_data->message ) && 'OK' === $boldgrid_api_data->message ) {
			// Success.
			echo wp_json_encode(
				array(
					'success' => true,
					'message' => $messages['success'],
				)
			);

			// Update the API key option.
			update_option( 'boldgrid_api_key', $api_key_hash );
		} elseif ( isset( $boldgrid_api_data->message ) &&
		'Unauthorized' === $boldgrid_api_data->message ) {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'invalid_key',
					'message' => $messages['invalid_key'],
				)
			);
		} elseif ( 'api call failed' === $boldgrid_api_data ) {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'invalid_key',
					'message' => $messages['invalid_key'],
				)
			);
		} elseif ( ! is_object( $boldgrid_api_data ) ) {
			// Log.
			error_log(
				__METHOD__ . ': Error: $boldgrid_api_data is not an object.  $boldgrid_api_data: ' .
				print_r( $boldgrid_api_data, true )
			);

			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'error_saving_key',
					'message' => $messages['error_saving_key'],
				)
			);
		} else {
			// Failure.
			echo wp_json_encode(
				array(
					'success' => false,
					'error' => 'error_saving_key',
					'message' => $messages['error_saving_key'],
				)
			);
		}

		wp_die();
	}

	/**
	 * Hash API Key.
	 *
	 * @param string $api_key A BoldGrid Connect Key to be hashed.
	 *
	 * @return string|bool MD5 hash representation of a BoldGrid Connect Key, or FALSE on error.
	 */
	public static function hash_api_key( $api_key = null ) {
		// Trim the input.
		$api_key = trim( $api_key );

		// Convert to lowercase.
		$api_key = strtolower( $api_key );

		// Remove dashes/hyphens from the input API Key.
		$api_key = preg_replace( '#-#', '', $api_key );

		// Check for the correct number of chars (32).
		if ( 32 !== strlen( $api_key ) ) {
			return false;
		}

		// Add dashes to the API Key.
		$api_key = rtrim( chunk_split( $api_key, 8, '-' ), '-' );

		// Hash the API Key.
		$api_key_hash = md5( $api_key );

		return $api_key_hash;
	}

	/**
	 * Set the BoldGrid Connect Key hash.
	 *
	 * @since 1.2.2
	 * @static
	 * @see Boldgrid_Inspirations_Config::get_format_configs().
	 */
	public static function set_api_key_hash() {
		// Get the BoldGrid configuration array.
		$configs = Boldgrid_Inspirations_Config::get_format_configs();

		// Look in the config for the api_key, which includes the WP option.
		self::$api_key_hash = ( isset( $configs['api_key'] ) ? $configs['api_key'] : null );
		// If it's not there, check $_REQUEST['key'].
		self::$api_key_hash = (
			( empty( self::$api_key_hash ) && isset( $_REQUEST['key'] ) ) ?
			sanitize_text_field( $_REQUEST['key'] ) : self::$api_key_hash
		);
	}

	/**
	 * Get the BoldGrid Connect Key hash.
	 *
	 * @since 1.2.2
	 * @static
	 *
	 * @return string|false Hash representation of the BoldGrid Connect Key, or FALSE on error.
	 */
	public static function get_api_key_hash() {
		// If an API key is stored, then return it.
		if ( ! empty( self::$api_key_hash ) ) {
			return self::$api_key_hash;
		}

		// Attempt to set the hash.
		self::set_api_key_hash();

		// If an API key is now stored, then return it.
		if ( ! empty( self::$api_key_hash ) ) {
			return self::$api_key_hash;
		} else {
			return false;
		}
	}

	/**
	 * Set the BoldGrid site hash.
	 *
	 * @since 1.2.3
	 * @static
	 * @see Boldgrid_Inspirations_Config::get_format_configs().
	 *
	 * @return bool
	 */
	public static function set_site_hash() {
		// If there is already a site hash stored, then abort.
		if ( ! empty( self::$site_hash ) ) {
			return true;
		}

		// Get the BoldGrid configuration array.
		$configs = Boldgrid_Inspirations_Config::get_format_configs();

		// Look in the config for the site_hash, which includes the WP option.
		if ( ! empty( $configs['site_hash'] ) ) {
			self::$site_hash = $configs['site_hash'];

			return true;
		}

		// Could not locate a site hash, so fail.
		return false;
	}

	/**
	 * Get the BoldGrid site hash.
	 *
	 * @since 1.2.3
	 * @static
	 *
	 * @return string The BoldGrid site hash.
	 */
	public static function get_site_hash() {
		// If a site hash is stored, then return it.
		if ( ! empty( self::$site_hash ) ) {
			return self::$site_hash;
		}

		// Attempt to set the site hash.
		self::set_site_hash();

		// Return the resulting site hash string.
		return self::$site_hash;
	}
}
