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
 * Deploy Cache class.
 *
 * @since 2.5.0
 */
class Cache {
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
	 * Install our caching plugin.
	 *
	 * @since 2.5.0
	 */
	public function install() {
		$data = (object) [
			'plugin_zip_url'       => 'https://downloads.wordpress.org/plugin/w3-total-cache.zip',
			'plugin_title'         => 'W3 Total Cache',
			'plugin_activate_path' => 'w3-total-cache/w3-total-cache.php',
		];

		$this->deploy->download_and_install_plugin(
			$data->plugin_zip_url,
			$data->plugin_activate_path,
			null,
			$data
		);

		$slug = explode( '/', $data->plugin_activate_path )[0];
		$this->deploy->messages->print_plugin( $data->plugin_title, $slug );
	}
}
