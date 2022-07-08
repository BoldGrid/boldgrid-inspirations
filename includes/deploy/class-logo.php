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
 * Deploy Logo class.
 *
 * @since SINCEVERSION
 */
class Logo {
	/**
	 * Download and setup a logo.
	 *
	 * @since SINCEVERSION
	 *
	 * @param int                          $asset_id The asset_id of our logo.
	 * @param Boldgrid_Inspirations_Deploy $deploy   Our deployment class.
	 */
	public static function deploy( $asset_id, $deploy ) {
		if ( ! is_numeric( $asset_id ) ) {
			return false;
		}

		$attachment_data = $deploy->asset_manager->download_and_attach_asset( false, $asset_id, null, 'all' );

		if ( empty( $attachment_data['attachment_id'] ) || ! is_numeric( $attachment_data['attachment_id'] ) ) {
			return false;
		} else {
			$attachment_id = $attachment_data['attachment_id'];
		}

		set_theme_mod( 'custom_logo', $attachment_id );
		update_option( 'site_logo', $attachment_id );

		return true;
	}
}
