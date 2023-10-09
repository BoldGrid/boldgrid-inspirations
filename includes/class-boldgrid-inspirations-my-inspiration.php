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
	 * @since 2.8.0
	 *
	 * @var array
	 */
	var $configs;

	/**
	 * Constructor.
	 *
	 * @since 2.8.0
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
	 * Render the header.
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered HTML.
	 */
	public function render_header() {
		$theme              = wp_get_theme();
		$screenshot_url     = get_option( 'boldgrid_site_screenshot', $theme->get_screenshot() );
		$initial_progress   = get_option( $this->configs['onboarding_progress_option'], 0 );
		$formatted_progress = sprintf( '%.0f', (float) $initial_progress * 100 );
		$nonce              = wp_create_nonce( 'boldgrid_inspirations_update_task' );
		$complete           = '100' === $formatted_progress ? true : false;
		$instructions       = __(
			'Congratulations on your new website! Sometimes it can be overwhelming deciding what to do next, so we\'ve prepared a list of tasks you can do to get the most out of Crio
			and Post and Page Builder. Some of them have already been completed for you! If you\'ve already finished a task, or you wish to skip it, you can mark it complete by
			clicking on the checkbox to the left of the task. Additionally, if you wish to skip all the tasks and mark them all as complete, you can click the button below.',
			'boldgrid-inspirations'
		);
		$completion         = __(
			'You\'ve completed all of the tasks! If you\'d like to see other things you can do, you can view the “Get More Help” section below.',
			'boldgrid-inspirations'
		);

		echo '<div class="my-inspirations-header">
			<div class="my-inspirations-container">
				<div class="theme-screenshot">
					<img src="' . esc_url( $screenshot_url ) . '">
				</div>
				<div class="my-inspirations-title">
					<h1>BoldGrid Inspirations</h1>
					<p class="instructions' . ( $complete ? ' hidden' : '' ) . '">' . esc_html( $instructions ) . '
						<a class="button button-secondary skip-all-tasks">' . esc_html__( 'Skip All Tasks', 'boldgrid-inspirations' ) . '</a>
					</p>
					<p class="completion' . ( $complete ? '' : ' hidden' ) . '">' . esc_html( $completion ) . '</p>
				</div>
				<div class="my-inspirations-progress">
					<div class="onboarding-progress-bar" role="progressbar" 
						aria-valuenow="' . esc_attr( $formatted_progress ) . '" aria-valuemin="0" aria-valuemax="100"
						style="--percent-complete:' . esc_attr( $formatted_progress . '%' ) . '">
						<span class="percent-complete">' . esc_html( $formatted_progress ) . '%</span>
					</div>
				</div>
			</div>
		</div>
		<div class="onboarding-nonce" style="display: none" data-nonce="' . esc_attr( $nonce ) . '"></div>
		';
	}

	/**
	 * Render the cards.
	 *
	 * @since 2.8.0
	 */
	public function render_onboarding_cards( $onboarding ) {
		$cards_data = $onboarding->get_cards_data();

		echo '<div class="onboarding-cards">';

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

		echo '</div>';
	}

	/**
	 * Render the support card.
	 *
	 * @since 2.8.0
	 */
	public function render_support_card( $onboarding ) {
		$support_tasks_data = $onboarding->get_support_tasks_data();

		echo '<div class="support-cards">
			<div id="card-support" class="boldgrid-onboarding-card full-width" style="--card-color: #079f07;">
				<div class="boldgrid-onboarding-card-title">
					<p>' . esc_html__( 'Get More Help', 'boldgrid-inspirations' ) . '</p>
					<div class="boldgrid-onboarding-card-description">' .
						esc_html__( 'BoldGrid has multiple avenues of support available', 'boldgrid-inspirations' ) . '
					</div>
				</div>
			<div class="boldgrid-support-card-tasks">';

		foreach ( $support_tasks_data as $task_data ) {
			$task = new BoldGrid\Inspirations\Onboarding\Task(
				$task_data['id'],
				$task_data['title'],
				$task_data['description'],
				'',
				empty( $task_data['links'] ) ? array() : $task_data['links'],
				empty( $task_data['buttons'] ) ? array() : $task_data['buttons'],
				null,
				$task_data['icon']
			);

			echo wp_kses_post( $task->render() );
		}

		echo '</div>
			</div>
		</div>';
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

		$this->render_header();
		$this->render_onboarding_cards( $onboarding );
		$this->render_support_card( $onboarding );
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
