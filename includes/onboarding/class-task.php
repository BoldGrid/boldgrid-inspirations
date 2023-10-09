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
class Task {
	/**
	 * The task ID.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $id;

	/**
	 * The task title.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $title;

	/**
	 * The task description.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $description;

	/**
	 * The task card ID.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	var $card_id;

	/**
	 * The task links.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	var $links;

	/**
	 * The task buttons.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	var $buttons;

	/**
	 * Task Complete
	 *
	 * @since 2.8.0
	 *
	 * @var bool
	 */
	var $task_complete = false;

	/**
	 * Constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param string $id            The task ID.
	 * @param string $title         The task title.
	 * @param string $description   The task description.
	 * @param bool   $task_complete The task complete status.
	 * @param string $card_id       The task card ID.
	 * @param array  $links         The task links.
	 * @param array  $buttons       The task buttons.
	 */
	public function __construct( $id, $title, $description, $card_id, $links, $buttons, $task_complete = null, $icon = null ) {
		$this->id            = $id;
		$this->title         = $title;
		$this->description   = $description;
		$this->card_id       = $card_id;
		$this->links         = $links;
		$this->icon          = $icon;
		$this->buttons       = $buttons;
		$this->task_complete = $task_complete;
	}

	/**
	 * Render
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered task markup.
	 */
	public function render() {
		$complete = $this->task_complete ? 'complete collapsed' : '';

		$markup  = '<div class="boldgrid-onboarding-task ' . esc_attr( $complete ) . '" id="' . esc_attr( $this->id ) . '">';
		$markup .= $this->render_checkbox();
		$markup .= $this->render_icon();
		$markup .= '<div class="task-content">';
		$markup .= $this->render_title();
		$markup .= $this->render_description();
		$markup .= $this->render_links();
		$markup .= $this->render_buttons();
		$markup .= '</div>';
		$markup .= '<div class="collapse-expand"><span class="dashicons"></div>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task checkbox.
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered task checkbox markup.
	 */
	public function render_checkbox() {
		if ( null === $this->task_complete ) {
			return '';
		}

		$markup  = '<div class="boldgrid-onboarding-task-checkbox">';
		$markup .= '<span class="dashicons"></span>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task icon.
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered task icon markup.
	 */
	public function render_icon() {
		if ( empty( $this->icon ) ) {
			return '';
		}

		$markup  = '<div class="task-icon">';
		$markup .= '<span class="dashicons ' . esc_attr( $this->icon ) . '"></span>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task title.
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered task title markup.
	 */
	public function render_title() {
		return '<div class="task-title">' . esc_html( $this->title ) . '</div>';
	}

	/**
	 * Render the task description.
	 *
	 * @since 2.8.0
	 *
	 * @return string The rendered task description markup.
	 */
	public function render_description() {
		return '<div class="task-description">' . wp_kses_post( $this->description ) . '</div>';
	}

	/**
	 * Render the task links.
	 *
	 * @since 2.8.0
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
			$markup .= '<li>
				<a href="' . esc_url( $link['url'] ) . '" target="_blank" rel="noopener noreferrer">' .
				esc_html( $link['text'] ) . ' </a></li>';
		}

		$markup .= '</ul>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Render the task buttons.
	 *
	 * @since 2.8.0
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
			$markup .= '<a href="' . esc_url( $button['url'] ) . '" ';
			$markup .= 'class="button ' . esc_attr( isset( $button['class'] ) ? $button['class'] : '' ) . '" ';
			$markup .= 'target="' . esc_attr( isset( $button['target'] ) ? $button['target'] : '_self' ) . '">';
			$markup .= esc_html( $button['text'] );
			$markup .= isset( $button['target'] ) && '_blank' === $button['target'] ? ' <span class="dashicons dashicons-external"></span>' : '';
			$markup .= '</a>';
			$markup .= '</li>';
		}

		$markup .= '</ul>';
		$markup .= '</div>';

		return $markup;
	}
}
