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
	 * Configs
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	var $configs;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param array $configs The configs.
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
	}

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

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-onboarding-progress.php';
		$progress = new Boldgrid_Inspirations_Onboarding_Progress( $this->configs );
		$progress->add_ajax_hooks();
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
			// 'null' so "My Inspiration" does not show as a menu item.
			'null',
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			__( 'My Inspiration', 'boldgrid-inspirations' ),
			'manage_options',
			'my-inspiration',
			array( $this, 'page' )
		);
	}

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
		require_once BOLDGRID_BASE_DIR . '/includes/class-boldgrid-inspirations-onboarding-tasks.php';
		require_once BOLDGRID_BASE_DIR . '/includes/onboarding/class-task-card.php';
		require_once BOLDGRID_BASE_DIR . '/includes/onboarding/class-task.php';

		$onboarding = new Boldgrid_Inspirations_Onboarding_Tasks( $this->configs );
		$cards_data = $onboarding->get_cards_data();

		$nonce = wp_create_nonce( 'boldgrid_inspirations_update_task' );

		$initial_progress = get_option( $this->configs['onboarding_progress_option'], 0 );

		$formatted_progress = sprintf( '%.0f', (float) $initial_progress * 100 );

		$theme          = wp_get_theme();
		$screenshot_url = get_option( 'boldgrid_site_screenshot', $theme->get_screenshot() );

		?>
		<div class="inspirations-banner" style="background-color: rgb(34, 113, 177); color: rgb(255, 255, 255);">
			<div class="theme-screenshot">
				<img src="<?php echo esc_url( $screenshot_url ); ?>">
			</div>
			<div class="inspirations-banner__title">
				<h1>BoldGrid Inspirations</h1>
				<p>Congratulations on your new website! Sometimes it can be overwhelming deciding what to do next, so we've prepared a list of tasks you can do to get the most out of Crio
					and Post and Page Builder. Some of them have already been completed for you! If you've already finished a task, or you wish to skip it, you can mark it complete by
					clicking on the checkbox to the left of the task.</p>
				</p>
			</div>
			<div class="progress">
				<h1> Progress</h1>
				<div class="onboarding-progress-bar" role="progressbar" 
					aria-valuenow="<?php echo esc_attr( $formatted_progress ); ?>" aria-valuemin="0" aria-valuemax="100"
					style="--percent-complete:<?php echo esc_attr( $formatted_progress . '%' ); ?>">
					<span class="percent-complete"><?php echo esc_html( $formatted_progress ); ?>%</span>
				</div>
			</div>
		</div>
		<div class="onboarding-cards">
		<div class="onboarding-nonce" style="display: none" data-nonce="<?php echo esc_attr( $nonce ); ?>"></div>
		<?php
		foreach ( $cards_data as $card_data ) {
			$card = new BoldGrid\Inspirations\Onboarding\Task_Card(
				$card_data['id'],
				$card_data['title'],
				$card_data['description'],
				$card_data['colors'],
				$card_data['icon'],
				$card_data['tasks']
			);

			echo wp_kses_post( $card->render() );
		}
		?>
		</div>
		<div id="card-support" class="boldgrid-onboarding-card full-width" style="--card-color: #079f07;">
				<div class="boldgrid-onboarding-card-title">
					<p><?php esc_html_e( 'Get More Help', 'boldgrid-inspirations' ); ?></p>
					<div class="boldgrid-onboarding-card-description">
					<?php
						esc_html_e( 'BoldGrid has multiple avenues of support available', 'boldgrid-inspirations' );
					?>
					</div>
				</div>
				<div class="boldgrid-support-card-tasks">
					<div class="boldgrid-support-card">
						<div class="boldgrid-support-card-icon">
							<span class="dashicons boldgrid-icon"></span>
						</div>
						<div class="boldgrid-support-card-contents">
							<div class="task-title"><?php esc_html_e( 'Support Center', 'boldgrid-inspirations' ); ?></div>
							<div class="task-description">
								<?php
									esc_html_e(
										'We have a large collection of support articles, videos, and more to help you learn everything you need to make the most out of your site',
										'boldgrid-inspirations'
									);
								?>
							</div>
							
						</div>
						<div class="task-buttons">
							<a href="https://www.boldgrid.com/support/" class="button button-secondary" target="_blank">
								<?php esc_html_e( 'Learn More', 'boldgrid-inspirations' ); ?>
							</a>
						</div>
					</div>
					<div class="boldgrid-support-card">
						<div class="boldgrid-support-card-icon">
							<span class="dashicons dashicons-admin-comments"></span>
						</div>
						<div class="boldgrid-support-card-contents">
							<div class="task-title"><?php esc_html_e( 'BoldGrid Forums', 'boldgrid-inspirations' ); ?></div>
							<div class="task-description">
								<?php
									esc_html_e(
										'You can also submit a question to the BoldGrid Support Forums',
										'boldgrid-inspirations'
									);
								?>
							</div>
						</div>
						<div class="task-buttons">
								<a href="https://www.boldgrid.com/support/ask-a-question/" class="button button-secondary" target="_blank">
									<?php esc_html_e( 'Learn More', 'boldgrid-inspirations' ); ?>
								</a>
							</div>
					</div>
					<div class="boldgrid-support-card">
						<div class="boldgrid-support-card-icon">
							<span class="dashicons dashicons-format-chat"></span>
						</div>
						<div class="boldgrid-support-card-contents">
							<div class="task-title"><?php esc_html_e( 'Premium Support', 'boldgrid-inspirations' ); ?></div>
							<div class="task-description">
								<?php
									esc_html_e(
										'As a premium user, you can contact us directly for help with your site',
										'boldgrid-inspirations'
									);
								?>
							</div>
						</div>
						<div class="task-buttons">
							<a href="https://www.boldgrid.com/central/account/help/premium" class="button button-secondary" target="_blank">
								<?php esc_html_e( 'Learn More', 'boldgrid-inspirations' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
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
