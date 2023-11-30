<?php
// Prevent direct calls.
require BOLDGRID_BASE_DIR . '/pages/templates/restrict-direct-access.php';

function boldgrid_inspirations_get_premium_url() {
	if ( ! class_exists( '\Boldgrid\Library\Library\Menu\Reseller' ) ) {
		return 'https://www.boldgrid.com/central';
	}
	$reseller = new \Boldgrid\Library\Library\Menu\Reseller();
	return method_exists( $reseller, 'getAttribute' ) ? $reseller->getAttribute( 'reseller_amp_url' ) : 'https://www.boldgrid.com/central';
}

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
	 * - task_complete: Whether or not the task is complete.
	 * - links (optional): An array of links to display on the task. Each link should have the following properties:
	 *          - text: The text of the link.
	 *          - url: The URL of the link.
	 *          - class (optional): String of classes to add to link.
	 * - buttons (optional): An array of buttons to display on the task. Each button should have the following properties:
	 *          - text: The text of the button.
	 *          - url: The URL of the button.
	 *          - class (optional): String of classes to add to button.
	 *          - target (optional): The target of the button ( default is '_self' ).
	 * - active_callback (optional): The callback function to run to determine if the task is active.
	 *                               If not provided, the task will always be active.
	 *
	 * Note: Callbacks must be a valid callable function accepting one parameter,
	 *       which will be an array of the install options.
	 *       This can be either an array of a class and method,
	 *       or a string, with the name of a method belonging to the
	 *       Boldgrid_Inspirations_Onboarding_Tasks class.
	 */
	'available_onboarding_tasks' => array(
		array(
			'id'            => 'choose_theme',
			'title'         => __( 'Choose Your Theme', 'boldgrid-inspirations' ),
			'task_complete' => true,
			'description'   => __(
				'BoldGrid Inspirations provides many different theme designs to choose from. If you\'re unhappy with the one you have chosen, you can start over again',
				'boldgrid-inspirations'
			),
			'card_id'       => 'customize_theme',
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-inspirations-plugin-product-guide/installing-your-first-boldgrid-inspiration/',
				),
			),
			'buttons'       => array(
				array(
					'text'   => __( 'Start Over', 'boldgrid-inspirations' ),
					'url'    => admin_url( 'admin.php?page=boldgrid-inspirations&force=1' ),
					'class'  => 'button-secondary',
					'target' => '_self',
				),
			),
		),
		array(
			'id'            => 'color_palette',
			'title'         => __( 'Choose Your Color Palette', 'boldgrid-inspirations' ),
			'task_complete' => false,
			'description'   => __(
				'One of the most noticeable things about a website is its color palette. Here, you can edit individual colors used in your palette, or choose a whole new palette',
				'boldgrid-inspirations'
			),
			'card_id'       => 'customize_theme',
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-the-color-palette/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Edit Palette', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[section]', 'colors', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'            => 'fonts',
			'title'         => __( 'Choose Your Fonts', 'boldgrid-inspirations' ),
			'task_complete' => false,
			'description'   => __(
				'Choosing the perfect font can really make your site stand out. Here, you can choose from a variety of fonts to use for your website.',
				'boldgrid-inspirations'
			),
			'card_id'       => 'customize_theme',
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/working-with-fonts-in-boldgrid-crio/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Choose Fonts', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[section]', 'boldgrid_typography', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'            => 'edit_homepage',
			'title'         => __( 'Edit Your Homepage', 'boldgrid-inspirations' ),
			'task_complete' => false,
			'description'   => __(
				'You can use the Post and Page Builder to customize the design of your pages. We recommend that you start with your homepage first.',
				'boldgrid-inspirations'
			),
			'card_id'       => 'edit_content',
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/page-builder/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Edit Homepage', 'boldgrid-inspirations' ),
					'url'   => add_query_arg(
						array(
							'post'   => get_option( 'page_on_front' ),
							'action' => 'edit',
						),
						admin_url( 'post.php' )
					),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'              => 'edit_blog',
			'title'           => __( 'Start Blogging', 'boldgrid-inspirations' ),
			'task_complete'   => false,
			'description'     => __(
				'You can use the Post and Page Builder to edit the included sample blog posts and to add additional posts.',
				'boldgrid-inspirations'
			),
			'active_callback' => 'has_installed_blog',
			'card_id'         => 'edit_content',
			'links'           => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/page-builder/',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Start Blogging', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'edit.php' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'              => 'customizer_headers',
			'title'           => __( 'Headers and Footers', 'boldgrid-inspirations' ),
			'description'     => __(
				'The design of your headers and footers can make a big difference in the overall look of your site. Here, you can customize their appearance by changing the layout of
				various elements, such as the logo, title, menu, and social icons.',
				'boldgrid-inspirations'
			),
			'active_callback' => 'has_legacy_headers',
			'card_id'         => 'customize_theme',
			'task_complete'   => false,
			'links'           => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-the-header-design-in-boldgrid-crio/#select-a-header-layout',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Customize Header', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[section]', 'bgtfw_header_presets', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary complete-on-click',
				),
				array(
					'text'  => __( 'Customize Footer', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[section]', 'boldgrid_footer_panel', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary complete-on-click',
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
			'active_callback' => 'has_custom_headers',
			'card_id'         => 'edit_content',
			'task_complete'   => false,
			'links'           => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/custom-header-templates/',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Edit Custom Headers & Footers', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'edit.php?post_type=crio_page_header' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'            => 'site_title_tagline',
			'title'         => __( 'Set Your Site Title and Tagline', 'boldgrid-inspirations' ),
			'description'   => __(
				'This will determine the title and tagline of your website. This is important for SEO, and can also be used in the header of your website.',
				'boldgrid-inspirations'
			),
			'card_id'       => 'general',
			'task_complete' => true,
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/working-with-your-site-title-logo-and-tagline-in-boldgrid-crio/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Edit Site Title and Tagline', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'options-general.php' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'            => 'logo',
			'title'         => __( 'Choose a Logo', 'boldgrid-inspirations' ),
			'description'   => __(
				'Here, you can upload a logo.',
				'boldgrid-inspirations'
			),
			'card_id'       => 'general',
			'task_complete' => false,
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/customizing-your-new-crio-website/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Upload Logo', 'boldgrid-inspirations' ),
					'url'   => add_query_arg( 'autofocus[control]', 'custom_logo', admin_url( 'customize.php' ) ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'            => 'menu_items',
			'title'         => __( 'Edit Menu Items', 'boldgrid-inspirations' ),
			'description'   => __(
				'Your menu items are how visitors will navigate your website. We\'ve already set some up for you, but you may want to add more if you add more pages or posts.',
				'boldgrid-inspirations'
			),
			'card_id'       => 'general',
			'task_complete' => false,
			'links'         => array(
				array(
					'text' => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/working-with-menus-in-boldgrid-crio/',
				),
			),
			'buttons'       => array(
				array(
					'text'  => __( 'Edit Menu Items', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'nav-menus.php' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'              => 'sprout_invoices',
			'title'           => __( 'Sprout Invoices', 'boldgrid-inspirations' ),
			'description'     => __(
				'With Sprout Invoices, you can create beautiful estimates and invoices for your clients in minutes, and get paid easily.',
				'boldgrid-inspirations'
			),
			'card_id'         => 'general',
			'task_complete'   => false,
			'active_callback' => 'has_installed_invoice',
			'links'           => array(
				array(
					'text' => __( 'Getting Started with Sprout Invoices', 'boldgrid-inspirations' ),
					'url'  => 'https://docs.sproutinvoices.com/article/5-getting-started-sprout-invoices',
				),
				array(
					'text' => __( 'Sprout Invoices + weForms Integration', 'boldgrid-inspirations' ),
					'url'  => 'https://docs.sproutinvoices.com/article/263-weforms-integration',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Get Started', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'admin.php?page=sprout-invoices' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
		array(
			'id'              => 'w3tc',
			'title'           => __( 'W3 Total Cache', 'boldgrid-inspirations' ),
			'description'     => __(
				'W3 Total Cache speeds up your WordPress website by reducing its download time, which makes your page load extremely fast.',
				'boldgrid-inspirations'
			),
			'card_id'         => 'general',
			'task_complete'   => false,
			'active_callback' => 'has_installed_cache',
			'links'           => array(
				array(
					'text' => __( 'W3 Total Cache Tutorials', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/w3-total-cache/',
				),
				array(
					'text' => __( 'How to configure W3 Total Cache', 'boldgrid-inspirations' ),
					'url'  => 'https://www.boldgrid.com/support/w3-total-cache/configuring-w3-total-cache-for-wordpress-with-shared-hosting/',
				),
			),
			'buttons'         => array(
				array(
					'text'  => __( 'Get Started', 'boldgrid-inspirations' ),
					'url'   => admin_url( 'admin.php?page=w3tc_setup_guide' ),
					'class' => 'button-secondary complete-on-click',
				),
			),
		),
	),
	/**
	 * Each available onboarding cards should be listed here
	 * with the following properties:
	 * - id: The unique ID of the card.
	 * - title: The title of the card.
	 * - description: The description of the card.
	 * - colors: An array of colors to use for the card.
	 *           - Primary color
	 *           - Secondary color
	 * - icon: The icons class to use for the card.
	 */
	'available_onboarding_cards' => array(
		array(
			'id'          => 'customize_theme',
			'title'       => __( 'Customize Crio', 'boldgrid-inspirations' ),
			'description' => __( 'Customizing your theme affects how your website appears across the entire site.', 'boldgrid-inspirations' ),
			'colors'      => array( '#f85a25', '#cb3706' ),
			'icon'        => 'dashicons dashicons-admin-appearance',
		),
		array(
			'id'          => 'edit_content',
			'title'       => __( 'Edit Your Site Content', 'boldgrid-inspirations' ),
			'description' => __( 'Your chosen Inspiration comes with a lot of prebuilt content, but you will likely need to edit the content to make it unique to your website.', 'boldgrid-inspirations' ),
			'colors'      => array( '#0073a8', '#005075' ),
			'icon'        => 'dashicons dashicons-edit',
		),
		array(
			'id'          => 'general',
			'title'       => __( 'General Site Settings', 'boldgrid-inspirations' ),
			'description' => __( 'These tasks are general settings that are configured independently of your theme or content.', 'boldgrid-inspirations' ),
			'colors'      => array( '#aa0073', '#aa0073' ),
			'icon'        => 'dashicons dashicons-admin-generic',
		),
	),
	/**
	 * Each support task should be listed here
	 * with the following properties:
	 * - id: The unique ID of the card.
	 * - title: The title of the card.
	 * - description: The description of the card.
	 * - icon: The icons class to use for the card.
	 * - buttons (optional): An array of buttons to display on the task. Each button should have the following properties:
	 *          - text: The text of the button.
	 *          - url: The URL of the button.
	 *          - class (optional): String of classes to add to button.
	 *          - target (optional): The target of the button ( default is '_blank' ).
	 */
	'available_support_tasks'    => array(
		array(
			'id'          => 'support_center',
			'title'       => __( 'Support Center', 'boldgrid-inspirations' ),
			'description' => __(
				'We have a large collection of support articles, videos, and more to help you learn everything you need to make the most out of your site.',
				'boldgrid-inspirations'
			),
			'icon'        => 'dashicons boldgrid-icon',
			'buttons'     => array(
				array(
					'text'   => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'    => 'https://www.boldgrid.com/support/',
					'class'  => 'button-secondary',
					'target' => '_blank',
				),
			),
		),
		array(
			'id'          => 'boldgrid_forums',
			'title'       => __( 'BoldGrid Forums', 'boldgrid-inspirations' ),
			'description' => __(
				'You can also submit a question to the BoldGrid Support Forums',
				'boldgrid-inspirations'
			),
			'icon'        => 'dashicons dashicons-admin-comments',
			'buttons'     => array(
				array(
					'text'   => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'    => 'https://www.boldgrid.com/support/ask-a-question/',
					'class'  => 'button-secondary',
					'target' => '_blank',
				),
			),
		),
		array(
			'id'          => 'premium_user_support',
			'title'       => __( 'Premium User Support', 'boldgrid-inspirations' ),
			'icon'        => 'dashicons dashicons-format-chat',
			'description' => __(
				'Premium users have access to our Premium Support Team. They are available to help you with any issues you may have.',
				'boldgrid-inspirations'
			),
			'buttons'     => array(
				array(
					'text'   => __( 'Learn More', 'boldgrid-inspirations' ),
					'url'    => boldgrid_inspirations_get_premium_url(),
					'class'  => 'button-secondary',
					'target' => '_blank',
				),
			),
		),
	),
);

