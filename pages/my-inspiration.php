<?php
/**
 * My Inspiration page.
 *
 * This file used to render the My Inspirations page.
 *
 * This file is included by class-boldgrid-inspirations-my-inspiration.php
 *
 * @since 1.7.0
 */

// Prevent direct calls.
require BOLDGRID_BASE_DIR . '/pages/templates/restrict-direct-access.php';

// Used to save closed meta boxes and their order.
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>

<div class='wrap'>

	<h1><?php echo esc_html__( 'My Inspiration', 'boldgrid-inspirations' ); ?></h1>

	<?php if ( ! empty( $_GET['new_inspiration'] ) ) { ?>
		<div class="notice notice-success is-dismissible">
			<p>
				&#10003; <?php esc_html_e( 'Inspirations Installed Successfully!', 'boldgrid-inspirations' ); ?>
			</p>
		</div>
	<?php } ?>

	<div>

		<div id="post-body" class="metabox-holder columns-<?php echo get_current_screen()->get_columns(); ?>">

			<div id="post-body-content"></div>

			<div id="dashboard-widgets">

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( $this->screen_id, 'container1', null ); ?>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $this->screen_id, 'container2', null ); ?>
					</div>

					<div id="postbox-container-3" class="postbox-container">
						<?php do_meta_boxes( $this->screen_id, 'container3', null ); ?>
					</div>

					<div style="clear:both;"></div>

				</div>

				<div id="postbox-container-4" class="postbox-container">
					<?php do_meta_boxes( $this->screen_id, 'container4', null ); ?>
				</div>

				<div style="clear:both;"></div>

				<div id="postbox-container-6" class="postbox-container">
					<?php do_meta_boxes( $this->screen_id, 'container6', null ); ?>
				</div>

				<div id="postbox-container-5" class="postbox-container">
					<?php do_meta_boxes( $this->screen_id, 'container5' ,null ); ?>
				</div>

			</div>

		</div>

	</div>

</div>
