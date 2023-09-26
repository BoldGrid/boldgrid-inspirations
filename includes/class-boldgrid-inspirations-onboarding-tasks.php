<?php
/**
 * File: boldgrid-inspirations/includes/class-boldgrid-inspirations-onboarding-tasks.php
 *
 * @package Boldgrid_Inspirations_Onboarding_Tasks
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Inspirations Onboarding Tasks class.
 *
 * This class is responsible for determining which tasks to add to
 * the onboarding process. This class should only run once per
 * deployment, and only run when the deployment process is completed.
 *
 * @since SINCEVERSION
 */
class Boldgrid_Inspirations_Onboarding_Tasks {
	/**
	 * The onboarding tasks option name.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $option_name;

	/**
	 * Inspirations Configs
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
	 */

	public function __construct( $configs ) {
		$this->configs     = $configs;
		$this->option_name = $this->configs['onboarding_tasks_option'];
	}

	/**
	 * Create Tasks for the onboarding process.
	 *
	 * @since SINCEVERSION
	 *
	 * @param array $install_options The install options.
	 */
	public function create_tasks( $install_options ) {
		$available_tasks = $this->configs['available_onboarding_tasks'];
		$active_tasks    = array();
		$complete_tasks  = 0;

		foreach ( $available_tasks as $task ) {
			// If no active callback is provided, the task is always active.
			if ( ! isset( $task['active_callback'] ) ) {
				$active_tasks[] = array(
					'id'            => $task['id'],
					'task_complete' => $task['task_complete'],
				);
				continue;
			}

			// If the active callback is a callable function, run it.
			if ( is_callable( $task['active_callback'] ) && true === call_user_func( $task['active_callback'], $install_options ) ) {
				$active_tasks[] = array(
					'id'            => $task['id'],
					'task_complete' => $task['task_complete'],
				);
				continue;
			}

			// If the active callback is a method of this class, run it.
			if ( is_callable( array( $this, $task['active_callback'] ) ) && true === call_user_func( array( $this, $task['active_callback'] ), $install_options ) ) {
				$active_tasks[] = array(
					'id'            => $task['id'],
					'task_complete' => $task['task_complete'],
				);
				continue;
			}
		}

		foreach ( $active_tasks as $task ) {
			if ( true === $task['task_complete'] ) {
				$complete_tasks++;
			}
		}

		// Save the active tasks.
		update_option( $this->option_name, $active_tasks );
		update_option( $this->configs['onboarding_progress_option'], $complete_tasks / count( $active_tasks ) );
	}

	public function get_task( $task_id, $task_complete ) {
		$available_tasks = $this->configs['available_onboarding_tasks'];

		foreach ( $available_tasks as $task ) {
			if ( $task['id'] === $task_id ) {
				$task['task_complete'] = $task_complete;
				return $task;
			}
		}

		return array();
	}

	/**
	 * Get Cards Data
	 *
	 * @since SINCEVERSION
	 *
	 * @return array An array of cards and their tasks.
	 */
	public function get_cards_data() {
		$available_cards = $this->configs['available_onboarding_cards'];

		$active_cards = array();

		foreach ( $available_cards as $card ) {
			$tasks         = $this->get_tasks_for_card( $card['id'] );
			$card['tasks'] = $tasks;
			if ( ! empty( $tasks ) ) {
				$active_cards[] = $card;
			}
		}

		return $active_cards;
	}

	/**
	 * Get Tasks for card
	 *
	 * @since SINCEVERSION
	 *
	 * @param string $card_id The card ID.
	 *
	 * @return array The tasks for the card.
	 */
	public function get_tasks_for_card( $card_id ) {
		$active_tasks = get_option( $this->option_name );

		if ( ! is_array( $active_tasks ) ) {
			return array();
		}

		$tasks_for_card = array();

		foreach ( $active_tasks as $active_task ) {
			$task = $this->get_task( $active_task['id'], $active_task['task_complete'] );
			if ( $task['card_id'] === $card_id ) {
				$tasks_for_card[] = $task;
			}
		}

		return $tasks_for_card;
	}

	public function get_support_tasks_data() {
		return $this->configs['available_support_tasks'];
	}

	/**
	 * Has Legacy Headers
	 *
	 * @since SINCEVERSION
	 *
	 * @param array $install_options
	 *
	 * @return boolean True if the theme has legacy headers, false otherwise.
	 */
	public function has_legacy_headers( $install_options ) {
		return isset( $install_options['theme_has_cph'] ) && true === $install_options['theme_has_cph'] ? false : true;
	}

	/**
	 * Has Custom Headers
	 *
	 * @since SINCEVERSION
	 *
	 * @param array $install_options
	 *
	 * @return boolean True if the theme has custom headers, false otherwise.
	 */
	public function has_custom_headers( $install_options ) {
		return isset( $install_options['theme_has_cph'] ) && true === $install_options['theme_has_cph'] ? true : false;
	}
}
