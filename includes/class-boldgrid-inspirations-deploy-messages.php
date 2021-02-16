<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Deploy_Messages
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Inspiration Deploy Messages class.
 *
 * @sincd 1.7..0
 */
class Boldgrid_Inspirations_Deploy_Messages {

	/**
	 * A list of plugins we have installed.
	 *
	 * @since 1.7.0
	 * @var array
	 */
	public $plugins = array();

	/**
	 * A list of headings we have printed to the page.
	 *
	 * @since 1.7.0
	 * @var array
	 */
	public $headings = array();

	/**
	 * Our Boldgrid_Inspirations_Deploy class.
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
	public function __construct( $deploy ) {
		$this->deploy = $deploy;
	}

	/**
	 * Add a plugin to the list of plugins installed.
	 *
	 * @since 1.7.0
	 *
	 * @param array $data
	 */
	public function add_plugin( $data ) {
		// Determine the slug of this plugin.
		$slug = '';
		if ( ! empty( $data->forked_plugin_path ) ) {
			$slug = explode( '/', $data->forked_plugin_path );
			$slug = $slug[0];
		} elseif( ! empty( $data->plugin_title ) ) {
			$slug = explode( '/', $data->plugin_activate_path );
			$slug = $slug[0];
		}

		// Add this plugin to the array if it doesn't already exist.
		if ( ! in_array( $slug, $this->plugins ) ) {
			$this->plugins[$slug] = array(
				'title' => $data->plugin_title,
			);
		}
	}

	/**
	 * Add a plugin manually.
	 *
	 * @since 1.7.0
	 *
	 * @param string $title Plugin title.
	 * @param string $path  Path the plugin.
	 */
	public function add_plugin_manually( $title, $path ) {
		$plugin                       = new stdClass();
		$plugin->plugin_title         = $title;
		$plugin->plugin_activate_path = $path;

		$this->add_plugin( $plugin );
	}

	/**
	 * Add the weForms plugin to the list of plugins.
	 *
	 * @since 2.3.0
	 */
	public function add_plugin_weforms() {
		$this->add_plugin_manually( 'weForms', 'weforms/weforms.php' );
	}

	/**
	 * Print the Inspirations complete message.
	 *
	 * @since 1.7.0
	 */
	public function print_complete() {
		echo '
			<div class="notice notice-success inline bginsp-deploy-notice">
				<p>
					' . wp_kses(
							sprintf(
								// translators: 1 An opening anchor tag to the My Inspirations page, 2 its closing tag.
								__( '&#10003; Inspirations Installed Successfully! If you are not redirected to your Inspirations dashboard within a few seconds, please %1$sclick here%2$s.', 'boldgrid-inspirations' ),
								'<a href="' . admin_url( 'admin.php?page=my-inspiration' ) . '">',
								'</a>'
							),
							array(
								'a' => array(
									'href' => array(),
								),
							)
						) . ' <span class="spinner inline"></span>
				</p>
			</div>
		';

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}

	/**
	 * Print a heading.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key     The key of the heading.
	 * @param string $heading The actual text of the heading.
	 */
	public function print_heading( $key, $heading ) {
		if ( ! in_array( $key, $this->headings ) ) {
			// $heading is translated with esc_html__, and so is already escaped.
			echo '<p class="heading">' . $heading . '</p>';

			$this->headings[] = $key;
		}

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}

	/**
	 * Print an image.
	 *
	 * @since 1.7.0
	 *
	 * @param int $attachment_id An attachment id.
	 */
	public function print_image( $attachment_id ) {
		$this->print_heading( 'images', esc_html__( 'Downloading images...', 'boldgrid-inspirations' ) );

		echo '
			<div class="installed-item installed-image">
				<img src="' . esc_url( wp_get_attachment_thumb_url( $attachment_id ) ) . '" />
			</div>
		';

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}

	/**
	 * Print a notice.
	 *
	 * @since 1.7.0
	 *
	 * @param string $message The message to print.
	 * @param string $type    The type of notice, such as error, success, etc.
	 */
	public function print_notice( $message, $type = 'error' ) {
		echo '<div class="notice notice-' . $type . ' inline bginsp-deploy-notice"><p>' . $message . '</p></div>';
	}

	/**
	 * Print a page.
	 *
	 * @since 1.7.0
	 *
	 * @param stdClass $page
	 */
	public function print_page( $page ) {
		$this->print_heading( 'page', esc_html__( 'Installing your pages...', 'boldgrid-inspirations' ) );

		echo '
			<div class="installed-item installed-page">
				<span class="dashicons dashicons-media-default"></span>
				<p>' . esc_html( $page->page_title ) . '</p>
			</div>
		';

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}

	/**
	 * Print a plugin.
	 *
	 * @since 1.7.0
	 *
	 * @param string $title The title of the plugin.
	 * @param string $slug  Optional, the slug of the plugin.
	 */
	public function print_plugin( $title, $slug = '' ) {
		// @todo This is a temporary fix as the BGB Premium plugin has no icon.
		if ( 'boldgrid-backup-premium' === $slug ) {
			$slug = 'boldgrid-backup';
		}

		// @todo This is a temporary fix as the P&PB Premium plugin has no icon.
		if ( 'post-and-page-builder-premium' === $slug ) {
			$slug = 'post-and-page-builder';
		}

		// @todo This is a temporary fix as the Crio Premium plugin has no icon.
		if ( 'crio-premium' === $slug ) {
			$slug = '';
		}

		/*
		 * Determine what we will print as an icon for the plugin. It will either be a generic
		 * dashicon, or the plugin's actual icon / logo.
		 */
		$icon = '<span class="dashicons dashicons-admin-plugins"></span>';
		if ( ! empty( $slug ) ) {
			$url  = 'https://ps.w.org/' . $slug . '/assets/icon-128x128.png';
			$icon = '<img src="' . esc_url( $url ) . '" />';
		}

		echo '
			<div class="installed-item installed-plugin">
				' . $icon /* phpcs:ignore */ . '
				<p>' . esc_html( $title ) . '</p>
			</div>
		';

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}

	/**
	 * Print all plugins that have been installed.
	 *
	 * We install plugins in two stages during the inspirations process:
	 * 1. Plugins required for certain pages (like a form plugin).
	 * 2. Plugins that are sitewide (like an seo plugin).
	 *
	 * The plugin installs are also not back to back. We don't want to show the user what plugins
	 * are being install for pages, then the images we're downloading, then more plugins (which are
	 * the sitewide plugins).
	 *
	 * When we install a plugin for a particular page, we queue it up using self::add_plugin(). Then,
	 * when we get to the sitewide plugins, we first print all plugins previously install, then we
	 * print each sitewide plugin as it is installed.
	 *
	 * This method handles the first step, printing all the plugins we previously installed, the
	 * per page plugins.
	 */
	public function print_plugins() {
		$this->print_heading( 'plugins', esc_html__( 'Installing plugins...', 'boldgrid-inspirations' ) );

		foreach( $this->plugins as $plugin_slug => $plugin_data ) {
			// No icon available for wc-gallery.
			$plugin_slug = 'wc-gallery' === $plugin_slug ? '' : $plugin_slug;

			$this->print_plugin( $plugin_data['title'], $plugin_slug );
		}
	}

	/**
	 * Print a theme.
	 *
	 * @param stdObject $theme_details The details of our theme, as received from the api server.
	 */
	public function print_theme( $theme_details ) {
		$this->print_heading( 'theme', esc_html__( 'Installing your theme...', 'boldgrid-inspirations' ) );

		$theme_name = $theme_details->themeRevision->Title;
		$screenshot = $this->deploy->deploy_theme->get_screenshot_url();

		echo '
			<div class="installed-item installed-theme">
				<img src="' . esc_url( $screenshot ) . '" />
				<p>' . esc_html( $theme_name ) . '</p>
			</div>
		';

		Boldgrid_Inspirations_Utility::inline_js_oneliner( 'scrollToBottom();' );
	}
}
