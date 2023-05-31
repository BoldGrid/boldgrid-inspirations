<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Deploy_Cta
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * Class: Boldgrid_Inspirations_Deploy_Cta.
 *
 * Responsible for installing pages during deployment.
 *
 * @since 1.0.7
 * @package Boldgrid_Inspirations_Deploy_Pages.
 * @subpackage Boldgrid_Inspirations_Deploy_Pages.
 * @author BoldGrid <support@boldgrid.com>.
 *
 * @link https://boldgrid.com.
 */
class Boldgrid_Inspirations_Deploy_Cta {

	/**
	 * Does content have CTA widget displayed.
	 *
	 * @access public
	 *
	 * @since 1.3.5
	 */
	public $has_cta = false;

	/**
	 * Is BSTW going to be enabled.  Default is true.
	 *
	 * @access public
	 *
	 * @since 1.3.5
	 */
	public $bstw_enabled = true;

	/**
	 * Util.
	 *
	 * @since 2.7.6
	 *
	 * @var Boldgrid_Inspirations_Utility
	 */
	public $util;

	/**
	 * Initialize Class.
	 *
	 * @since 1.3.5
	 *
	 * @access public
	 */
	public function __construct() {
		$this->util = new Boldgrid_Inspirations_Utility();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.3.5
	 *
	 * @access public
	 */
	public function add_hooks() {
		// add_filter( 'boldgrid_deployment_pre_insert_post', array( $this, 'has_cta' ) );
		// add_action( 'boldgrid_deployment_deploy_theme_pre_return', array( $this, 'set_theme_mod' ) );
	}

	/**
	 * Check if content has a Call To Action Widget.
	 *
	 * This runs on the boldgrid_deployement_pre_insert_post filter,
	 * and doesn't modify the actual post content for this.
	 *
	 * @since 1.3.5
	 *
	 * @access public
	 *
	 * @param Array $post Contains the post content.
	 *
	 * @return Array $post Contains the post content.
	 */
	public function has_cta( $post ) {
		$dom = new DOMDocument;
		$dom->loadHTML( $post['post_content'] );
		$this->has_cta = $this->util->attribute_exists( $dom, 'data-cta', 'homepage' );

		return $post;
	}

	/**
	 * Gets the theme mods for theme being installed.
	 *
	 * @since 1.3.6
	 *
	 * @access public
	 *
	 * @return Array The collection of theme mods for a theme.
	 */
	public function get_theme_mods( $theme_folder_name ) {
		return get_option( 'theme_mods_' . $theme_folder_name, array() );
	}

	/**
	 * Set the bstw_enabled option for the theme being installed.
	 *
	 * @since 1.3.5
	 *
	 * @param String $theme_folder_name the name of theme being installed.
	 */
	public function set_theme_mod( $theme_folder_name ) {
		$mods = $this->get_theme_mods( $theme_folder_name );

		// Set the value of bstw_enabled theme mod.
		$mods['bstw_enabled'] = $this->bstw_enabled;

		update_option( 'theme_mods_' . $theme_folder_name, $mods );
	}
}
