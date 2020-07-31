<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Branding
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Branding class.
 */
class Boldgrid_Inspirations_Branding {
	/**
	 * Class property for reseller information array with elements: {
	 * 	reseller_identifier
	 * 	reseller_title
	 * 	reseller_logo_url
	 * 	reseller_website_url
	 * 	reseller_support_url
	 * 	reseller_amp_url
	 * 	reseller_email
	 * 	reseller_phone
	 * 	reseller_css_url
	 * }
	 *
	 * @var array
	 * @access private
	 */
	private $reseller_data = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Get the WP option containing reseller data.
		$reseller_data = get_option( 'boldgrid_reseller' );

		// Set the class property $reseller_data.
		$this->set_reseller_data( $reseller_data );
	}

	/**
	 * Set the class property $reseller_data.
	 *
	 * @param array $reseller_data Reseller data array.
	 */
	private function set_reseller_data( $reseller_data = array() ) {
		$this->reseller_data = $reseller_data;
	}

	/**
	 * Get the class property $reseller_data.
	 */
	private function get_reseller_data() {
		return $this->reseller_data;
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		global $wp_version;

		$reseller_data = $this->get_reseller_data();

		/*
		 * Add hooks to brand the login page.
		 *
		 * As of @since 2.0.5, this section has been (1) cleaned up / reorganized and (2) the ability
		 * to skip login page branding can now be done via a new attribute, reseller_brand_login.
		 *
		 * If the reseller_brand_login attribute is missing, continue branding as done prior to
		 * @since 2.0.5. It's missing because Inspirations is reading the reseller data from a
		 * transient, and the attribute hasn't been fetched from the API server yet. Otherwise, users
		 * would upgrade Inspirations, and until the reseller data transient cleared, the branding
		 * they're use to seeing would not show.
		 */
		$is_attribute_missing = ! isset( $reseller_data['reseller_brand_login'] );
		if ( $is_attribute_missing || ! empty( $reseller_data['reseller_brand_login'] ) ) {
			// Login Css.
			add_action( 'login_enqueue_scripts', array( $this, 'boldgrid_login_css' ) );

			// Login Logo Url.
			add_filter( 'login_headerurl', 	array( $this, 'boldgrid_login_logo_url' ) );

			// Login footer.
			add_action( 'login_footer', array( $this, 'boldgrid_login_footer' ) );

			// Link text of the header logo above the login form.
			$hook = version_compare( $wp_version, '5.2', '>=' ) ? 'login_headertext' : 'login_headertitle';
			add_filter( $hook, array( $this, 'boldgrid_login_logo_title' ) );
		}

		// Add action for BoldGrid admin icon.
		add_action( 'init',
			array(
				$this,
				'boldgrid_admin_icon',
			)
		);

		// Add actions and filters for reseller admin bar menu and footer.
		if ( ! empty( $reseller_data['reseller_identifier'] ) ) {
			add_filter( 'admin_footer_text',
				array(
					$this,
					'boldgrid_footer_admin_reseller',
				)
			);
		} else {
			// No reseller.
			add_filter( 'admin_footer_text',
				array(
					$this,
					'boldgrid_footer_admin',
				)
			);
		}
	}

	/**
	 * Change the default WordPress login logo to BoldGrid Login Logo.
	 *
	 * @see login_enqueue_scripts don't echo out to login_head.
	 * @see login_headerurl.
	 * @see login_headertitle.
	 */
	public function boldgrid_login_css() {
		// Get the reseller vars.
		$reseller_data = $this->get_reseller_data();

		$reseller_css_url = esc_url(
			! empty( $reseller_data['reseller_css_url'] ) ?
			$reseller_data['reseller_css_url'] : plugins_url() . '/' .
			basename( BOLDGRID_BASE_DIR ) . '/assets/css/boldgrid-login.css'
		);

		wp_register_style(
			'custom-login',
			$reseller_css_url,
			array(),
			BOLDGRID_INSPIRATIONS_VERSION
		);

		wp_enqueue_style( 'custom-login' );

		/* @formatter:off */
		echo "
			<style type='text/css'>
				.login h1 a {
					background-image: url(" . esc_url( plugins_url() . '/' .
			basename( BOLDGRID_BASE_DIR ) .
		'/assets/images/boldgrid-login-logo.png') . ') !important;
                }
            </style>
        ';
		/* @formatter:on */
	}

	/**
	 * Add the login logo url instead of default wordpress.org logo url.
	 */
	public function boldgrid_login_logo_url() {
		return esc_url( 'http://www.boldgrid.com/' );
	}

	/**
	 * Change the hover title from WordPress.org to BoldGrid.com.
	 */
	public function boldgrid_login_logo_title() {
		return esc_html( 'BoldGrid.com' );
	}

	/**
	 * Add custom links and logos to footer.
	 *
	 * BoldGrid with no partner login page footer.
	 */
	public function boldgrid_login_footer() {
		// Get the reseller vars.
		$reseller_data = $this->get_reseller_data();

		$reseller_logo_url = (
			! empty( $reseller_data['reseller_logo_url'] ) ?
			$reseller_data['reseller_logo_url'] :
			plugins_url() . '/' . basename( BOLDGRID_BASE_DIR ) . '/assets/images/wordpresslogo.png'
		);

		$reseller_title       = ! empty( $reseller_data['reseller_title'] ) ? $reseller_data['reseller_title'] : null;
		$reseller_support_url = ! empty( $reseller_data['reseller_support_url'] ) ? $reseller_data['reseller_support_url'] : 'http://www.boldgrid.com/documentation';

		// Print HTML.
		?>
<br />
<center>
	<img src='<?php echo $reseller_logo_url; ?>'>
</center>
<br />
<div style='text-align: center;'>
	<?php esc_html_e( 'Need Support?', 'boldgrid-inspirations' ); ?><br />
		<?php

		if ( ! empty( $reseller_title ) ) {
			printf(
				wp_kses(
					// translators: 1 Title of reseller, 2 opening anchor tag linking to reseller's support center, 3 its closing anchor tag.
					__( '%1$s provides dedicated help for %2$sBoldGrid%3$s.', 'boldgrid-inspirations' ),
					array( 'a' => array( 'target' => array( 'blank' ), 'href' => array(), ) )
					),
				esc_html( $reseller_title ),
				'<a target="_blank" rel="nofollow" href="' . esc_url( $reseller_support_url ) . '">',
				'</a>'
			);
		} else {
			printf(
				// translators: 1 The opening anchor tag linking to the BoldGrid support center, 2 its closing anchor tag.
				__( 'Check out the %1$sBoldGrid Education Channel%2$s!', 'boldgrid-inspirations' ),
				'<a href="http://www.boldgrid.com/support/" target="_blank" rel="nofollow">',
				'</a>'
			);
		}
		?>
		</div>
<?php
	}

	/**
	 * If the WordPress admin bar is visible, enqueue our 'adminiconstyle.css' sheet to add the
	 * icons.
	 *
	 * This will avoid the icon from breaking if there are changes made to CSS styling by user, or
	 * WP core.
	 *
	 * @see is_admin_bar_showing().
	 * @see wp_enqueue_style().
	 */
	public function boldgrid_admin_icon() {
		if ( is_admin_bar_showing() ) {
			wp_enqueue_style(
				'adminiconstyle',
				plugins_url() . '/' . basename( BOLDGRID_BASE_DIR ) .
				'/assets/css/adminiconstyle.css',
				array(
					'admin-bar',
				),
				BOLDGRID_INSPIRATIONS_VERSION,
				'all'
			);
		}
	}

	/**
	 * Custom Footer in Admin Dashboard.
	 *
	 * Replaces default admin footer text.
	 * BoldGrid - No partner branding.
	 *
	 * @see admin_footer_text().
	 */
	public function boldgrid_footer_admin() {
		printf(
			wp_kses(
				// translators: 1 Opening anchor tag linking to boldgrid.com, 2 closing anchor tag, 3 opening anchor tag to wordpress.org, 4 opening i tag, 5 closing i tag.
				__( '%4$sBuilt with %1$sBoldGrid%2$s.%5$s | %4$sPowered by %3$sWordPress%2$s.%5$s', 'boldgrid-inspirations' ),
				array( 'a' => array( 'href' => array(), 'target' => array( '_blank' ) ), 'i' => array() )
				),
			'<a href="https://www.boldgrid.com/" target="_blank">',
			'</a>',
			'<a href="http://wordpress.org/" target="_blank">',
			'<i>',
			'</i>'
		);
	}

	/**
	 * Reseller Admin Footer Branding.
	 */
	public function boldgrid_footer_admin_reseller() {
		// Load the general footer.
		$this->boldgrid_footer_admin();

		$reseller_data        = $this->get_reseller_data();
		$reseller_title       = ! empty( $reseller_data['reseller_title'] ) ? $reseller_data['reseller_title'] : 'BoldGrid.com';
		$reseller_support_url = ! empty( $reseller_data['reseller_support_url'] ) ? $reseller_data['reseller_support_url'] : 'https://www.boldgrid.com/documentation';

		// Display the reseller footer.
		printf(
			wp_kses(
				// translators: 1 Opening anchor tag linking to reseller's support url, 2 its closing anchor tag, 3 name of reseller, 4 opening i tag, 5 closing i tag.
				__( '%4$sSupport from %1$s%3$s%2$s.%5$s', 'boldgrid-inspirations' ),
				array( 'a' => array( 'href' => array(), 'target' => array( '_blank' ) ), 'i' => array() )
			),
			'<a target="_blank" href="' . esc_url( $reseller_support_url ) . '">',
			'</a>',
			esc_html( $reseller_title ),
			'<i>',
			'</i>'
		);
	}
}
