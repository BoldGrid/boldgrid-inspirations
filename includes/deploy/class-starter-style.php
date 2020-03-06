<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Inspirations\Deploy;

/**
 * Starter Styles class.
 *
 * @since SINCEVERSION
 */
class Starter_Style {
	/**
	 * Our deploy class.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy
	 */
	private $deploy;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param Boldgrid_Inspirations_Deploy $deploy
	 */
	public function __construct( \Boldgrid_Inspirations_Deploy $deploy ) {
		$this->deploy = $deploy;
	}

	/**
	 * Deploy starter styles.
	 *
	 * @since SINCEVERSION
	 */
	public function deploy() {
		if ( ! $this->deploy->deploy_theme->is_crio() ) {
			return;
		}

		$styles = $this->get();

		foreach ( $styles as $key => $value ) {
			$skip = [ '0', 'nav_menu_locations' ];

			if ( in_array( $key, $skip ) ) {
				continue;
			}

			set_theme_mod( $key, $value );
		}
	}

	/**
	 * Get our starter style.
	 *
	 * @since SINCEVERSION
	 *
	 * @global WP_Filesystem $wp_filesystem
	 *
	 * @return array
	 */
	public function get() {
		global $wp_filesystem;

		$style = [];

		$path = $this->get_path();

		if ( $wp_filesystem->exists( $path ) ) {
			$style = include $path;
			$style = json_decode( $style, true );
		}

		return $style;
	}

	/**
	 * Get the path to the starter styles.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string
	 */
	public function get_path() {
		$name  = sanitize_key( $this->deploy->deploy_theme->get_attribute( 'Name' ) );
		$theme = $this->deploy->deploy_theme->get_theme();
		$path  = $theme->get_theme_root() . '/' . $this->deploy->deploy_theme->get_folder_name() . '/inc/starter-styles/' . $name . '.php';

		return $path;
	}
}