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
class Task {
	/**
	 * The task ID.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $id;

	/**
	 * The task title.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $title;

	/**
	 * The task description.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $description;

	/**
	 * The task card ID.
	 *
	 * @since SINCEVERSION
	 *
	 * @var string
	 */
	var $card_id;

	/**
	 * The task links.
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	var $links;

	/**
	 * The task buttons.
	 *
	 * @since SINCEVERSION
	 *
	 * @var array
	 */
	var $buttons;

	/**
	 * Task Complete
	 *
	 * @since SINCEVERSION
	 *
	 * @var bool
	 */
	var $complete = false;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param string $id            The task ID.
	 * @param string $title         The task title.
	 * @param string $description   The task description.
	 * @param bool   $task_complete The task complete status.
	 * @param string $card_id       The task card ID.
	 * @param array  $links         The task links.
	 * @param array  $buttons       The task buttons.
	 */
	public function __construct( $id, $title, $description, $card_id, $task_complete, $links, $buttons ) {
		$this->id          = $id;
		$this->title       = $title;
		$this->description = $description;
		$this->card_id     = $card_id;
		$this->links       = $links;
		$this->buttons     = $buttons;
	}

	/**
	 * Render
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task markup.
	 */
	public function render() {
		$markup  = '<div class="boldgrid-onboarding-task" id="task-' . esc_attr( $this->id ) . '">';
		$markup .= $this->render_checkbox();
		$markup .= '<div class="task-content">';
		$markup .= $this->render_title();
		$markup .= $this->render_description();
		$markup .= '</div>';
		$markup .= $this->render_links();
		$markup .= $this->render_buttons();
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task checkbox.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task checkbox markup.
	 */
	public function render_checkbox() {
		$icon_class = $this->task_complete ? 'dashicons-yes' : 'dashicons-marker';

		$markup  = '<div class="boldgrid-onboarding-task-checkbox">';
		$markup .= '<span class="dashicons ' . esc_attr( $icon_class ) . '"></span>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task title.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task title markup.
	 */
	public function render_title() {
		return '<div class="task-title">' . esc_html( $this->title ) . '</div>';
	}

	/**
	 * Render the task description.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task description markup.
	 */
	public function render_description() {
		return '<div class="task-description">' . wp_kses_post( $this->description ) . '</div>';
	}

	/**
	 * Render the task links.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task links markup.
	 */
	public function render_links() {
		if ( empty( $this->links ) ) {
			return '';
		}

		$markup  = '<div class="task-links">';
		$markup .= '<ul>';

		foreach ( $this->links as $link ) {
			$markup .= '<li>';
			$markup .= '<a href="' . esc_url( $link['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link['text'] ) . '</a>';
			$markup .= '</li>';
		}

		$markup .= '</ul>';
		$markup .= '</div>';
	}

	/**
	 * Render the task buttons.
	 *
	 * @since SINCEVERSION
	 *
	 * @return string The rendered task buttons markup.
	 */
	public function render_buttons() {
		if ( empty( $this->buttons ) ) {
			return '';
		}

		$markup  = '<div class="task-buttons">';
		$markup .= '<ul>';

		foreach ( $this->buttons as $button ) {
			$markup .= '<li>';
			$markup .= '<a href="' . esc_url( $button['url'] ) . '" class="button ' . esc_attr( $button['class'] ) . '">' . esc_html( $button['text'] ) . '</a>';
			$markup .= '</li>';
		}

		$markup .= '</ul>';
		$markup .= '</div>';

		return $markup;
	}
}
