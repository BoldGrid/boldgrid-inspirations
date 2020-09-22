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
 * Deploy Invoice class.
 *
 * @since SINCEVERSION
 */
class Invoice {
	/**
	 * Our deploy class.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy
	 */
	private $deploy;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param Boldgrid_Inspirations_Deploy $deploy
	 */
	public function __construct( \Boldgrid_Inspirations_Deploy $deploy ) {
		$this->deploy = $deploy;
	}
}