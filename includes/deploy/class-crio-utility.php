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
 * Crio Utility class.
 *
 * @since sinceversion
 */
class Crio_Utility {
	/**
	 * Setup our template locations taxonomy.
	 *
	 * This code is copied directly from Crio. In the future, it would help if these were static methods
	 * within Crio that we could call directly.
	 * @see /src/includes/page-headers/templates/class-crio-premium-page-headers-templates.php
	 *
	 * @since SINCEVERSION
	 */
	public static function register_template_locations() {
		$taxonomy = 'template_locations';

		// Labels part for the GUI
		$labels = array(
			'name'              => __( 'Template Location', 'bgtfw' ),
			'singular_name'     => _x( 'Template Location', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Template Locations', 'bgtfw' ),
			'all_items'         => null,
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Edit Template Location', 'bgtfw' ),
			'update_item'       => __( 'Update Template Location', 'bgtfw' ),
			'add_new_item'      => null,
			'new_item_name'     => null,
			'menu_name'         => __( 'Template Locations', 'bgtfw' ),
			'most_used'         => null,
		);

		$locations = array(
			'header',
			'sticky-header',
			'footer',
		);

		// Register our taxonomy. It may already exist if Crio is already installed.
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy(
				$taxonomy,
				'crio_page_header',
				array(
					'hierarchical'          => true,
					'labels'                => $labels,
					'public'                => true,
					'show_ui'               => true,
					'show_in_menu'          => false,
					'show_in_rest'          => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var'             => true,
					'rewrite'               => array( 'slug' => 'template_location' ),
					'meta_box_cb'           => false,
					'capabilities'          => array(
						'manage_terms' => '',
						'edit_terms'   => '',
						'delete_terms' => '',
						'assign_terms' => 'edit_posts',
					),
				)
			);
		}

		// Add our terms.
		foreach ( $locations as $slug ) {
			$name = ucwords( str_replace( '-', ' ', $slug ) );
			// Crio may already be installed, so check for existing terms.
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
			}
		}
	}

	/**
	 * Set Custom Templates.
	 *
	 * This is run after all pages / posts are added to the site.
	 * This is used to adjust the theme_mods for the custom templates and ensure that
	 * they match the new post ids. This is necessary because the theme_mods are set
	 * using the post id of the template before being submitted by the author plugin, which
	 * is different thant the post id when created via inspirations.
	 *
	 * @param array $cph_original_ids Original CPH Id Numbers.
	 */
	public static function set_custom_templates( $cph_original_ids ) {
		$template_locations = array( 'page_headers', 'page_footers', 'sticky_page_headers' );
		$page_post_types    = array( 'global', 'pages', 'posts', 'home', 'blog', 'search' );

		$theme_mods = array();

		/*
		 * The theme mods that store the various custom template settings are a combination of
		 * the template location and the post and page type that the template is for. For example,
		 * 'bgtfw_page_header_home_template'. Rather than list all of the combinations, we loop
		 * through the two arrays of combination possibilities and set the theme mods.
		 */
		foreach ( $template_locations as $location ) {
			foreach ( $page_post_types as $page_post_type ) {
				$theme_mod_name = 'bgtfw_' . $location . '_' . $page_post_type . '_template';
				$template_id = get_theme_mod( $theme_mod_name );
				if ( isset( $cph_original_ids[ $template_id ] ) ) {
					set_theme_mod( $theme_mod_name, $cph_original_ids[ $template_id ] );
				}
				$theme_mods[ $theme_mod_name ] = get_theme_mod( $theme_mod_name );
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
			preg_match( '/\[boldgrid_component type="wp_boldgrid_component_menu".*\]/s', $content, $menus );
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
}
