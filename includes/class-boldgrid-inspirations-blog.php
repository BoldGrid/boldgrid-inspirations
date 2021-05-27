<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Inspirations_Blog
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Blog class.
 *
 * @since 1.4
 */
class Boldgrid_Inspirations_Blog {

	/**
	 * Configs.
	 *
	 * @since 1.4
	 * @var   array
	 */
	public $configs;

	/**
	 * The page id of the blog page.
	 *
	 * @since SINCEVERSION
	 * @var int
	 */
	public $page_id;

	/**
	 * The title of our blog page.
	 *
	 * @since SINCEVERSION
	 * @var string
	 */
	public $title;

	/**
	 * Constructor.
	 *
	 * @since 1.4
	 *
	 * @param array $configs
	 */
	public function __construct( $configs = array() ) {
		$this->configs = $configs;
		$this->title   = __( 'Blog', 'boldgrid-inspirations' );
	}

	/**
	 * Create the blog menu item.
	 *
	 * @since 1.4
	 *
	 * @param int $menu_id
	 * @param int $menu_order Default value is 150. This number was previously in the code without
	 *                        any comments.
	 */
	public function create_menu_item( $menu_id, $menu_order = 150 ) {
		$data = array(
			'menu-item-object-id' => $this->page_id,
			'menu-item-parent-id' => 0,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $menu_order,
		);

		return wp_update_nav_menu_item( $menu_id, 0, $data );
	}

	/**
	 * Create our blog page.
	 *
	 * @since SINCEVERSION
	 */
	public function create_page() {
		$page = get_page_by_title( $this->title );

		if ( ! empty( $page->post_status ) && 'published' === $page->post_status ) {
			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post( array(
				'post_title'     => $this->title,
				'post_name'      => sanitize_key( $this->title ),
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			) );
		}

		$this->page_id = (int) $page_id;

		return ! empty( $this->page_id );
	}

	/**
	 * Create widgets.
	 *
	 * During an Inspirations Deployment, if we are installing a blog, create a set of widgets and
	 * add them to the sidebar.
	 *
	 * @since 1.4
	 */
	public function create_sidebar_widgets() {
		/*
		 * Set our sidebar id.
		 *
		 * With v1 themes it used to be 'sidebar-1'. As Inspirations has transitioned to installing
		 * Crio themes, the sidebar is now 'primary-sidebar'.
		 */
		$theme   = wp_get_theme();
		$sidebar = 'Crio' === $theme->get( 'Name' ) ? 'primary-sidebar' : 'sidebar-1';

		/**
		 * Filter the sidebar to add our new widgets to.
		 *
		 * Not all themes have a 'sidebar-1'.
		 *
		 * @since 1.4
		 *
		 * @param string $sidebar.
		 */
		$sidebar = apply_filters( 'boldgrid_deploy_blog_sidebar', $sidebar );

		$widgets_to_create = $this->configs[ 'new_blog_widgets' ];

		/**
		 * Filter the widgets that we will create.
		 *
		 * @since 1.4
		 *
		 * @param array $widgets_to_create An array of widgets.
		 */
		$widgets_to_create = apply_filters( 'boldgrid_deploy_blog_widgets', $widgets_to_create );

		/*
		 * Empty the sidebar before we start adding widgets to it, otherwise we will end up with
		 * duplicate items in the sidebar after more than one deployment.
		 */
		Boldgrid_Inspirations_Widget::empty_sidebar( $sidebar );

		foreach( $widgets_to_create as $widget ) {
			$key = Boldgrid_Inspirations_Widget::create_widget( $widget['type'], $widget['value'] );

			Boldgrid_Inspirations_Widget::add_to_sidebars( $sidebar, $widget['type'] . '-' . $key );
		}
	}
}
