<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Inspirations\W3TC;

/**
 * W3TC Utility Class.
 *
 * @since SINCEVERSION
 */
class Utility {
	/**
	 * Configure W3TC after an Inspiration's deploy.
	 *
	 * @since SINCEVERSION
	 *
	 * @see Boldgrid_Inspirations_Deploy_Post::add_hooks.
	 */
	public static function deploy_post_setup() {
		if ( ! class_exists( '\W3TC\Config' ) ) {
			return;
		}

		$changes_made = false;

		$config = new \W3TC\Config();

		// If page cache is not enabled, enable it.
		if ( ! $config->get_boolean( 'pgcache.enabled' ) ) {
			$config->set( 'pgcache.enabled', true );
			$config->set( 'pgcache.engine', 'file' );
			$changes_made = true;
		}

		if ( $changes_made ) {
			$config->save();
		}
	}
}
