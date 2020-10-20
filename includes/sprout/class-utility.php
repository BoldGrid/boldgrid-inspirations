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
 * @since SINCEVERSION
 */
class Utility {
	/**
	 * Prevent Sprout from redirecting after being installed by Inspirations.
	 *
	 * Sprout's normal hook at 10 adds the option - which will trigger the redirect. We hook in at
	 * priority 15 and delete the option.
	 *
	 * @since SINCEVERSION
	 */
	public static function cancel_activation_redirection() {
		delete_option( 'si_do_activation_redirect' );
	}
}
