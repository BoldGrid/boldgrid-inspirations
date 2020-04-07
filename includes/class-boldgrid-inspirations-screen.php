<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Screen
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Screen class.
 *
 * Handles actions based upon the wordpress admin screen.
 */
class Boldgrid_Inspirations_Screen {
	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		/**
		 * Enqueue js and css files.
		 */

		// current_screen is an admin hook triggered after the necessary elements
		// to identify a screen are set up.
		add_action( 'current_screen',
			array(
				$this,
				'enqueue_script_per_screen_id',
			)
		);

		// Pointers are registered per screen, which makes it fitting to configure them within
		// this screen class.
		add_action( 'current_screen',
			array(
				$this,
				'enqueue_pointers_per_screen_id',
			)
		);

		// Load handlebar templates per screen.
		add_action( 'admin_footer',
			array(
				$this,
				'admin_footer_handlebars_per_screen_id',
			)
		);
	}

	/**
	 * Admin footer per screen id.
	 */
	public function admin_footer_handlebars_per_screen_id() {
		$this->set_screen();

		$js_per_screen_id = array();

		if ( in_array( $this->screen->id, $js_per_screen_id ) ) {
			include BOLDGRID_BASE_DIR . '/pages/templates/screen/id/' . $this->screen->id . '.php';
		}
	}

	/**
	 * Pointers (tooltips).
	 *
	 * Pointers are registered per screen, which makes it fitting to configure them within this
	 * screen class.
	 */
	public function enqueue_pointers_per_screen_id() {
		include_once BOLDGRID_BASE_DIR .
		'/includes/class-boldgrid-inspirations-wp-help-pointers.php';

		$pointers = new Boldgrid_WP_Help_Pointers();

		$pointers->add_hooks();
	}

	/**
	 * Enqueue javascript based upon the screen id.
	 */
	public function enqueue_script_per_screen_id() {
		$this->set_screen();

		$js_per_screen_id = array(
			'appearance_page_staged-theme',
			'media_page_boldgrid-connect-search',
			'page',
			'upload',
		);

		if ( in_array( $this->screen->id, $js_per_screen_id, true ) ) {
			// Setup some vars.
			$handle = 'screen_id_js' . $this->screen->id;

			$file_path = 'assets/js/screen/id/' . $this->screen->id . '.js';

			// Enqueue the js.
			wp_enqueue_script(
				$handle,
				plugins_url(
					$file_path,
					BOLDGRID_BASE_DIR . '/boldgrid-inspirations.php'
				),
				array(),
				BOLDGRID_INSPIRATIONS_VERSION,
				true
			);
		}
	}

	/**
	 * Set screen.
	 */
	public function set_screen() {
		// Get the current screen if we don't already have it.
		if ( ! isset( $this->screen ) ) {
			$this->screen = get_current_screen();

			// Uncomment the below during dev to see which screen you're on.
			// die( "<pre>" . print_r( $this->screen, 1 ) . "</pre>" );
		}
	}
}
