<?php
/**
 * File: class-boldgrid-inspirations-wpcli.php
 *
 * @link https://www.boldgrid.com
 * @since 1.7.0
 *
 * @package    Boldgrid_Inspirations
 * @subpackage Boldgrid_Inspirations/includes
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'bginsp', 'Boldgrid_Inspirations_Wpcli' );

/**
 * Perform actions for BoldGrid Inspirations.
 *
 * @since 1.7.0
 */
class Boldgrid_Inspirations_Wpcli {
	/**
	 * Make api calls to the BoldGrid API server.
	 *
	 * ## SUBCOMMANDS
	 *
	 * get-plugins Get a list of sitewide plugins.
	 *
	 * ## SUBCOMMAND: get-plugins
	 *
	 * Get the list of sitewide plugins that will be installed during a deployment.
	 *
	 * During an Inspirations deployment, there are sitewide plugins and there are per page plugins.
	 * A sitewide plugin is a plugin that will be installed during each Inspirations deployment,
	 * regardless of the pages installed. A per page plugin is a plugin that is installed in support
	 * of a certain page. For example, if you have a "Contact Us" page, it will need a forms plugin.
	 *
	 * # OPTIONS
	 *
	 * [--key=<api key hash>]
     * Test using a different api key hash.
     *
     * [--channel=<channel>]
     * Test different channels, such as 'stable', 'edge', and 'candidate'.
     *
     * # EXAMPLES
     *
     * wp bginsp api get-plugins
	 * wp bginsp api get-plugins --key=<API KEY HASH>
	 * wp bginsp api get-plugins --channel=edge
	 *
	 * @since 1.7.0
	 */
	public function api( array $args = [], array $assoc_args = [] ) {
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-wpcli-api.php';

		$api = new Boldgrid_Inspirations_Wpcli_Api( $args, $assoc_args );

		$api_command = ! empty( $args[0] ) ? $args[0] : false;

		switch( $api_command ) {
			case 'get-plugins':
				$api->get_plugins();
				break;
			default:
				WP_CLI::error( 'Missing api command.' );
		}
	}
}