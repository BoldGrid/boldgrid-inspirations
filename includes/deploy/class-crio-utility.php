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
}
