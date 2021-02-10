<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Purchase_Coins
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Purchase Coins class.
 */
class Boldgrid_Inspirations_Purchase_Coins extends Boldgrid_Inspirations {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		if ( is_admin() ) {
			( Boldgrid_Inspirations_Config::use_boldgrid_menu() ? add_action( 'admin_menu',
				array (
					$this,
					'menu_purchase_coins'
				), 1238 ) : add_action( 'admin_menu',
				array (
					$this,
					'menu_purchase_coins'
				), 1456 ) );
		}
	}

	/**
	 * Purchase Coins submenu item.
	 */
	public function menu_purchase_coins() {
		( Boldgrid_Inspirations_Config::use_boldgrid_menu() ? add_submenu_page(
			'boldgrid-transactions', __( 'Purchase Coins', 'boldgrid-inspirations' ), __( 'Purchase Coins', 'boldgrid-inspirations' ), 'administrator',
			'boldgrid-purchase-coins', array (
				$this,
				'page_purchase_coins'
			) ) : add_submenu_page( 'boldgrid-inspirations', __( 'Purchase Coins', 'boldgrid-inspirations' ), __( 'Purchase Coins', 'boldgrid-inspirations' ),
			'administrator', 'boldgrid-purchase-coins',
			array (
				$this,
				'page_purchase_coins'
			) ) );
	}

	/**
	 * Menu callback.
	 */
	public function page_purchase_coins() {
		include BOLDGRID_BASE_DIR . '/pages/purchase_coins.php';
	}
}
