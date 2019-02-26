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

	private $screen_id = 'inspirations_page_my-inspiration';

	/**
	 * Add Admin hooks.
	 *
	 * This method is called via the Boldgrid_Inspirations_Inspiration::add_hooks method, specifically
	 * within the is_admin conditional.
	 *
	 * @since x.x.x
	 */
	public function add_admin_hooks() {
		// Reset meta box order.
		// delete_user_meta( get_current_user_id(), 'meta-box-order_' . $this->screen_id );

		add_action( 'admin_menu', array( $this, 'admin_menu', ) );

		add_action( 'admin_footer-' . $this->screen_id, array( $this, 'page_footer' ) );

		add_action( 'load-' . $this->screen_id, array( $this, 'add_screen_meta_boxes' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 *
	 */
	public function add_meta_boxes() {
		add_action('add_meta_boxes_'.$this->screen_id, function() {
			$theme = wp_get_theme();

			$installed = new Boldgrid_Inspirations_Installed();

			$on_click = ' <span class="dashicons dashicons-editor-help" onclick="event.stopPropagation(); imageEdit.toggleHelp(this); return false;"></span>';

			add_meta_box(
				'current_inspiration',
				esc_html__('Current Inspiration','plugin_domain'),
				array( $this, 'box_current_screen' ),
				$this->screen_id,
				'container1'
			);

			add_meta_box(
				'pages_content',
				__('Pages Content','plugin_domain') . $on_click,
				array( $this, 'box_pages' ),
				$this->screen_id,
				'container2'
			);

			add_meta_box(
				'customization',
				__('Customize Theme','plugin_domain') . $on_click,
				array( $this, 'box_customization' ),
				$this->screen_id,
				'container3'
			);

			add_meta_box(
				'theme',
				esc_html__( 'Current theme:', 'boldgrid-inspirations' ) . ' ' . esc_html__( $theme->get( 'Name' ) ),
				array( $this, 'box_theme' ),
				$this->screen_id, // Screen to which to add the meta box
				'container4' // Context
			);

			if ( $installed->has_installed_posts() ) {
				add_meta_box(
					'additional_features', //Meta box ID
					esc_html__( 'Additional Features', 'boldgrid-inspirations' ),
					array( $this, 'box_features' ),
					$this->screen_id, // Screen to which to add the meta box
					'container4' // Context
				);
			}

			add_meta_box(
				'support', //Meta box ID
				esc_html__( 'Support & Learning', 'boldgrid-inspirations' ),
				array( $this, 'box_support' ),
				$this->screen_id, // Screen to which to add the meta box
				'container5' // Context
			);

			// Add .imgedit-group-top class to applicable meta boxes so that the help icons work.
			$box_ids = array( 'pages_content', 'customization' );
			foreach( $box_ids as $id ) {
				add_filter( 'postbox_classes_' . $this->screen_id . '_' . $id, function( $classes = array() ) {
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
	 *
	 */
	public function add_screen_meta_boxes() {
		$this->add_meta_boxes();

		/* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
		do_action('add_meta_boxes_'. $this->screen_id, null);
		do_action('add_meta_boxes', $this->screen_id, null);

		/* Enqueue WordPress' script for handling the meta boxes */
		wp_enqueue_script('postbox');

		/* Add screen option: user can choose between 1 or 2 columns (default 2) */
		// add_screen_option('layout_columns', array( 'max' => 4, 'default' => 4) );
	}

	/**
	 *
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
	 * @since x.x.x
	 */
	public function admin_menu() {
		add_submenu_page(
			'boldgrid-inspirations',
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			'manage_options',
			'my-inspiration',
			array( $this, 'page' )
		);
	}

	/**
	 *
	 */
	public function box_current_screen() { ?>
		<p>
			<a href="<?php echo esc_url( get_site_url() ); ?>" class="button button-primary dashicons-before dashicons-admin-home"><?php echo esc_html__( 'View Site', 'boldgrid-inspirations' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=boldgrid-inspirations' ) ); ?>" class="button button-primary dashicons-before dashicons-lightbulb"><?php echo esc_html__( 'Start Over with New Inspiration', 'boldgrid-inspirations' ); ?></a>
		</p>
	<?php }

	/**
	 *
	 */
	public function box_customization() {
	// Link to Customizer.
	$return_url     = 'admin.php?page=admin.php&page=my-inspiration';
	$return_url    .= empty( $_GET['new_inspiration'] ) ? '' : 'new_inspiration=1';
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
			<a href="<?php echo esc_url( $colors_url ); ?>" class="dashicons-before dashicons-art"><?php echo esc_html__( 'Colors', 'boldgrid-inspirations' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( $logo_url ); ?>" class="dashicons-before dashicons-id-alt"><?php echo esc_html__( 'Logo', 'boldgrid-inspirations' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( $contact_url ); ?>" class="dashicons-before dashicons-phone"><?php echo esc_html__( 'Contact Info', 'boldgrid-inspirations' ); ?></a>
		</li>
	</ul>
	<a href="<?php echo esc_url( $customizer_url ); ?>" class="button button-primary dashicons-before dashicons-admin-customize"><?php echo esc_html__( 'Go to Customizer', 'boldgrid-inspirations' ); ?></a>
	<?php }

	/**
	 *
	 */
	public function box_features() { ?>
		<ul>
			<li><?php echo esc_html__( 'Blog', 'boldgrid-inspirations' ); ?> <a class="dashicons-before dashicons-admin-post small" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php echo esc_html__( 'Go to Posts', 'boldgrid-inspirations' ); ?></a>
		</ul>
	<?php }

	/**
	 *
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
					<a href="" class="dashicons-before dashicons-edit" title="' . esc_attr__( 'Edit', 'boldgrid-inspirations' ) . '"></a>
					<a href="" class="dashicons-before dashicons-external" title="' . esc_attr( 'View', 'boldgrid-inspirations' ) . '"></a>
				</span>
				<div style="clear:both;"></div>
			</li>';
		}
		?>
		</ul>

		<p>
			<a href="<?php echo admin_url( 'post-new.php?post_type=page' ); ?>" class="dashicons-before dashicons-welcome-add-page"><?php esc_html_e( 'Add New Page', 'boldgrid-inspirations' ); ?></a>
		</p>

		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button button-primary dashicons-before dashicons-admin-page"><?php echo esc_html__( 'Go to Page Editor', 'boldgrid-inspirations' ); ?></a>
	<?php }

	/**
	 *
	 */
	public function box_support() {
		$reseller = new \Boldgrid\Library\Library\Menu\Reseller();
	?>
		<p>
			<a href="https://www.boldgrid.com/support/inspirations-plugin/" class="dashicons-before dashicons-external"><?php echo esc_html__( 'View Support Docs', 'boldgrid-inspirations' ); ?></a>
		</p>

		<h3><?php echo esc_html__( 'New to Wordpress? Ways to get Support:', 'boldgrid-inspirations' ); ?></h3>

		<ul class="support-boxes">

			<li>
				<?php echo esc_html__( 'Find guides and tutorials on BoldGrid.com.', 'boldgrid-inspirations' ); ?>
				<p>
					<a href="https://www.boldgrid.com/support/" class="button button-primary dashicons-before dashicons-sos"><?php echo esc_html__( 'View Tutorials', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

			<li>
				<?php echo esc_html__( 'Need one-one support?', 'boldgrid-inspirations' ); ?>
				<p>
					<a href="<?php echo esc_url( $reseller->getAttribute( 'reseller_amp_url' ) ); ?>" class="button button-primary dashicons-before dashicons-admin-users"><?php echo esc_html__( 'Login Now', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

			<li>
				<?php echo esc_html__( 'Get support from your fellow users.', 'boldgrid-inspirations' ); ?>
				<p>
					<a href="https://www.facebook.com/groups/BGTeamOrange" class="button button-primary dashicons-before dashicons-format-chat"><?php echo esc_html__( 'User Groups', 'boldgrid-inspirations' ); ?></a>
				</p>
			</li>

		</ul>
	<?php }

	/**
	 *
	 */
	public function box_theme() {
		$theme = wp_get_theme(); ?>

		<p><img src="<?php echo esc_url( $theme->get_screenshot() ); ?>" style="max-width:100%; border:1px solid #ddd;" /></p>

		<p style="text-align:center;">
			<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>" class="button button-primary dashicons-before dashicons-admin-appearance"><?php echo esc_html__( 'Choose New Theme' , 'boldgrid-inspirations' ); ?></a>
		</p>
	<?php }

	/**
	 * Render the "My Inspiration" page.
	 *
	 * @since x.x.x
	 */
	public function page() {
		include BOLDGRID_BASE_DIR . '/pages/my-inspiration.php';
	}

	/**
	 *
	 */
	public function page_footer() {
		?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// toggle
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			postboxes.add_postbox_toggles( pagenow );
		});
		//]]>
		</script>
		<?php
	}
}
