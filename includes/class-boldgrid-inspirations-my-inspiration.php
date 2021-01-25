<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_My_Inspiration
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Inspiration My Inspiration class.
 */
class Boldgrid_Inspirations_My_Inspiration {

	/**
	 * The My Inspirations screen id.
	 *
	 * @since 1.7.0
	 * @var string $screen_id
	 * @access private
	 */
	private $screen_id = 'admin_page_my-inspiration';

	/**
	 * Add Admin hooks.
	 *
	 * This method is called via the Boldgrid_Inspirations_Inspiration::add_hooks method, specifically
	 * within the is_admin conditional.
	 *
	 * @since 1.7.0
	 */
	public function add_admin_hooks() {
		/*
		 * Reset meta box order.
		 *
		 * For developers only. If you drag metaboxes around / etc and need to reset things, do this.
		 */
		// delete_user_meta( get_current_user_id(), 'meta-box-order_' . $this->screen_id );

		add_action( 'admin_menu', array( $this, 'admin_menu', ) );

		add_action( 'admin_footer-' . $this->screen_id, array( $this, 'page_footer' ) );

		add_action( 'load-' . $this->screen_id, array( $this, 'add_screen_meta_boxes' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Add our meta boxes.
	 *
	 * @since 1.7.0
	 */
	public function add_meta_boxes() {
		add_action( 'add_meta_boxes_' . $this->screen_id, function() {
			$theme = wp_get_theme();

			$installed = new Boldgrid_Inspirations_Installed();

			// Help button, which includes applicable js to handle toggling the help.
			$on_click = ' <span class="dashicons dashicons-editor-help" onclick="event.stopPropagation(); imageEdit.toggleHelp(this); return false;"></span>';

			add_meta_box(
				'current_inspiration',
				esc_html__( 'Current Inspiration', 'boldgrid-inspirations' ),
				array( $this, 'box_current_inspiration' ),
				$this->screen_id,
				'container1'
			);

			add_meta_box(
				'pages_content',
				esc_html__( 'Site Content', 'boldgrid-inspirations' ) . $on_click,
				array( $this, 'box_pages' ),
				$this->screen_id,
				'container2'
			);

			add_meta_box(
				'customization',
				esc_html__( 'Customize Theme', 'boldgrid-inspirations' ) . $on_click,
				array( $this, 'box_customization' ),
				$this->screen_id,
				'container3'
			);

			add_meta_box(
				'theme',
				esc_html__( 'Current theme:', 'boldgrid-inspirations' ) . ' ' . esc_html__( $theme->get( 'Name' ) ),
				array( $this, 'box_theme' ),
				$this->screen_id,
				'container4'
			);

			// Section temporarily removed.
			/*
			if ( $installed->has_installed_posts() ) {
				add_meta_box(
					'additional_features',
					esc_html__( 'Additional Features', 'boldgrid-inspirations' ),
					array( $this, 'box_features' ),
					$this->screen_id,
					'container4'
				);
			}
			*/

			add_meta_box(
				'support',
				esc_html__( 'Support & Learning', 'boldgrid-inspirations' ),
				array( $this, 'box_support' ),
				$this->screen_id,
				'container5'
			);

			if ( \Boldgrid\Inspirations\Sprout\Utility::is_deploy() ) {
				add_meta_box(
					'invoice',
					esc_html__( 'Sprout Invoices', 'boldgrid-inspirations' ),
					array( $this, 'box_invoice' ),
					$this->screen_id,
					'container6'
				);
			}

			if ( \Boldgrid\Inspirations\W3TC\Utility::is_deploy() ) {
				add_meta_box(
					'cache',
					esc_html__( 'W3 Total Cache', 'boldgrid-inspirations' ),
					array( $this, 'box_cache' ),
					$this->screen_id,
					'container6'
				);
			}

			// Add .imgedit-group-top class to applicable meta boxes so that the help icons work.
			$box_ids = array( 'pages_content', 'customization' );
			foreach( $box_ids as $id ) {
				add_filter( 'postbox_classes_' . $this->screen_id . '_' . $id, function( array $classes = array() ) {
					$class = 'imgedit-group-top';

					if ( ! in_array( $class, $classes ) ) {
						$classes[] = $class;
					}

					return $classes;
				} );
			}
		});
	}

	/**
	 * Add screen meta boxes.
	 *
	 * @since 1.7.0
	 */
	public function add_screen_meta_boxes() {
		$this->add_meta_boxes();

		do_action( 'add_meta_boxes_'. $this->screen_id, null );
		do_action( 'add_meta_boxes', $this->screen_id, null );

		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.7.0
	 *
	 * @param string $hook Current hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook !== $this->screen_id ) {
			return;
		}

		wp_enqueue_script(
			'my-inspiration-js',
			plugins_url( '/' . basename( BOLDGRID_BASE_DIR ) . '/assets/js/my-inspiration.js' ),
			array( 'jquery' ),
			BOLDGRID_INSPIRATIONS_VERSION,
			true
		);

		wp_enqueue_style(
			'my-inspiration-css',
			plugins_url( '/' . basename( BOLDGRID_BASE_DIR ) . '/assets/css/my-inspiration.css' ),
			array(),
			BOLDGRID_INSPIRATIONS_VERSION
		);

		wp_enqueue_script( 'image-edit' );
	}

	/**
	 * Add our menu item.
	 *
	 * @since 1.7.0
	 */
	public function admin_menu() {
		add_submenu_page(
			// Null so "My Inspiration" does not show as a menu item.
			null,
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			'manage_options',
			'my-inspiration',
			array( $this, 'page' )
		);
	}

	/**
	 * Render the W3 Total Cache meta box.
	 *
	 * @since 2.5.0
	 */
	public function box_cache() { ?>
		<img class="myinsp-logo" src="<?php echo esc_url( BOLDGRID_BASE_URL . '/assets/images/inspirations/cache/w3-total-cache.png' ); ?>">
		<p><?php esc_html_e( 'W3 Total Cache speeds up your WordPress website by reducing its download time, which makes your page load extremely fast.', 'boldgrid-inspirations' ); ?></p>
		<ul>
			<li><a href="https://www.boldgrid.com/support/w3-total-cache/"><?php esc_html_e( 'W3 Total Cache Tutorials', 'boldgrid-inspirations' );?></a></li>
			<li><a href="https://www.boldgrid.com/support/w3-total-cache/configuring-w3-total-cache-for-wordpress-with-shared-hosting/"><?php esc_html_e( 'How to configure W3 Total Cache', 'boldgrid-inspirations' );?></a></li>
		</ul>
	<?php }

	/**
	 * Render meta box for, "Current Inspiration".
	 *
	 * @since 1.7.0
	 */
	public function box_current_inspiration() { ?>
		<p>
			<a href="<?php echo esc_url( get_site_url() ); ?>" class="button button-primary dashicons-before dashicons-admin-home"><?php esc_html_e( 'View Site', 'boldgrid-inspirations' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=boldgrid-inspirations&force=1' ) ); ?>" class="button dashicons-before dashicons-lightbulb"><?php esc_html_e( 'Start Over with New Inspiration', 'boldgrid-inspirations' ); ?></a>
		</p>
	<?php }

	/**
	 * Render meta box for, "Customization".
	 *
	 * @since 1.7.0
	 */
	public function box_customization() {
	// Link to Customizer.
	$return_url     = 'admin.php?page=admin.php&page=my-inspiration';
	$return_url    .= empty( $_GET['new_inspiration'] ) ? '' : '&new_inspiration=1';
	$customizer_url = admin_url( 'customize.php' );
	$customizer_url = add_query_arg( 'return', urlencode( $return_url ), $customizer_url );


	// Links to specific sections within the Customizer.
	$colors_url  = add_query_arg( 'autofocus[section]', 'colors', $customizer_url );
	$logo_url    = add_query_arg( 'autofocus[section]', 'title_tagline', $customizer_url );
	$contact_url = add_query_arg( 'autofocus[section]', 'boldgrid_footer_panel', $customizer_url );
	?>
	<p class="imgedit-help">
		<?php esc_html_e( 'If you want to dive into the Customizer and change colors, fonts, headers and footers, etc., go to the Customizer directly.', 'boldgrid-inspirations' ); ?>
	</p>

	<ul>
		<li>
			<a href="<?php echo esc_url( $colors_url ); ?>" class="dashicons-before dashicons-art"><?php esc_html_e( 'Colors', 'boldgrid-inspirations' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( $logo_url ); ?>" class="dashicons-before dashicons-id-alt"><?php esc_html_e( 'Logo', 'boldgrid-inspirations' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( $contact_url ); ?>" class="dashicons-before dashicons-phone"><?php esc_html_e( 'Contact Info', 'boldgrid-inspirations' ); ?></a>
		</li>
	</ul>
	<a href="<?php echo esc_url( $customizer_url ); ?>" class="button dashicons-before dashicons-admin-customize"><?php esc_html_e( 'Go to Customizer', 'boldgrid-inspirations' ); ?></a>
	<?php }

	/**
	 * Render meta box for, "Features".
	 *
	 * @since 1.7.0
	 */
	public function box_features() { ?>
		<ul>
			<li><?php esc_html_e( 'Blog', 'boldgrid-inspirations' ); ?> <a class="dashicons-before dashicons-admin-post small" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Go to Posts', 'boldgrid-inspirations' ); ?></a>
		</ul>
	<?php }

	/**
	 * Render meta box for, "Sprout Invoices".
	 *
	 * @since 2.5.0
	 */
	public function box_invoice() { ?>
		<img class="myinsp-logo" src="<?php echo esc_url( BOLDGRID_BASE_URL . '/assets/images/inspirations/invoice/sprout-invoices.png' ); ?>">
		<p><?php esc_html_e( 'With Sprout Invoices, you can create beautiful estimates and invoices for your clients in minutes, and get paid easily.', 'boldgrid-inspirations' ); ?></p>
		<ul>
			<li><a href="https://docs.sproutinvoices.com/article/5-getting-started-sprout-invoices"><?php esc_html_e( 'Getting Started with Sprout Invoices', 'boldgrid-inspirations' ); ?></a></li>
			<li><a href="https://docs.sproutinvoices.com/article/263-weforms-integration"><?php esc_html_e( 'Sprout Invoices + weForms Integration', 'boldgrid-inspirations' ); ?></a></li>
		</ul>
	<?php }

	/**
	 * Render meta box for, "Pages".
	 *
	 * @since 1.7.0
	 */
	public function box_pages() {
		$installed = new Boldgrid_Inspirations_Installed();

		$pages = $installed->get_all_pages(); ?>

		<p class="imgedit-help">
			<?php esc_html_e( 'If you\'re happy with the look of your Inspiration theme and ready to start editing the content of your site, go directly to your page editor.', 'boldgrid-inspirations' ); ?>
		</p>

		<ul>
		<?php
		foreach( $pages as $page ) {
			echo '
			<li>' .
				esc_html__( $page->post_title ) . ' (<em>' . $page->post_type . '</em>)
				<span style="float:right;">
					<a href="' . get_edit_post_link( $page->ID ) . '" class="dashicons-before dashicons-edit" title="' . esc_attr__( 'Edit', 'boldgrid-inspirations' ) . '"></a>
					<a href="' . get_page_link( $page ) . '" class="dashicons-before dashicons-external" title="' . esc_attr( 'View', 'boldgrid-inspirations' ) . '" target="_blank"></a>
				</span>
				<div style="clear:both;"></div>
			</li>';
		}
		?>
		</ul>

		<p>
			<a href="<?php echo admin_url( 'post-new.php?post_type=page' ); ?>" class="dashicons-before dashicons-welcome-add-page"><?php esc_html_e( 'Add New Page', 'boldgrid-inspirations' ); ?></a>
			<?php if ( $installed->has_installed_posts() ) { ?>
			| <a href="<?php echo admin_url( 'post-new.php' ); ?>"><?php esc_html_e( 'Add New Post', 'boldgrid-inspirations' ); ?></a>
			<?php } ?>
		</p>

		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button dashicons-before dashicons-admin-page"><?php esc_html_e( 'Go to All Pages', 'boldgrid-inspirations' ); ?></a>
		<?php if ( $installed->has_installed_posts() ) { ?>
			<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="button dashicons-before dashicons-admin-post"><?php esc_html_e( 'Go to All Posts', 'boldgrid-inspirations' ); ?></a>
		<?php } ?>
	<?php }

	/**
	 * Render meta box for, "Support".
	 *
	 * @since 1.7.0
	 */
	public function box_support() {
		$reseller = new \Boldgrid\Library\Library\Menu\Reseller();

		// The method_exists() call is a tmp fix in cases where the wrong library is loaded.
		$reseller_amp_url = method_exists( $reseller, 'getAttribute' ) ? $reseller->getAttribute( 'reseller_amp_url' ) : 'https://www.boldgrid.com/central';
	?>
		<p>
			<a href="https://www.boldgrid.com/support/inspirations-plugin/" class="dashicons-before dashicons-external" target="_blank"><?php esc_html_e( 'View Support Docs', 'boldgrid-inspirations' ); ?></a>
		</p>

		<h3><?php esc_html_e( 'New to WordPress? Ways to get Support:', 'boldgrid-inspirations' ); ?></h3>

		<ul class="support-boxes">

			<li>
				<?php esc_html_e( 'Find guides and tutorials on BoldGrid.com.', 'boldgrid-inspirations' ); ?>
				<p>
					<a href="https://www.boldgrid.com/support/" class="button dashicons-before dashicons-sos" target="_blank"><?php esc_html_e( 'View Tutorials', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

			<li>
				<?php echo wp_kses(
					sprintf(
						// translators: 1 a line break for formatting purposes, we want the string to be on two lines.
						esc_html__( 'Need one-one %1$s support?', 'boldgrid-inspirations' ),
						'<br />'
					),
					array( 'br' => array() )
				); ?>
				<p>
					<a href="<?php echo esc_url( $reseller_amp_url ); ?>" class="button dashicons-before dashicons-admin-users" target="_blank"><?php esc_html_e( 'Login Now', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

			<li>
				<?php esc_html_e( 'Get support from your fellow users.', 'boldgrid-inspirations' ); ?>
				<p>
					<a href="https://www.facebook.com/groups/BGTeamOrange" class="button dashicons-before dashicons-format-chat" target="_blank"><?php esc_html_e( 'User Groups', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

		</ul>
	<?php }

	/**
	 * Render meta box for, "Theme".
	 *
	 * @since 1.7.0
	 */
	public function box_theme() {
		$theme = wp_get_theme(); ?>

		<p><img src="<?php echo esc_url( $theme->get_screenshot() ); ?>" style="max-width:100%; border:1px solid #ddd;" /></p>

		<p style="text-align:center;">
			<a href="<?php echo esc_url( admin_url( 'theme-install.php?browse=featured' ) ); ?>" class="button dashicons-before dashicons-admin-appearance"><?php esc_html_e( 'Choose New Theme' , 'boldgrid-inspirations' ); ?></a>
		</p>
	<?php }

	/**
	 * Get the URL to the My Inspirations page.
	 *
	 * @since 1.7.0
	 *
	 * @param  bool $new Whether or not to include the new_inspiration flag.
	 * @return string
	 */
	public static function get_url( $new = false ) {
		$url = admin_url( 'admin.php?page=my-inspiration' );

		if ( $new ) {
			$url .= '&new_inspiration=1';
		}

		return $url;
	}

	/**
	 * Render the "My Inspiration" page.
	 *
	 * @since 1.7.0
	 */
	public function page() {
		include BOLDGRID_BASE_DIR . '/pages/my-inspiration.php';
	}

	/**
	 * Render required js for meta boxes.
	 *
	 * @since 1.7.0
	 */
	public function page_footer() {
		?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery( document ).ready( function( $ ) {
			$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
			postboxes.add_postbox_toggles( pagenow );
		});
		//]]>
		</script>
		<?php
	}

	/**
	 * Redirect the user to the My Insprations page.
	 *
	 * @since 1.7.0
	 */
	public static function redirect() {
		wp_redirect( admin_url( 'admin.php?page=my-inspiration' ) );
		exit;
	}
}
