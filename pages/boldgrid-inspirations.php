<?php
/**
 * This page handles displaying the Inspirations page.
 *
 * This file is included via Boldgrid_Inspirations_Built::inspiration_page().
 *
 * @param bool  $prompting_for_key Whether or not we are prompting for the api key.
 * @param array $mode_data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Configure variables.
$lang = array(
	'AddFunctionality' => __( 'Add Functionality', 'boldgrid-inspirations' ),
	'Blog'             => __( 'Blog', 'boldgrid-inspirations' ),
	'Design'           => __( 'Design', 'boldgrid-inspirations' ),
	'Content'          => __( 'Content', 'boldgrid-inspirations' ),
	'CoinBudget'       => __( 'Coin Budget', 'boldgrid-inspirations'),
	'Coins'            => __( 'Coins', 'boldgrid-inspirations' ),
	'InstallBlog'      => __( 'Install a sample blog.', 'boldgrid-inspirations' ),
	'Pageset'          => __( 'Pageset', 'boldgrid-inspirations' ),
	'Free'             => __( 'Free', 'boldgrid-inspirations' ),
	'Desktop'          => __( 'Enter desktop preview mode', 'boldgrid-inspirations' ),
	'Tablet'           => __( 'Enter tablet preview mode', 'boldgrid-inspirations' ),
	'Mobile'           => __( 'Enter mobile preview mode', 'boldgrid-inspirations' ),
	'Next'             => __( 'Next', 'boldgrid-inspirations' ),
	'Welcome'          => __( 'Welcome', 'boldgrid-inspirations' ),
);

$start_over = ! empty( $_GET['force'] );

// Whether or not we need to show the user the "Content Check Warning" screen.
$show_content_warning = ! empty( $mode_data['has_any_site'] );

$sections = array(
	'welcome' => array(
		'classes' => array(
			'screen-contained',
		),
	),
	'api-key' => array(
		'classes' => array(
			'hidden',
			'screen-contained',
		),
	),
	'content-check-warning' => array(
		'classes' => array(
			'hidden',
			'screen-contained',
		),
	),
	'design' => array(
		'classes' => array(
			'hidden',
		),
	),
	'content' => array(
		'classes' => array(
			'hidden',
		),
	),
	'contact' => array(
		'classes' => array(
			'hidden',
			'screen-contained',
		),
	),
);

/*
 * Allow the design section to load first instead of the "Welcome" section. Primarily used after
 * BoldGrid Central sends us a new key and we need to resume the Inspirations process at the design
 * step.
 */
$section = ! empty( $_GET['section'] ) ? $_GET['section'] : '';

// If we are forcing the design section first (see comment above), adjust our screens as needed.
if ( 'design' === $section ) {
	$sections['welcome']['classes'][] = 'hidden';

	switch( $show_content_warning ) {
		case true:
			$sections['content-check-warning']['classes'] = array( 'screen-contained' );
			break;
		case false:
			$sections['design']['classes'] = array();
			break;
	}
}

?>
<div class="wrap main">

	<?php require_once BOLDGRID_BASE_DIR . '/pages/includes/boldgrid-inspirations/menu.php'; ?>

	<form method="post" class="inspir-under-menu' name="post_deploy" id="post_deploy" action="admin.php?page=boldgrid-inspirations">

	<div style="clear:both;"></div>

	<div id="screen-welcome" class="<?php echo implode( ' ', $sections['welcome']['classes'] ); ?>" style="max-width:800px;">

		<div style="text-align:center; margin-bottom:24px;">

			<h1 class="bginsp-logo"><strong>BOLD</strong>GRID INSPIRATIONS</h1>

		</div>

		<div style="float:left; width:calc( 55% - 30px);">

			<p style="text-align:center;"><strong><?php echo esc_html__( 'Just 3 Simple Steps to Get Started:', 'boldgrid-inspirations' ); ?></strong>

			<div class="boldgrid-plugin-card">
				<div class="top">
					<img src="<?php echo Boldgrid_Inspirations_Utility::get_image_url( 'inspirations/features-themes-gridpic.png' ); ?>" class="step-screenshot" />
					<h2 class="circled-text circled-text-left boldgrid-orange-border">1</h2>
					<p><strong><?php echo esc_html__( 'Choose a theme design', 'boldgrid-inspirations' ); ?></strong></p>
					<div style="clear:both;"></div>
				</div>
			</div>

			<div class="boldgrid-plugin-card">
				<div class="top">
					<img src="<?php echo Boldgrid_Inspirations_Utility::get_image_url( 'inspirations/features-templates-gridpic.png' ); ?>" class="step-screenshot" />
					<h2 class="circled-text circled-text-left boldgrid-orange-border">2</h2>
					<p><strong><?php echo esc_html__( 'Choose page options', 'boldgrid-inspirations' ); ?></strong></p>
					<div style="clear:both;"></div>
				</div>
			</div>

			<div class="boldgrid-plugin-card">
				<div class="top">
					<img src="<?php echo Boldgrid_Inspirations_Utility::get_image_url( 'inspirations/contact-info-close-24ac47.png' ); ?>" class="step-screenshot" />
					<h2 class="circled-text circled-text-left boldgrid-orange-border">3</h2>
					<p><strong><?php echo esc_html__( 'Enter Essential info', 'boldgrid-inspirations' ); ?></strong></p>
					<div style="clear:both;"></div>
				</div>
			</div>

			<div class="boldgrid-plugin-card">
				<div class="top">
					<img src="<?php echo Boldgrid_Inspirations_Utility::get_image_url( 'inspirations/click.png' ); ?>" class="step-screenshot" style="padding-left:51px; padding-right:51px;" />
					<h2 class="circled-text circled-text-left boldgrid-orange-border">&#10004;</h2>
					<p><?php
						printf(
							wp_kses(
								// translators: 1 Opening span tag which will surround "Install", 2 its closing span tag.
								__( 'Click %1$sInstall%2$s and you\'re ready to customize and design with our intuitive interface.', 'boldgrid-inspirations' ),
								array( 'span' => array( 'class' => array(), ), )
							),
							'<span class="boldgrid-orange">',
							'</span>'
						);
					?></p>
					<p><?php echo esc_html__( 'Don\'t worry, if you make a mistake or change your mind, you can start over with a single click!', 'boldgrid-inspirations' ); ?></p>
					<div style="clear:both;"></div>
				</div>
			</div>

		</div>

		<div style="float:right; width:calc( 45% - 30px);">

			<p>
				<?php esc_html_e( 'Inspirations is a tool to inspire and create. Choose from a large repository of themes, use demo content as a start or make your own with the easy to use design tools. Customize to your heart\'s content and add useful plugin functionality. Publish and be proud of your new site.', 'boldgrid-inspirations' ); ?>
			</p>

			<p style="text-align:center;">
				<a class="button button-primary boldgrid-orange dashicons-before dashicons-after dashicons-arrow-right-alt"><?php echo esc_html__( 'Let\'s Get Started!', 'boldgrid-inspirations' ); ?></a>
			</p>

			<?php
			/*
			 * Recommend the Total Upkeep plugin for transferring websites.
			 *
			 * The user has landed on Inspirations, but it may be that they need to transfer a website
			 * instead of install a new one. The Total Upkeep plugin can help, and we'll recommend
			 * it below.
			 */
			if ( class_exists( '\Boldgrid\Library\Library\Plugin\Plugin' ) ) {
				$backup_plugin = new Boldgrid\Library\Library\Plugin\Plugin( 'boldgrid-backup' );
			?>
			<p style="margin-top:50px;">
				<strong><?php esc_html_e( 'Need to transfer an existing website instead?', 'boldgrid-inspirations' ); ?></strong><br />

				<?php esc_html_e( 'The Total Upkeep plugin can help transfer your WordPress website from another host in no time! ', 'boldgrid-inspirations' );

				/*
				 * Help the user get the Total Upkeep plugin.
				 *
				 * The three different conditionals below are as follows:
				 * 1. The user needs to install the plugin.
				 * 2. The user already has the plugin installed, but needs to activate it.
				 * 3. The user already has the plugin activated, and needs to go to the transfers page.
				 */
				if ( ! $backup_plugin->getIsInstalled() ) {
					$install_url = add_query_arg( 'src', 'boldgrid-inspirations', $backup_plugin->getInstallUrl() );

					echo wp_kses(
						sprintf(
							// Translators: 1 An opening em tag, 2 its closing em tag.
							__( 'Click %1$sInstall%2$s below and we\'ll install the %1$sTotal Upkeep%2$s plugin and take you to the %1$sSite Transfer%2$s wizard.', 'boldgrid-inspirations' ),
							'<em>',
							'</em>'
						),
						[ 'em' => [] ]
					);

					?><p style="text-align:center;">
						<a class="button" href="<?php echo esc_url( $install_url ); ?>">
							<?php esc_html_e( 'Install Total Upkeep', 'boldgrid-inspirations' ); ?>
						</a>
					</p><?php
				} elseif( ! $backup_plugin->isActive() ) {
					$activate_url = add_query_arg( 'src', 'boldgrid-inspirations', $backup_plugin->getActivateUrl() );

					echo wp_kses(
						sprintf(
							// Translators: 1 An opening em tag, 2 its closing em tag.
							__( 'Click %1$sActivate%2$s below and we\'ll activate the %1$sTotal Upkeep%2$s plugin and take you to the %1$sSite Transfer%2$s wizard.', 'boldgrid-inspirations' ),
							'<em>',
							'</em>'
						),
						[ 'em' => [] ]
					);

					?><p style="text-align:center;">
						<a class="button" href="<?php echo esc_url( $activate_url ); ?>">
							<?php esc_html_e( 'Activate Total Upkeep', 'boldgrid-inspirations' ); ?>
						</a>
					</p><?php
				} else {
					echo wp_kses(
						sprintf(
							__( 'Click %1$sTransfer Website Wizard%2$s below and we\'ll take you to the %1$sTotal Upkeep Site Transfer%2$s wizard.', 'boldgrid-inspirations' ),
							'<em>',
							'</em>'
						),
						[ 'em' => [] ]
					);

					?><p style="text-align:center;">
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=boldgrid-backup-transfers' ) ); ?>">
							<?php esc_html_e( 'Transfer Website Wizard', 'boldgrid-inspirations' ); ?>
						</a>
					</p><?php
				}
				?>
			</p>
			<?php } // End recommending the Total Upkeep plugin. ?>

		</div>

	</div>

<?php
if ( $prompting_for_key ) {
?>
	<div id="screen-api-key" class="<?php echo implode( ' ', $sections['api-key']['classes'] ); ?>">
	</div>
<?php
}
if ( $show_content_warning ) {
	$change_theme_verbiage = Boldgrid_Inspirations_Config::use_boldgrid_menu() ? __( 'Customize > Change Themes', 'boldgrid-inspirations' ) : __( 'Appearance > Change Themes', 'boldgrid-inspirations' );
?>
	<div id="screen-content-check-warning" class="<?php echo implode( ' ', $sections['content-check-warning']['classes'] ); ?>" style="text-align:center;">
		<h1><?php echo esc_html__( 'Content Check Warning', 'boldgrid-inspirations' ); ?></h1>
		<p><?php esc_html_e( 'We see that you have some content on your site already.  If you complete this process any existing pages and posts will be put in the "Trash" (recoverable).  Existing images will stay in your Media Library.  Your current theme will be deactivated but not removed.', 'boldgrid-inspirations' ); ?></p>
		<p id="bginsp_confirm_overwrite">
			<span><?php esc_html_e( 'Please check the box to continue.', 'boldgrid-inspirations' ); ?></span>
			<input type="checkbox" id="bginsp_overwrite" /> <label for="bginsp_overwrite"><?php esc_html_e( 'Confirm you wish to replace your current website.', 'boldgrid-inspirations' ); ?></label>
		</p>
		<a class="button button-primary next-step dashicons-before dashicons-after dashicons-arrow-right-alt"><?php echo esc_html__( 'I Understand - Continue', 'boldgrid-inspirations'); ?></a>

		<p style="margin-top:2em;">
			<?php printf(
				// translators: 1 The text, "Appearance > Change Themes".
				esc_html__( 'To change just your theme and keep your content, go to %1$s.', 'boldgrid-inspirations' ),
				$change_theme_verbiage
			); ?>
		</p>
		<a class="button button-primary dashicons-before dashicons-arrow-left-alt" href="<?php echo esc_url( admin_url( 'theme-install.php?browse=featured' ) ); ?>"><?php esc_html_e( $change_theme_verbiage, 'boldgrid-inspirations' ); ?></a>
	</div>
<?php
}
?>

	<div id="screen-design" class="<?php echo implode( ' ', $sections['design']['classes'] ); ?>">
		<div class="inspirations-mobile-toggle">
			<!-- Mobile Filter-->
				<div class="wp-filter">
					<div class="filter-count">
						<span class="count theme-count"><?php echo esc_html__( 'All', 'boldgrid-inspirations' ); ?></span>
					</div>
					<ul class="filter-links">
						<li><a href="#" data-sort="show-all" class="current"><?php echo esc_html__( 'Show All', 'boldgrid-inspirations' ); ?></a></li>
					</ul>
					<a class="drawer-toggle" href="#"><?php echo esc_html__( 'Filter Themes', 'boldgrid-inspirations' ); ?></a>
				</div>
			<!-- End of Mobile Filter-->
		</div>
		<div class="left" id="categories">
		</div>
		<div class="theme-browser rendered right">
		<div class="boldgrid-plugin-card full-width" style="margin-bottom:15px;">
			<div class="top">
				<?php echo esc_html__( 'Choose a theme to start your design. A theme sets the basics of layout, type and colors but you can customize each once you start designing.', 'boldgrid-inspirations' ); ?>
			</div>
		</div>
			<div class="themes wp-clearfix"></div>
		</div>
	</div>

	<div style="clear:both;"></div>

	<div id="screen-content" class="<?php echo implode( ' ', $sections['content']['classes'] ); ?>" >
		<div class="inspirations-mobile-toggle">
			<!-- Mobile Filter-->
				<div class="wp-filter">
					<a class="drawer-toggle" href="#"><?php echo esc_html__( 'Change Content', 'boldgrid-inspirations' ); ?></a>
				</div>
			<!-- End of Mobile Filter-->
		</div>
		<div class="left">
			<div class="content-menu-section">
				<div class="page-set-filter"><?php echo $lang['Pageset']; ?></div>
				<div id="pageset-options"></div>
			</div>
			<div class="content-menu-section">
				<div class="feature-filter"><?php echo $lang['AddFunctionality']; ?></div>
				<div class="feature-option">
					<input type="checkbox" name="install-blog" value=true /> <?php echo $lang['Blog']; ?>
					<div id="blog-toggle" class="toggle toggle-light"></div>
				</div>
				<div class="feature-option" id="feature_option_invoice" data-shown-pointer=false >
					<input type="checkbox" name="install-invoice" value=true />
					<?php esc_html_e( 'Invoice', 'boldgrid-inspirations' ); ?>
					<span class="dashicons dashicons-editor-help" data-bginsp-id="bginsp-invoice"></span>
					<div id="invoice-toggle" class="toggle toggle-light"></div>

					<div class="help" data-bginsp-id="bginsp-invoice">
						<p><?php esc_html_e( 'With Sprout Invoices, you can create beautiful estimates and invoices for your clients in minutes, and get paid easily.', 'boldgrid-inspirations' ); ?></p>
						<img src="<?php echo esc_url( BOLDGRID_BASE_URL . '/assets/images/inspirations/invoice/sprout-invoices.png' ); ?>" />
					</div>
				</div>
				<div class="feature-option" id="feature_option_cache" data-shown-pointer=false data-no-build="true">
					<input type="checkbox" name="install-cache" value=true /> <?php esc_html_e( 'Speed Boost', 'boldgrid-inspirations' ); ?>
					<span class="dashicons dashicons-editor-help" data-bginsp-id="bginsp-cache"></span>
					<div id="cache-toggle" class="toggle toggle-light"></div>

					<div class="help" data-bginsp-id="bginsp-cache">
						<p><?php esc_html_e( 'Faster website = better search rankings, more visitors, increased revenue and more. W3 Total Cache speeds up your WordPress website by reducing its download time, which makes your page load extremely fast.', 'boldgrid-inspirations' ); ?></p>
						<img src="<?php echo esc_url( BOLDGRID_BASE_URL . '/assets/images/inspirations/cache/w3-total-cache.png' ); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div class="right">
			<div class="boldgrid-plugin-card full-width" style="margin-bottom:15px;">
				<div class="top">
					<?php echo esc_html__( 'Choose the basic pages you wish to start with and any additional functionality.', 'boldgrid-inspirations' ); ?>
				</div>
			</div>
			<div id="build-summary">
				<div style="float:left;">
					<span id="theme-title"></span>
					<span class ="summary-subheading">
						<span id="sub-category-title"></span>
						<span class="devices">
							<button type="button" class="preview-desktop" aria-pressed="true" data-device="desktop">
								<span class="screen-reader-text"><?php echo $lang['Desktop']; ?></span>
							</button>
							<button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
								<span class="screen-reader-text"><?php echo $lang['Tablet']; ?></span>
							</button>
							<button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
								<span class="screen-reader-text"><?php echo $lang['Mobile']; ?></span>
							</button>
						</span>
					</span>
				</div>
				<div style="float:right;">
					<a class="button inspirations button-secondary"><?php echo esc_html__( 'Back', 'boldgrid-inspirations' ); ?></a>
					<a class="inspirations button button-primary install"><?php echo $lang['Next']; ?></a>
				</div>
			</div>

			<div style="clear:both;"></div>

			<div id="preview-container" >
				<div id="step-content-notices"><p></p></div>
				<iframe id="theme-preview"></iframe>
			</div>

			<div class="loading-wrapper boldgrid-loading hidden"></div>
		</div>
	</div>

	<div style="clear:both;"></div>

	<div id="screen-contact" class="<?php echo implode( ' ', $sections['contact']['classes'] ); ?>">
		<?php
		// Contact template.
		include BOLDGRID_BASE_DIR . '/pages/includes/boldgrid-inspirations/contact.php';
		?>
	</div>

	<input type="hidden" id="nonce-install-staging" value="<?php echo wp_create_nonce( "nonce-install-staging" ); ?>" />

	<div class="hidden">
		<input type="hidden" name="task"                           id="task"                           value="deploy" >
		<?php wp_nonce_field( 'deploy', 'deploy' ); ?>
		<input type="text"   name="boldgrid_cat_id"                id="boldgrid_cat_id"                value="-1" >
		<input type="text"   name="boldgrid_sub_cat_id"            id="boldgrid_sub_cat_id"            value="-1" >
		<input type="text"   name="boldgrid_theme_id"              id="boldgrid_theme_id"              value="-1" >
		<input type="text"   name="boldgrid_page_set_id"           id="boldgrid_page_set_id"           value="-1" >
		<input type="text"   name="boldgrid_api_key_hash"          id="boldgrid_api_key_hash"          value="<?php echo (isset($boldgrid_configs['api_key']) ? $boldgrid_configs['api_key'] : null); ?>" >
		<input type="text"   name="boldgrid_new_path"              id="boldgrid_new_path"              value="<?php echo str_replace('.','',str_replace(' ','',microtime())); ?>" >
		<input type="text"   name="boldgrid_pde"                   id="boldgrid_pde"                   value="" >
		<input type="text"   name="boldgrid_language_id"           id="boldgrid_language_id"           value="" >
		<input type="text"   name="boldgrid_build_profile_id"      id="boldgrid_build_profile_id"      value="" >
		<input type="text"   name="coin_budget"                    id="coin_budget"                    value="20" >
		<input type="text"   name="boldgrid_theme_version_type"    id="boldgrid_theme_version_type"    value="<?php echo $theme_channel ?>" >
		<input type="text"   name="boldgrid_page_set_version_type" id="boldgrid_page_set_version_type" value="<?php echo $theme_channel ?>" >
		<input type="text"   name="start_over"						id="start_over"                    value="<?php echo $start_over ? '1' : '0'; ?>" >
		<input type="text"   name="pages"                                                              value="" >
		<input type="text"   name="staging"                                                            value="" >
		<input type="hidden" name="_wp_http_referer"                                                   value="/single-site/wp-admin/admin.php?page=boldgrid-inspirations&amp;boldgrid-tab=install" >
		<input type="hidden"                                       id="wp_language"                    value="<?php echo bloginfo( 'language' ); ?>" >
		<input type="submit"                                                                           value="Deploy" >
	</div>

	</form>

</div>
