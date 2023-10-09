<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Dashboard_Widget
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * The BoldGrid Dashboard Widget class.
 *
 * @since 2.1.0
 */
class Boldgrid_Inspirations_Dashboard_Widget {
	/**
	 * Add admin hooks.
	 *
	 * @since 2.1.0
	 */
	public function add_admin_hooks() {
		add_filter( 'Boldgrid\Library\Notifications\DashboardWidget\getFeaturePlugin\boldgrid-inspirations', array( $this, 'filter_feature' ), 10, 2 );
	}

	/**
	 * Filter the Inspirations item in the dashboard widget.
	 *
	 * @since 2.1.0
	 *
	 * @param  \Boldgrid\Library\Library\Ui\Feature    The feature object.
	 * @param  \Boldgrid\Library\Library\Plugin\Plugin The plugin object.
	 */
	public function filter_feature( \Boldgrid\Library\Library\Ui\Feature $feature, \Boldgrid\Library\Library\Plugin\Plugin $plugin ) {
		$feature->icon       = '<img src="//repo.boldgrid.com/assets/icon-boldgrid-inspirations-128x128.png" />';
		$onboarding_progress = get_option( 'boldgrid_onboarding_progress' );

		if ( ! Boldgrid_Inspirations_Installed::has_built_site() ) {
			$feature->content .= '<div class="notice notice-info inline"><p>' . wp_kses(
				sprintf(
					// translators: 1 The opening anchor tag to the Inspirations page, 2 its closing tag.
					__( 'It looks like you haven\'t completed the Inspirations process yet. %1$sClick here to begin%2$s.', 'boldgrid-inspirations' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=boldgrid-inspirations' ) ) . '">',
					'</a>'
				),
				[ 'a' => [ 'href' => [] ] ]
			) . '</p></div>';
		} elseif ( 1 > $onboarding_progress ) {
			$feature->content .= '<div class="notice notice-info inline"><p>' . wp_kses(
				sprintf(
					// translators: 1 The opening anchor tag to the Inspirations page, 2 its closing tag.
					__( 'It looks like there is still more you can do with BoldGrid. %1$sClick here to continue%2$s.', 'boldgrid-inspirations' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=boldgrid-inspirations' ) ) . '">',
					'</a>'
				),
				array( 'a' => array( 'href' => array() ) )
			) . '</p></div>';
		}

		return $feature;
	}
}
