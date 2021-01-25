<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Inspirations\Sprout;

/**
 * Sprout Utility Class.
 *
 * @since 2.5.0
 */
class Utility {
	/**
	 * Prevent Sprout from redirecting after being installed by Inspirations.
	 *
	 * Sprout's normal hook at 10 adds the option - which will trigger the redirect. We hook in at
	 * priority 15 and delete the option.
	 *
	 * @since 2.5.0
	 */
	public static function cancel_activation_redirection() {
		delete_option( 'si_do_activation_redirect' );
	}

	/**
	 * Get an instace of \Boldgrid\Library\Library\Plugin for this plugin.
	 *
	 * @since 2.5.0
	 *
	 * @return \Boldgrid\Library\Library\Plugin
	 */
	public static function get_plugin() {
		return \Boldgrid\Library\Library\Plugin\Factory::create( 'sprout-invoices' );
	}

	/**
	 * Whether or not Sprout was installed during a deployment.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public static function is_deploy() {
		$installed = new \Boldgrid_Inspirations_Installed();
		$option    = $installed->get_install_option( 'install_invoice' );

		return ! empty( $option );
	}
}
