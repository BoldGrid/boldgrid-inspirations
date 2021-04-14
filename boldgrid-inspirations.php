<?php
/**
 * Plugin Name: BoldGrid Inspirations
 * Plugin URI: https://www.boldgrid.com/boldgrid-inspirations/
 * Version: 2.6.2
 * Author: BoldGrid <support@boldgrid.com>
 * Author URI: https://www.boldgrid.com/
 * Description: Find inspiration, customize, and launch! BoldGrid Inspirations includes FREE WordPress themes and is the easiest way to launch a new WordPress site complete with custom content.
 * Text Domain: boldgrid-inspirations
 * Domain Path: /languages
 * License: GPL
 */

// Define version.
if ( ! defined( 'BOLDGRID_INSPIRATIONS_VERSION' ) ) {
	define( 'BOLDGRID_INSPIRATIONS_VERSION', implode( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
}

// Used for this and other BoldGrid plugins to locate the core plugin directory.
if ( ! defined( 'BOLDGRID_BASE_DIR' ) ) {
	define( 'BOLDGRID_BASE_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'BOLDGRID_BASE_URL' ) ) {
	define( 'BOLDGRID_BASE_URL', plugins_url( '', __FILE__ ) );
}

// Prevent direct calls.
require BOLDGRID_BASE_DIR . '/pages/templates/restrict-direct-access.php';

// If our class is not loaded, then require it.
if ( ! class_exists( 'Boldgrid_Inspirations' ) ) {
	require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations.php';
}

// If PHP is compatible, then load the rest.
if ( Boldgrid_Inspirations::is_php_compatible() ) {
	// Classes needed ASAP.
	require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-inspiration.php';
	require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-survey.php';

	// Instantiate the inspiration class (also loads the parent class Boldgrid_Inspirations).
	$boldgrid_inspirations_inspiration = new Boldgrid_Inspirations_Inspiration();

	// Add action to call pre_add_hooks after init.
	add_action( 'init',
		array(
			$boldgrid_inspirations_inspiration,
			'pre_add_hooks',
		)
	);
	// Include the autoloader to set plugin options and create instance.
	$loader = require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	// Load Library.
	$load = new Boldgrid\Library\Util\Load(
		array(
			'type' => 'plugin',
			'file' => plugin_basename( __FILE__ ),
			'loader' => $loader,
			'keyValidate' => true,
			'licenseActivate', false,
		)
	);
	$boldgrid_inspirations_settings = get_option( 'boldgrid_settings' );

	if ( ! empty( $boldgrid_inspirations_settings['library'] ) ) {
		// Load attribution module.
		new Boldgrid\Inspirations\Premium\Attribution;
	}

	// Inspirations survey. Needs to load ASAP in order to filter bgtfw configs.
	$survey = new BoldGrid_Inspirations_Survey();
	$survey->add_hooks();
} else {
	// If PHP is not compatible, deactivate and die if activating from an admin page, or do nothing.
	add_action( 'admin_init', 'Boldgrid_Inspirations::check_php_wp_version' );
}

register_deactivation_hook( __FILE__, array( 'Boldgrid_Inspirations_Attribution_Page', 'on_deactivate' ) );
