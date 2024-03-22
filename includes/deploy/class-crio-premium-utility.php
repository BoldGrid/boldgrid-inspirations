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
 * Crio Premium Utility class.
 *
 * This handles the deployment of Crio Premium specific features.
 *
 * @since sinceversion
 */
class Crio_Premium_Utility {
	/**
	 * Set Custom Templates.
	 *
	 * This is run after all pages / posts are added to the site.
	 *
	 * The purpose of this is to make sure that the custom templates assigned in the
	 * customizer by the auther are set correctly with the new post ids that have been
	 * assigned by inspirations.
	 *
	 * This adjusts the theme_mods for the custom templates and ensure that
	 * they match the new post ids. This is necessary because the theme_mods are set
	 * using the post id of the template by the author before being submitted
	 * by the author plugin, which is different thant the post id
	 * when created via inspirations.
	 *
	 * @param array $cph_original_ids Original CPH Id Numbers.
	 */
	public static function set_custom_templates() {
		$template_locations = array( 'page_headers', 'page_footers', 'sticky_page_headers' );
		$page_post_types    = array( 'global', 'pages', 'posts', 'home', 'blog', 'search' );

		/*
		 * The theme mods that store the various custom template settings are a combination of
		 * the template location and the post and page type that the template is for. For example,
		 * 'bgtfw_page_header_home_template'. Rather than list all of the combinations, we loop
		 * through the two arrays of combination possibilities and set the theme mods.
		 */
		foreach ( $template_locations as $location ) {
			foreach ( $page_post_types as $page_post_type ) {
				$theme_mod_name     = 'bgtfw_' . $location . '_' . $page_post_type . '_template';
				$template_author_id = get_theme_mod( $theme_mod_name );
				$template_local_id  = \Boldgrid\Inspirations\Deploy\Author_Ids::get_from_author( $template_author_id );

				set_theme_mod( $theme_mod_name, strval( $template_local_id ) );
			}
		}
	}

	/**
	 * Set Template Menus
	 *
	 * This is run after all pages / posts are added to the site. This is used to adjust the
	 * boldgrid shortcode for the menus, and replace the menu id, with the ID of the appropriate
	 * menu. This requires that the menu's location name contain the name of the location of the menu
	 * in the theme. For example, if you are wanting to include the 'main' menu, you must include the
	 * word 'main' in the template's menu location name.
	 */
	public static function set_template_menus() {
		$boldgrid_survey = get_option( 'boldgrid_survey' );
		$dnd_social_menu = isset( $boldgrid_survey['social'] ) &&
			isset( $boldgrid_survey['social']['do-not-display'] ) &&
			true === $boldgrid_survey['social']['do-not-display'];

		$templates = get_posts(
			array(
				'post_type' => 'crio_page_header',
			)
		);

		foreach ( $templates as $template ) {
			$content = $template->post_content;

			if ( empty( $content ) ) {
				continue;
			}
			$menus = array();
			preg_match_all( '/\[boldgrid_component type="wp_boldgrid_component_menu".*\]/', $content, $menus );

			foreach ( $menus[0] as $menu ) {
				if ( $dnd_social_menu && false !== strpos( $menu, 'social' ) ) {
					$content = str_replace( $menu, '', $content );
					continue;
				}
				$adjusted_menu = self::adjust_menu_id( $menu, $template->ID, $dnd_social_menu );
				$content       = str_replace( $menu, $adjusted_menu, $content );
			}

			wp_update_post(
				array(
					'ID'           => $template->ID,
					'post_content' => $content,
				)
			);
		}
	}

	/**
	 * Adjust the menu ID.
	 *
	 * This performs the actual string replacement of the menu id.
	 *
	 * @param string $menu        The menu to adjust.
	 * @param string $template_id The post id of this template.
	 *
	 * @return string The adjusted menu shortcode.
	 */
	public static function adjust_menu_id( $menu, $template_id ) {
		$crio_premium_menu_locations = get_option( 'crio_premium_menu_locations', array() );
		$menu_locations = get_theme_mod( 'nav_menu_locations', array() );

		/**
		 * The menu attributes are url encoded, and contain all kinds of things that get
		 * in the way of parsing them into an array. This is a simple way to get the menu
		 * attributes into an array.
		 */
		$menu_attrs = json_decode(
			trim(
				urldecode(
					str_replace(
						array( ']', 'opts=' ),
						'',
						shortcode_parse_atts( trim( $menu ) )['1'] // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					)
				),
				'"'
			),
			true
		);

		/** There are two ways that a menu from the custom template can be matched
		 * against the new menus created by inspirations. The first is by referencing
		 * the 'nav_menu_locations' theme mod. This is the primary way that it should
		 * be matched. However, in the case of a custom template using the new v2
		 * nested menus, the menu is not set to a location. Therefore, we need to
		 * reference the menus listed in wp_nav_menu() and match them against them.
		 */
		$menu_adjusted = false;

		/**
		 * This is where we check to see if the menu location name is contained
		 * within the shortcode's attributes. If it is, we change the menu id to
		 * the correct id number.
		 */
		foreach ( $menu_locations as $menu_location => $menu_id ) {
			$menu_location_id = $menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location_id]'];

			$strpos = strpos(
				$menu_location_id,
				$menu_location
			);

			if ( false !== $strpos ) {
				$menu_attrs['widget-boldgrid_component_menu[][bgc_menu]'] = $menu_id;
				$menu_adjusted                                            = true;

				$crio_premium_menu_locations[ $menu_location_id ] = array(
					'id'          => $menu_location_id,
					'name'        => ucwords( str_replace( '-', ' ', explode( '_', $menu_location_id )[0] ) ),
					'template_id' => $template_id,
				);

			}
		}

		/**
		 * If the menu was not adjusted in the previous step, we check
		 * to see if the menu's location name matches the menu's name.
		 */
		if ( false === $menu_adjusted ) {
			foreach ( wp_get_nav_menus() as $menu ) {
				$location_name    = strtolower( $menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location]'] );
				$menu_location_id = $menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location_id]'];

				if (
					strtolower( $menu->name ) === $location_name ||
					false !== strpos( $location_name, strtolower( $menu->name ) ) ||
					false !== strpos( strtolower( $menu->name ), $location_name )
				) {
					$menu_attrs['widget-boldgrid_component_menu[][bgc_menu]'] = $menu->term_id;

					$crio_premium_menu_locations[ $menu_location_id ] = array(
						'id'          => $menu_location_id,
						'name'        => ucwords( str_replace( '-', ' ', explode( '_', $menu_location_id )[0] ) ),
						'template_id' => $template_id,
					);
				}
			}
		}

		update_option( 'crio_premium_menu_locations', $crio_premium_menu_locations );

		/**
		 * Here, this is all re-assembled into a shortcode string, to be returned
		 * and replaced in the post's content.
		 */
		$opts = '{';

		foreach ( $menu_attrs as $key => $value ) {
			if ( 'widget-boldgrid_component_menu[][bgc_menu]' === $key ) {
				$opts .= '"' . $key . '":' . $value . ',';
			} else {
				$opts .= '"' . $key . '":"' . $value . '",';
			}
		}

		$opts = rtrim( $opts, ',' );

		$opts = rawurlencode( $opts . '}' );

		return '[boldgrid_component type="wp_boldgrid_component_menu" opts="' . $opts . '"]';
	}
}
