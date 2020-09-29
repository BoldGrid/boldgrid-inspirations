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
 * @since SINCEVERSION
 */
class Utility {
	/**
	 * Get the last form created.
	 *
	 * @since SINCEVERSION
	 *
	 * @return object
	 */
	public static function get_latest_form() {
		$forms_manager = new WeForms_Form_Manager();

		return $forms_manager->all()['forms'][0];
	}
	/**
	 * Import a json file.
	 *
	 * @since SINCEVERSION
	 *
	 * @param string $filepath Path to json file.
	 * @return bool Status of import.
	 */
	public static function import_json_file( $filepath ) {
		if ( ! class_exists( 'WeForms_Admin_Tools' ) ) {
			require_once WP_PLUGIN_DIR . '/weforms/includes/admin/class-admin-tools.php';
		}

		return WeForms_Admin_Tools::import_json_file( $filepath );
	}
}