<?php
/**
 * File: boldgrid-inspirations/includes/class-boldgrid-inspirations-onboarding-progress.php
 *
 * @package   Boldgrid_Inspirations_Onboarding_Progress
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Onboarding Progress class.
 *
 * This class is responsible for calculating the progress of
 * the onboarding process. Additionally, this class is responsible
 * updating the status of tasks as they are completed.
 *
 * @since 2.8.0
 */
class Boldgrid_Inspirations_Onboarding_Progress {
	/**
	 * The onboarding tasks option name.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $tasks_option_name;

	/**
	 * The onboarding progress option name.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $progress_option_name;

	/**
	 * Inspirations Configs
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
	 */
	public function __construct( $configs ) {
		$this->configs              = $configs;
		$this->tasks_option_name    = $this->configs['onboarding_tasks_option'];
		$this->progress_option_name = $this->configs['onboarding_progress_option'];
	}

	/**
	 * Add Admin Ajax hooks.
	 *
	 * @since 2.8.0
	 */
	public function add_ajax_hooks() {
		add_action( 'wp_ajax_boldgrid_inspirations_update_task', array( $this, 'ajax_update_task' ) );
	}

	/**
	 * Get Tasks.
	 * 
	 * @since 2.8.0
	 * 
	 * @return array The tasks.
	 */
	public function get_tasks() {
		$tasks = get_option( $this->tasks_option_name );

		return is_array( $tasks ) ? $tasks : array();
	}

	/**
	 * Get Task.
	 *
	 * @since 2.8.0
	 *
	 * @param string $task_id The task ID.
	 *
	 * @return array The task object.
	 */
	public function get_task( $task_id ) {
		$tasks = $this->get_tasks();

		foreach ( $tasks as $task ) {
			if ( $task['id'] === $task_id ) {
				return $task;
			}
		}

		return false;
	}

	/**
	 * Update Task.
	 *
	 * @since 2.8.0
	 *
	 * @param array $task The task object.
	 */
	public function update_task( $task ) {
		$tasks = $this->get_tasks();

		foreach ( $tasks as $key => $task_data ) {
			if ( $task_data['id'] === $task['id'] ) {
				$tasks[ $key ] = $task;
			}
		}

		if ( ! empty( $tasks ) ) {
			update_option( $this->tasks_option_name, $tasks );

			$this->update_percent_complete();
		}
	}

	/**
	 * Update Percent Complete.
	 *
	 * @since 2.8.0
	 */
	public function update_percent_complete() {
		$tasks = $this->get_tasks();

		$complete = 0;
		$total    = count( $tasks );

		// Prevents a divide by zero if there are no tasks.
		if ( 0 === $total ) {
			return;
		}

		foreach ( $tasks as $task ) {
			if ( $task['task_complete'] ) {
				$complete++;
			}
		}

		$percent_complete = ( $total > 0 ) ? $complete / $total : 0;

		if ( get_option( $this->progress_option_name ) !== $percent_complete ) {
			do_action( 'boldgrid_feedback_add', 'onboarding_task_completed', $percent_complete, true );
			update_option( $this->progress_option_name, $percent_complete );
		}
	}

	/**
	 * Ajax callback to update the status of a task.
	 *
	 * @since 2.8.0
	 */
	public function ajax_update_task() {
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'missing_nonce' );
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'boldgrid_inspirations_update_task' ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'invalid_permissions' );
		}

		$task_id = isset( $_POST['task_id'] ) ? sanitize_text_field( wp_unslash( $_POST['task_id'] ) ) : '';

		if ( 'skip_all_tasks' === $task_id ) {
			$this->skip_all_tasks();
		}

		$task = $this->get_task( $task_id );

		if ( ! $task ) {
			wp_send_json_error( 'invalid or missing task_id' );
		}

		$task['task_complete'] = isset( $_POST['task_status'] ) ? (bool) sanitize_text_field( wp_unslash( $_POST['task_status'] ) ) : ! $task['task_complete'];

		$this->update_task( $task );

		wp_send_json_success();
	}

	/**
	 * Skip all tasks.
	 *
	 * @since 2.8.0
	 */
	public function skip_all_tasks() {
		$tasks = $this->get_tasks();

		foreach ( $tasks as $key => $task ) {
			$task['task_complete'] = true;
			$tasks[ $key ]         = $task;
		}

		update_option( $this->tasks_option_name, $tasks );

		$this->update_percent_complete();

		do_action( 'boldgrid_feedback_add', 'onboarding_skipped_all_tasks', true, true );

		wp_send_json_success();
	}
}
