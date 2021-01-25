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
 * @since 2.5.0
 */
class Invoice {
	/**
	 * Our deploy class.
	 *
	 * @since 2.5.0
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy
	 */
	private $deploy;

	/**
	 * Constructor.
	 *
	 * @since 2.5.0
	 *
	 * @param Boldgrid_Inspirations_Deploy $deploy
	 */
	public function __construct( \Boldgrid_Inspirations_Deploy $deploy ) {
		$this->deploy = $deploy;
	}

	/**
	 * Run the deployment of our invoicing plugin.
	 *
	 * @since 2.5.0
	 *
	 * @param array $args An array of arguments.
	 */
	public function deploy( $args = array() ) {
		$this->install();

		// Import our weForms form and get the id.
		\Boldgrid\Inspirations\Weforms\Utility::import_json_file( dirname( __FILE__ ) . '/invoice/forms/get-a-quote.json' );
		$form = \Boldgrid\Inspirations\Weforms\Utility::get_by_title( 'Get a Quote Form' );
		if ( empty( $form->id ) ) {
			return false;
		}

		// Insert our "Get a Quote" page. $form->id is used in get-a-quote.php below.
		$post = include 'invoice/posts/get-a-quote.php';
		$post_id = wp_insert_post( $post );

		// Add the new post to the menu.
		wp_update_nav_menu_item( $args['menu_id'], 0, array(
			'menu-item-object-id' => $post_id,
			'menu-item-parent-id' => 0,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish'
		) );

		// Print a message to the user that we just created this page.
		$post_object = new \stdClass();
		$post_object->page_title = $post['post_title'];
		$this->deploy->messages->print_page( $post_object );
	}

	/**
	 * Install Sprout Invoices.
	 *
	 * @since 2.5.0
	 */
	public function install() {
		$data = (object) [
			'plugin_zip_url'       => 'https://downloads.wordpress.org/plugin/sprout-invoices.zip',
			'plugin_title'         => 'Sprout Invoices',
			'plugin_activate_path' => 'sprout-invoices/sprout-invoices.php',
		];

		$this->deploy->download_and_install_plugin(
			$data->plugin_zip_url,
			$data->plugin_activate_path,
			null,
			$data
		);
	}
}
