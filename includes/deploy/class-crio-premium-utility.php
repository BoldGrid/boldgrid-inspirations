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
	 * Post Meta Keys.
	 *
	 * These post meta keys are to be imported when setting
	 * post meta.
	 *
	 * @var array post meta keys.
	 */
	public static $post_meta_keys = array(
		'crio-premium-page-header-override',
		'crio-premium-page-header-select',
		'crio-premium-page-header-background',
		'crio-premium-page-header-featured-image-background',
		'crio-premium-template-has-page-title',
	);

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
				$template_local_id  = apply_filters( 'get_local_id_from_author_id', $template_author_id );

				if ( false !== $template_author_id ) {
					set_theme_mod( $theme_mod_name, $template_local_id );
				}
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
			preg_match( '/\[boldgrid_component type="wp_boldgrid_component_menu".*\]/', $content, $menus );
			foreach ( $menus as $menu ) {
				$adjusted_menu = self::adjust_menu_id( $menu );
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
	 * @param string $menu The menu to adjust.
	 *
	 * @return string The adjusted menu shortcode.
	 */
	public static function adjust_menu_id( $menu ) {
		$menu_locations = get_theme_mod( 'nav_menu_locations' );

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
			$strpos = strpos(
				$menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location_id]'],
				$menu_location
			);

			if ( false !== $strpos ) {
				$menu_attrs['widget-boldgrid_component_menu[][bgc_menu]'] = $menu_id;
				$menu_adjusted                                            = true;
			}
		}

		/**
		 * If the menu was not adjusted in the previous step, we check
		 * to see if the menu's location name matches the menu's name.
		 */
		if ( false === $menu_adjusted ) {
			foreach ( wp_get_nav_menus() as $menu ) {
				if ( $menu->name === $menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location]'] ) {
					$menu_attrs['widget-boldgrid_component_menu[][bgc_menu]'] = $menu->term_id;
				}
			}
		}

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


	/**
	 * Set Post Meta
	 *
	 * This is run after all pages / posts are added to the site. This is used to set
	 * the post meta header overrides for the pages / posts.
	 */
	public static function set_post_meta( $post_id, $post_meta ) {
		foreach ( $post_meta as $key => $value ) {
			if ( false !== array_search( $key, self::$post_meta_keys, true ) ) {
				$filtered_value = apply_filters( 'get_local_id_from_author_id', $value[0] );
				update_post_meta( $post_id, $key, $filtered_value );
			}
		}
	}
}
