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
 * @since SINCEVERSION
 */
class Task_Card {
	/**
	 * The card ID.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $id;

	/**
	 * The card title.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $title;

	/**
	 * The card description.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $description;

	/**
	 * The card color.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $color;

	/**
	 * The card icon.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $icon;

	/**
	 * The card tasks.
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	var $tasks;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param string $id          The card ID.
	 * @param string $title       The card title.
	 * @param string $description The card description.
	 * @param string $color       The card color.
	 * @param string $icon        The card icon.
	 * @param array  $tasks       The card tasks.
	 */
	public function __construct( $id, $title, $description, $color, $icon, $tasks ) {
		$this->id          = $id;
		$this->title       = $title;
		$this->description = $description;
		$this->color       = $color;
		$this->icon        = $icon;
		$this->tasks       = $tasks;
	}

	/**
	 * Render
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered HTML.
	 */
	public function render() {
		$tasks = '';

		foreach ( $this->tasks as $task ) {
			$tasks .= $task->render();
		}

		$card = sprintf(
			'<div id="card-%1$s" class="boldgrid-onboarding-card">
				<div class="boldgrid-onboarding-card-title" style="border-bottom: 2px %2$s solid">
					<p>%3$s</p>
					<div class="boldgrid-onboarding-card-description">%4$s</div>
				</div>
				<div class="boldgrid-onboarding-card-icon">
					<span class="%5$s"></span>
				</div>
				<div class="boldgrid-onboarding-card-tasks">%6$s</div>
			</div>',
			esc_attr( $this->id ),
			esc_attr( $this->color ),
			esc_html( $this->title ),
			wp_kses_post( $this->description ),
			esc_attr( $this->icon ),
			wp_kses_post( $tasks )
		);

		return $card;
	}
}
