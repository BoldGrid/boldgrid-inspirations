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
 * Social Menu class.
 *
 * @since SINCEVERSION
 */
class Social_Menu {
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
	 * Create a unique menu for our social links.
	 *
	 * @since SINCEVERSION
	 *
	 * @param  string $name Menu name.
	 * @return int          Id of menu created.
	 */
	public function create_menu( $name ) {
		$original_name = $name;
		$count         = 2;

		/*
		 * Get a unique name for our menu.
		 *
		 * Start off by trying to create $name. If it doesn't exist, try $name-1, $name-2, etc.
		 */
		$menu_object = wp_get_nav_menu_object( $name );
		while( ! empty( $menu_object ) ) {
			$name        = $original_name . '-' . $count;
			$menu_object = wp_get_nav_menu_object( $name );
			$count++;
		}

		return wp_create_nav_menu( $name );
	}

	/**
	 * Create a social media menu based off of survey data and assign to social nav menu location.
	 *
	 * This method should only be ran for Crio themes.
	 *
	 * This method has been introduced for Crio. Prior, the social media menu was created by filtering
	 * the bgtfw configs. As of Crio, that code no longer exists, and so we must actually create a menu.
	 *
	 * @since SINCEVERSION
	 */
	public function deploy() {
		// Make sure we have social media data.
		$socials = $this->get_social();
		if ( empty( $socials ) ) {
			return;
		}

		// Create a menu.
		$menu_id = $this->create_menu( 'social' );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}

		// Add all of our menu items.
		foreach ( $socials as $network => $url ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				[
					'menu-item-title'  => $network,
					'menu-item-url'    => $url,
					'menu-item-status' => 'publish',
				]
			);
		}

		// Save our new menu to the theme's nav_menu_locations.
		$locations           = get_theme_mod( 'nav_menu_locations' );
		$locations['social'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Get our social media data.
	 *
	 * Data is gathered from the user via the Inspirations process.
	 *
	 * @since SINCEVERSION
	 *
	 * @return array Example: https://pastebin.com/ANt5G8uq
	 */
	public function get_social() {
		$social = $this->deploy->survey->get_social();

		// Preview sites should show a social media menu. If preview server, set dummy data.
		if ( empty( $social ) && $this->deploy->is_preview_server ) {
			$social = [
				'facebook' => 'https://facebook.com',
				'twitter'  => 'https://twitter.com',
			];
		}

		return $social;
	}
}