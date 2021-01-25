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
 * @since 2.5.0
 */
class Utility {
	/**
	 * Configure W3TC after an Inspiration's deploy.
	 *
	 * @since 2.5.0
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

	/**
	 * Get an instace of \Boldgrid\Library\Library\Plugin for this plugin.
	 *
	 * @since 2.5.0
	 *
	 * @return \Boldgrid\Library\Library\Plugin
	 */
	public static function get_plugin() {
		return \Boldgrid\Library\Library\Plugin\Factory::create( 'w3-total-cache' );
	}

	/**
	 * Whether or not W3TC was installed during a deployment.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public static function is_deploy() {
		$installed = new \Boldgrid_Inspirations_Installed();
		$option    = $installed->get_install_option( 'install_cache' );

		return ! empty( $option );
	}
}
