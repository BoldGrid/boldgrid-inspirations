<?php
// Prevent direct calls.
require BOLDGRID_BASE_DIR . '/pages/templates/restrict-direct-access.php';

return array(
	'onboarding_tasks_option'    => 'boldgrid_onboarding_tasks',
	'onboarding_progress_option' => 'boldgrid_onboarding_progress',
	/**
	 * Each available onboarding task should be listed here
	 * with the following properties:
	 * - id: The unique ID of the task.
	 * - title: The title of the task.
	 * - description: The description of the task.
	 * - card_id: The ID of the card to display the task on.
	 * - links (optional): An array of links to display on the task. Each link should have the following properties:
	 *          - text: The text of the link.
	 *          - url: The URL of the link.
	 *          - class (optional): String of classes to add to link.
	 * - buttons (optional): An array of buttons to display on the task. Each button should have the following properties:
	 *          - text: The text of the button.
	 *          - url: The URL of the button.
	 *          - class (optional): String of classes to add to button.
	 * - active_callback (optional): The callback function to run to determine if the task is active.
	 *                               If not provided, the task will always be active.
	 *
	 * Note: Callback methods must be a valid callable function accepting one parameter,
	 *       which will be an array of the install options.
	 *       This can be either an anonymous function, an array of a class and method,
	 *       or a string, with the name of a method belonging to the
	 *       Boldgrid_Inspirations_Onboarding_Tasks class.
	 */
	'available_onboarding_tasks' => array(
		array(
			'id'          => 'color_palette',
			'title'       => __( 'Choose Your Color Palette', 'boldgrid-inspirations' ),
			'description' => __(
				'One of the most noticeable things about a website is its color palette. Here, you can edit individual colors used in your palette, or choose a whole new palette',
				'boldgrid-inspirations'
			),
			'card_id'     => 'customize_theme',
			'links'       => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-the-color-palette/',
				),
			),
			'buttons'      => array(
				array(
					'text'  => __( 'Edit Palette', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[section]', 'colors', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary',
				),
			),
		),
		array(
			'id'          => 'menus',
			'title'       => __( 'Customize Menu Designs', 'boldgrid-inspirations' ),
			'description' => __(
				'How people navigate your site is important. Here, you can customize the appearance of your menus and the menu items. Choose your menu fonts, colors, spacing, hover effects, and more.',
				'boldgrid-inspirations'
			),
			'card_id'     => 'customize_theme',
			'links'       => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-your-new-crio-website/',
				),
			),
			'buttons'     => array(
				array(
					'text'  => __( 'Customize Main Menu', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[panel]', 'bgtfw_menu_items_main', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary',
				),
			),
		),
		array(
			'id'              => 'customizer_headers',
			'title'           => __( 'Headers and Footers', 'boldgrid-inspirations' ),
			'description'     => __(
				'The design of your headers and footers can make a big difference in the overall look of your site. Here, you can customize their appearance by changing ',
				'boldgrid-inspirations'
			),
			'active_callback' => function( $install_options ) {
				return isset( $install_options['theme_has_cph'] ) && true === $install_options['theme_has_cph'] ? false : true;
			},
			'card_id'         => 'customize_theme',
			'links'           => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-your-new-crio-website/',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Customize Headers', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[panel]', 'bgtfw_menu_items_main', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary',
				),
				array(
					'text'  => __( 'Customize Footers', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[panel]', 'bgtfw_menu_items_main', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary',
				),
			),
		),
		array(
			'id'              => 'custom_headers',
			'title'           => __( 'Headers and Footers', 'boldgrid-inspirations' ),
			'description'     => __(
				'While the decision of which headers and footers to use on which pages is configured by Crio, their actual content can be edited in the same way that posts and pages are.',
				'boldgrid-inspirations'
			),
			'active_callback' => function( $install_options ) {
				return isset( $install_options['theme_has_cph'] ) && true === $install_options['theme_has_cph'] ? true : false;
			},
			'card_id'         => 'edit_content',
			'links'           => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-your-new-crio-website/',
				),
			),
			'buttons'        => array(
				array(
					'text'  => __( 'Customize Main Menu', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[panel]', 'bgtfw_menu_items_main', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary',
				),
			),
		),
	),
);

