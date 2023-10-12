<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Inspirations\Onboarding;

/**
 * Onboarding Task class.
 *
 * @since 2.8.0
 */
class Task_Card {
	/**
	 * The card ID.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $id;

	/**
	 * The card title.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $title;

	/**
	 * The card description.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $description;

	/**
	 * The card color.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $color;

	/**
	 * The card icon.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $icon;

	/**
	 * The card tasks.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	var $tasks;

	/**
	 * Constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param string $id          The card ID.
	 * @param string $title       The card title.
	 * @param string $description The card description.
	 * @param string $color       The card color.
	 * @param string $icon        The card icon.
	 * @param array  $tasks       The card tasks.
	 */
	public function __construct( $id, $title, $description, $colors, $icon, $tasks ) {
		$this->id          = $id;
		$this->title       = $title;
		$this->description = $description;
		$this->colors      = $colors;
		$this->icon        = $icon;
		$this->tasks       = $tasks;
	}

	/**
	 * Render
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered HTML.
	 */
	public function render() {
		$tasks = '';
		foreach ( $this->tasks as $task_data ) {
			$task = new Task(
				$task_data['id'],
				$task_data['title'],
				$task_data['description'],
				$task_data['card_id'],
				empty( $task_data['links'] ) ? array() : $task_data['links'],
				empty( $task_data['buttons'] ) ? array() : $task_data['buttons'],
				$task_data['task_complete']
			);

			$tasks .= $task->render();
		}

		$card = sprintf(
			'<div id="card-%1$s" class="boldgrid-onboarding-card" style="--card-color: %2$s; --card-color-dark: %3$s">
				<div class="boldgrid-onboarding-card-title">
					<p>%4$s</p>
					<div class="boldgrid-onboarding-card-description">%5$s</div>
				</div>
				<div class="boldgrid-onboarding-card-icon">
					<span class="%6$s"></span>
				</div>
				<div class="boldgrid-onboarding-card-tasks">%7$s</div>
			</div>',
			esc_attr( $this->id ),
			esc_attr( $this->colors[0] ),
			esc_attr( $this->colors[1] ),
			esc_html( $this->title ),
			wp_kses_post( $this->description ),
			esc_attr( $this->icon ),
			wp_kses_post( $tasks )
		);

		return $card;
	}
}
