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
 * Post Meta Class.
 *
 * This handles the deploying of post meta.
 *
 * @since sinceversion
 */
class Post_Meta {
	/**
	 * Post Meta Keys.
	 *
	 * These post meta keys are to be imported when setting
	 * post meta. Any post meta that is not in this array will
	 * not be imported during the inspirations process.
	 *
	 * @var array post meta keys.
	 */
	public static $post_meta_keys = array(
		'crio-premium-page-header-override',
		'crio-premium-page-header-select',
		'crio-premium-page-header-background',
		'crio-premium-page-header-featured-image-background',
		'crio-premium-template-has-page-title',
		'crio-premium-include-site-header',
	);

	/**
	 * Set Post Meta
	 *
	 * This is run after all pages / posts are added to the site.
	 *
	 * Currently this is only used to add post meta fields for Crio Premium,
	 * but it can be used by other plugins as well.
	 *
	 * @param int   $post_id          Post ID.
	 * @param array $post_meta        Post Meta.
	 * @param bool  $filter_post_meta Whether or not the post_meta should be filtered to replace the local ID with the author ID.
	 */
	public static function set_post_meta( $post_id, $post_meta, $filter_post_meta = false ) {
		foreach ( $post_meta as $key => $value ) {
			if ( false !== array_search( $key, self::$post_meta_keys, true ) && false !== $filter_post_meta ) {
				$filtered_value = \Boldgrid\Inspirations\Deploy\Author_Ids::get_from_author( $value[0] );
				update_post_meta( $post_id, $key, $filtered_value );
			} else if ( false !== array_search( $key, self::$post_meta_keys, true ) ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

}

