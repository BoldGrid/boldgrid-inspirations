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
 * Deploy Menus class.
 *
 * @since SINCEVERSION
 */
class Menus {
	/**
	 * An array of all the pages Inspirations is deploying.
	 *
	 * @since SINCEVERSION
	 * @var array
	 * @access private
	 */
	private $all_pages = array();

	/**
	 * Whether or not this pageset has a _bginsp_menus page.
	 *
	 * @since SINCEVERSION
	 * @var bool
	 * @access private
	 */
	private $has_menu = false;

	/**
	 * If our pageset has a _bginsp_menus page, this is the page (as passed in via the API).
	 *
	 * @since SINCEVERSION
	 * @var object
	 * @access private
	 */
	private $page;

	/**
	 * The id of the menu in the primary / main menu location.
	 *
	 * This is a relic from the v1 menu system, and doesn't server any purpose in this class.
	 *
	 * This is set at the end of self::deploy().
	 *
	 * @since SINCEVERSION
	 * @var int
	 * @access private
	 */
	private $primary_menu_id;

	/**
	 * Construct.
	 *
	 * @since SINCEVERSION
	 *
	 * @param array $all_pages An array of pages Inspirations is deploying.
	 */
	public function __construct( $all_pages ) {
		$this->all_pages = $all_pages;

		foreach ( $all_pages as $page ) {
			if ( '_bginsp_menus' === $page->post_type ) {
				$this->has_menu = true;
				$this->page     = $page;

				break;
			}
		}
	}

	/**
	 * Deploy our custom menus.
	 */
	public function deploy() {
		$item_ids = array();
		$menu_ids = array();
		$configs  = $this->get_configs();

		if ( empty( $configs ) || empty( $configs['menus']) ) {
			return;
		}

		foreach ( $configs['menus'] as $term_id => $menu_data ) {
			// Create the menu.
			$menu_exists = wp_get_nav_menu_object( $menu_data['name'] );

			/*
			 * If the nav menu exists already, skip it.
			 *
			 * Nav menus may already exist because (1) they're created through the social menu
			 * and (2) they're included in the new v2 menus.
			 *
			 * @todo v2 menus should be updated to exclude menus that will be created through
			 * the social options.
			 */
			if ( $menu_exists ) {
				continue;
			}

			$menu_id = wp_create_nav_menu( $menu_data['name'] );

			// Keep track of the fact we created this menu.
			$menu_ids[ $term_id ] = $menu_id;

			foreach ( $menu_data['items'] as $item ) {
				$db_id = wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-object-id' => \Boldgrid_Inspirations_Utility::get_local_id( $item['object_id'] ),
					/*
					 * We can only successfully set the parent id IF we're looping through menu items
					 * in the proper order. IE parent item has already been created.
					 */
					'menu-item-parent-id' => empty( $item['menu_item_parent'] ) ? 0 : $item_ids[ $item['menu_item_parent'] ],
					'menu-item-object'    => $item['object'],
					'menu-item-type'      => $item['type'],
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $item['menu_order'],
					'menu-item-url'       => empty( $item['url'] ) ? '' : $item['url'],
					'menu-item-title'     => $item['title'],
					'menu-item-classes'   => implode( ' ', $item['classes'] ),
				) );

				// Keep track of the menu item's ID, both locally and remotely.
				$item_ids[ $item['ID'] ] = $db_id;
			}
		}

		// Assign our new nav menus
		$locations = array();
		foreach ( $configs['locations'] as $slug => $menu_id ) {
			// Whether or not we created this menu.
			if ( ! empty( $menu_ids[ $menu_id ] ) ) {
				$locations[ $slug ] = $menu_ids[ $menu_id ];
			}
		}
		set_theme_mod( 'nav_menu_locations', $locations );

		// Set our primary menu id (a v1 menus relic). See property description.
		foreach ( array( 'primary', 'main' ) as $primary ) {
			if ( ! empty( $locations[ $primary ] ) ) {
				$this->primary_menu_id = $locations[ $primary ];
				break;
			}
		}
	}

	/**
	 * Get the configs to build our menus.
	 *
	 * @since SINCEVERSION
	 *
	 * @return array
	 */
	private function get_configs() {
		$configs = array();

		$code = trim( $this->page->code );

		// Strip tags, just in case <p> tags have automatically been added.
		$code = wp_strip_all_tags( $code );

		$configs = json_decode( $code, true );

		return $configs;
	}

	/**
	 * Get our primary menu id.
	 *
	 * @since SINCEVERSION
	 *
	 * @return int
	 */
	public function get_primary_menu_id() {
		return $this->primary_menu_id;
	}

	/**
	 * Whether or not this pageset has a _bginsp_menus page.
	 *
	 * @since SINCEVERSION
	 *
	 * @return bool
	 */
	public function has_menu() {
		return $this->has_menu;
	}
}
