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
 * This handles the storing of author ids and coorelation between them and local IDs.
 *
 * @since sinceversion
 */
class Author_Ids {

	/**
	 * Set Author IDs.
	 *
	 * Sets the author IDs option.
	 *
	 * @param array $author_ids Author IDs.
	 *
	 * @static
	 */
	public static function set_author_ids( $author_ids_to_local ) {
		update_option( 'boldgrid_author_ids_to_local', $author_ids_to_local );
	}

	/**
	 * Get Author ID From Local ID.
	 *
	 * Returns the author id using the local ID as reference.
	 *
	 * @return string Author ID.
	 *
	 * @static
	 */
	public static function get_from_local( $local_id ) {
		$ids_option = get_option( 'boldgrid_author_ids_to_local', array() );

		return array_search( $local_id, $ids_option ) ? array_search( $local_id, $ids_option ) : $local_id;
	}

	/**
	 * Get Local ID from Author ID.
	 *
	 * Returns the local ID using the author ID as reference.
	 *
	 * @return string Local ID.
	 *
	 * @static
	 */
	public static function get_from_author( $author_id ) {
		$ids_option = get_option( 'boldgrid_author_ids_to_local', array() );

		return isset( $ids_option[ $author_id ] ) ? $ids_option[ $author_id ] : $author_id;
	}
}

