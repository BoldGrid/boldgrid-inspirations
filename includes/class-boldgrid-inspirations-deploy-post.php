<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Deploy_Post
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Inspiration Deploy Post class.
 *
 * This class handles tasks to take after a deployment has finished.
 *
 * @since 1.7.0
 */
class Boldgrid_Inspirations_Deploy_Post {
	/**
	 * An instance of Boldgrid_Inspirations_Deploy_Status.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Deploy_Status
	 */
	private $deploy_status;

	/**
	 * An instance of Boldgrid_Inspirations_Installed.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var Boldgrid_Inspirations_Installed
	 */
	private $installed;

	/**
	 * Whether or not we are in the call immediately after a deployment.
	 *
	 * This is the call that triggers the after_switch_theme action.
	 *
	 * @since 1.7.0
	 * @access private
	 * @var bool
	 */
	private $is_post_deploy = false;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->is_post_deploy = ! empty( $_GET['doing_wp_cron'] ) && 'fire-after-theme-switch-hooks' === $_GET['doing_wp_cron'];

		$this->installed = new Boldgrid_Inspirations_Installed();

		$this->deploy_status = new Boldgrid_Inspirations_Deploy_Status();
	}

	/**
	 * Add hooks.
	 *
	 * These hooks are added via the Boldgrid_Inspirations_Inspiration class, within its
	 * add_hooks_always method. These hooks are added regardless of is_admin().
	 *
	 * This method is ran within the "init" filter. Keep this in mind when adding filters below.
	 *
	 * @since 1.7.0
	 */
	public function add_hooks() {
		add_filter( 'option_theme_switched', array( $this, 'stop_switch_theme' ), 10, 2 );

		if ( $this->is_post_deploy ) {
			add_filter( 'after_switch_theme', array( $this, 'install_widgets' ), 15 );

			if ( ! empty( $_POST['install_cache'] ) ) {
				add_filter( 'wp_loaded', '\Boldgrid\Inspirations\W3TC\Utility::deploy_post_setup' );
			}
		}
	}

	/**
	 * Install widgets after a deployment.
	 *
	 * @since 1.7.0
	 */
	public function install_widgets() {
		// If we installed a blog, setup the blog widgets.
		if ( $this->installed->get_install_option( 'install_blog' ) ) {
			$configs = Boldgrid_Inspirations_Config::get_format_configs();

			$blog = new Boldgrid_Inspirations_Blog( $configs );
			$blog->create_sidebar_widgets();
		}
	}

	/**
	 * Prevent "after_switch_theme" action from running too soon.
	 *
	 * Within WordPress' switch_theme() function, the "theme_switched" option is set to the old
	 * theme's stylesheet at the end of the method:
	 * @link https://github.com/WordPress/WordPress/blob/03240dd3f4442546562824bc6a10ed7c197bd6b2/wp-includes/theme.php#L780
	 *
	 * If the "theme_switched" option is found, then ultimately the "after_switch_theme" action will
	 * be ran.
	 * @link https://github.com/WordPress/WordPress/blob/03240dd3f4442546562824bc6a10ed7c197bd6b2/wp-includes/theme.php#L2875
	 *
	 * If we are in the middle of deploying a site with Inspirations, then we don't want the
	 * "after_switch_theme" action to run, as we're still in the middle of setting up the new site.
	 *
	 * To prevent this from happening, we will hook into the call to get the "theme_switched" option
	 * and return false if we're in the middle of a deployment.
	 *
	 * @since 1.7.0
	 *
	 * @param mixed  $value  Value of the option. If stored serialized, it will be unserialized prior
	 *                       to being returned.
	 * @param string $option Option name.
	 *
	 */
	public function stop_switch_theme( $value, $option ) {
		return $this->deploy_status->is_deploying() ? false : $value;
	}
}
