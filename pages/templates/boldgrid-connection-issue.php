<?php
/**
 * BoldGrid Source Code
 *
 * Partial for the BoldGrid Connection Issue notice.
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

?>
<div id="container_boldgrid_connection_notice" class="error">
	<h2 class="dashicons-before dashicons-admin-network">
		<?php esc_html_e( 'BoldGrid Connection Issue', 'boldgrid-inspirations' ); ?>
	</h2>
	<p>
		<?php
		esc_html_e(
			'There was an issue reaching the BoldGrid Connect server. Some BoldGrid features may be temporarily unavailable. Please try again in a moment.',
			'boldgrid-inspirations'
		);
		?>
	</p>
	<p>
		<?php
		printf(
			// translators: 1 The opening anchor tag to the BoldGrid Status page, 2 its closing anchor tag.
			esc_html__( 'If the issue persists, then please feel free to check our %1$sBoldGrid Status%2$s page.',
				'boldgrid-inspirations'
			),
			// @todo Update to status.boldgrid.com when that subdomain is ready.
			'<a target="_blank" href="https://www.boldgrid.com/">',
			'</a>'
		);
		?>
	</p>
</div>
