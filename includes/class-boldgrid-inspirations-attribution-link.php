<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Attribution_Asset
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Attribution Asset class.
 *
 * This class includes methods that help process assets during the creating of the Attribution
 * page.
 *
 * @since 1.3.1
 */
class Boldgrid_Inspirations_Attribution_Link {

	/**
	 * @var bool $licensed Licensed plugin?
	 * @var array $controls Controls array.
	 */
	protected
		$licensed,
		$controls;

	/**
	 * Initialize class and set properties.
	 *
	 * @since 1.4.3
	 */
	public function __construct() {
		$this->licensed = $this->setLicensed( true );
		$this->controls = $this->setControls(
			array(
				'reseller_control' => array(
					'type'        => 'checkbox',
					'settings'     => 'hide_partner_attribution',
					'transport'   => 'postMessage',
					'label'       => __( 'Hide Partner Attribution', 'boldgrid-inspirations' ),
					'section'     => 'boldgrid_footer_panel',
					'default'     => false,
					'priority'    => 50,
				),
				'special_thanks_control' => array(
					'type'        => 'checkbox',
					'settings'     => 'hide_special_thanks_attribution',
					'transport'   => 'postMessage',
					'label'       => __( 'Hide Special Thanks Link', 'boldgrid-inspirations' ),
					'section'     => 'boldgrid_footer_panel',
					'default'     => false,
					'priority'    => 60,
				)
			)
		);
	}

	/**
	 * Sets the licensed property
	 *
	 * @since 1.4.3
	 *
	 * @param bool $licensed Licensed plugin?
	 */
	public function setLicensed( $licensed ) {
		return $this->licensed = $licensed;
	}

	/**
	 * Sets the controls array.
	 *
	 * @since 1.4.3
	 *
	 * @param array $controls Controls array.
	 */
	public function setControls( $controls ) {
		return $this->controls = $controls;
	}

	/**
	 * Add hooks.
	 *
	 * Adds the necessary hooks for this class to implement
	 * it's functionality in WordPress.
	 *
	 * @since 1.4.3
	 */
	public function add_hooks() {
		add_filter( 'bgtfw_attribution_links', array( $this, 'add_reseller_link' ) );
		add_filter( 'bgtfw_attribution_links', array( $this, 'add_attribution_link' ) );
		add_filter( 'boldgrid_theme_framework_config', array( $this, 'partner_attribution_configs' ), 5 );
		add_filter( 'kirki/fields', array( $this, 'add_controls' ) );
		if ( $this->getLicensed() ) {
			add_filter( 'boldgrid_theme_framework_config', array( $this, 'special_thanks_configs' ), 5 );
		}
	}

	/**
	 * Adds required customizer footer configurations.
	 *
	 * @param  array $configs BGTFW Configurations.
	 * @return array $configs BGTFW Configurations.
	 */
	public function partner_attribution_configs( $configs ) {

		$configs['customizer-options']['required']['boldgrid_enable_footer'] = array_values( array_diff( $configs['customizer-options']['required']['boldgrid_enable_footer'], array( 'hide_partner_attribution' ) ) );
		$reseller = get_option( 'boldgrid_reseller', false );

		if ( $reseller && ! empty( $reseller_data['reseller_title'] ) ) {
			$configs['customizer-options']['required']['boldgrid_enable_footer'][] = 'hide_partner_attribution';
		}

		return $configs;
	}

	/**
	 * Adds required customizer footer configurations.
	 *
	 * @param  array $configs BGTFW Configurations.
	 *
	 * @return array $configs BGTFW Configurations.
	 */
	public function special_thanks_configs( $configs ) {
		$configs['customizer-options']['required']['boldgrid_enable_footer'][] = 'hide_special_thanks_attribution';
		return $configs;
	}

	/**
	 * Adds attribution link controls to theme customizer.
	 *
	 * @param array $controls [description]
	 */
	public function add_controls( $controls ) {
		$controls = array_merge( $controls, $this->getControls() );

		if ( ! get_option( 'boldgrid_reseller', false ) ) {
			unset( $controls['reseller_control'] );
		}
		if ( ! $this->getLicensed() ) {
			unset( $controls['special_thanks_control'] );
		}

		return $controls;
	}

	/**
	 * Create the attribution link for reseller affiliations.
	 *
	 * @since 1.4.3
	 *
	 * @param string $link Attribution markup to add to footer links.
	 *
	 * @return string $link Markup to add.
	 */
	public function add_reseller_link( $link ) {
		// If the user hasn't disabled the footer, add the links.
		if ( get_theme_mod( 'boldgrid_enable_footer', true ) ) {
			$reseller_data = get_option( 'boldgrid_reseller', false );
			// Authorized Reseller/Partner Link.
			if ( ! get_theme_mod( 'hide_partner_attribution' ) || is_customize_preview() ) {
				if ( ! empty( $reseller_data['reseller_title'] ) ) {
					$link = sprintf(
						'<span class="link reseller-attribution-link">%s <a href="%s" rel="nofollow" target="_blank">%s</a></span>',
						__( 'Support from', 'bgtfw' ),
						$reseller_data['reseller_website_url'],
						$reseller_data['reseller_title']
					);
				}
			}
		}

		return $link;
	}

	/**
	 * Create the attribution link and keep link filterable for BoldGrid Staging
	 *
	 * @since 1.4.3
	 *
	 * @param string $link Attribution markup to add to footer links.
	 *
	 * @return string $link Markup to add.
	 */
	public function add_attribution_link( $link ) {
		$attribution_data = get_option( 'boldgrid_attribution' );
		$attribution_page = get_page_by_title( 'Attribution' );
		$special_thanks = __( 'Special Thanks', 'bgtfw' );

		// If option is available use that or try to find the page by slug name.
		if ( ! empty( $attribution_data['page']['id'] ) ) {
			$link = '<a href="' . get_permalink( $attribution_data['page']['id'] ) . '">' . $special_thanks . '</a>';
		} elseif ( $attribution_page ) {
			$link = '<a href="' . get_site_url( null, 'attribution' ) . '">' . $special_thanks . '</a>';
		} else {
			$link = '';
		}

		$this->getLicensed() ? : set_theme_mod( 'hide_special_thanks_attribution', false );
		$value = get_theme_mod( 'hide_special_thanks_attribution', false );

		$value = $value ? '<span class="link special-thanks-attribution-link hidden">' . $link . '</span>' :
			'<span class="link special-thanks-attribution-link">' . $link . '</span>';

		$link = $value;
		if ( ! get_theme_mod( 'boldgrid_enable_footer', true ) && $this->getLicensed() ) {
			$link = '';
		} else {
			$link = $value;
		}

		return $link;
	}

	/**
	 * Gets $licensed class property.
	 *
	 * @return bool $licensed Licensed Plugin?
	 */
	public function getLicensed() {
		return $this->licensed;
	}

	/**
	 * Gets $controls class property.
	 *
	 * @return array $controls Controls array.
	 */
	public function getControls() {
		return $this->controls;
	}
}