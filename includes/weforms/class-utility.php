<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Inspirations\Weforms;

/**
 * weForms Untility class.
 *
 * @since 2.5.0
 */
class Utility {
	/**
	 * Get all forms.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public static function get_all_forms() {
		$forms_manager = new \WeForms_Form_Manager();

		$all_forms = $forms_manager->all();

		return empty( $all_forms['forms'] ) ? array() : $all_forms['forms'];
	}

	/**
	 * Get a form by title.
	 *
	 * Returns the first form that matches.
	 *
	 * @since 2.5.0
	 *
	 * @param string $title
	 * @return mixed WeForms_Form Object on success, false when not found.
	 */
	public static function get_by_title( $title ) {
		$all_forms = self::get_all_forms();

		foreach ( $all_forms as $form ) {
			if ( $title === $form->data->post_title ) {
				return $form;
			}
		}

		return false;
	}

	/**
	 * Import a json file.
	 *
	 * @since 2.5.0
	 *
	 * @param string $filepath Path to json file.
	 * @return bool Status of import.
	 */
	public static function import_json_file( $filepath ) {
		if ( ! class_exists( 'WeForms_Form' ) ) {
			require_once WP_PLUGIN_DIR . '/weforms/includes/class-form.php';
		}

		if ( ! class_exists( 'WeForms_Admin_Tools' ) ) {
			require_once WP_PLUGIN_DIR . '/weforms/includes/admin/class-admin-tools.php';
		}

		return \WeForms_Admin_Tools::import_json_file( $filepath );
	}
}
