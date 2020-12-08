/**
 * This file helps with adding the "BoldGrid Connect Search" tab to the media
 * modal.
 *
 * Throughout this document, "BoldGrid Connect Search" will be refered to as
 * BGCS. You may also see a few references to .last() or :visible. This is
 * because several media modal's may be on the same page, not all of them active /
 * visible. Using last() / :visible seems to successfully grab the active media
 * modal.
 *
 * @summary Add the BGCS tab to media modals.
 *
 * @since 0.1
 */

/* globals wp, _wpMediaViewsL10n, pagenow, jQuery */

var IMHWPB = IMHWPB || {};

/**
 * Add the BGCS tab to media modals.
 *
 * @since 0.1
 */
IMHWPB.InsertMediaTabManager = function( $ ) {
	var self = this;

	$( function() {
		/*
		 * This script relies on _wpMediaViewsL10n. If we don't have it, abort.
		 *
		 * One example of when this doesn't exist is within Sprout Invoices > Add Invoice. At this time,
		 * they do not have any "Add media" buttons or a way to add media to an invoice. In this case,
		 * avoid js errors and abort.
		 *
		 * @todo We may need a cleaner approach to this in the future.
		 */
		if ( typeof _wpMediaViewsL10n === 'undefined' ) {
			return;
		}

		self.initVars();

		/*
		 * When one of our addTabTrigger elements is clicked, wait 2/10's of a
		 * second and then add our BoldGrid Connect Search tab. The timout is
		 * there to ensure that the media modal has opened, we cannot add the
		 * tab until it has opened. Please advise if you have a better technique
		 * for adding tabs to the media modal.
		 */
		$( document.body ).on( 'click', self.addTabTriggers, function() {
			var $clicked = $( this );

			setTimeout( function() {
				self.addTab( $clicked );
			}, 200 );
		} );

		// Remove "Image Search" tab in the left menu.
		$( document.body ).on( 'click', '#wp-content-media-buttons button', function() {
			self.removeImageSearch();
		} );

		self.setIframe();

		self.onTabClick();

		/*
		 * If the user clicks "Insert Media" and the "Media Library" tab is selected by default,
		 * refresh the media library.
		 */
		$( document.body ).on( 'click', self.selectors.insertMedia, function() {
			if ( $( self.selectors.mediaLibrary ).hasClass( 'active' ) ) {
				self.refreshMediaLibrary();
			}
		} );
	} );

	/**
	 * Add iframe loading message.
	 *
	 * The BGCS iframe takes a few seconds to load. To ease the transition,
	 * we'll show a loading message.
	 *
	 * @since 1.1.2
	 */
	this.addLoadingMessage = function() {
		var $content = $( '.media-frame-content:visible' ),
			$spinner = $(
				'<span class="spinner boldgrid_connect_search">' + self.i18n.loading + '</span>'
			);

		// Add the spinner.
		$content.append( $spinner );

		// Show the spinner for 2 seconds, then fade out half a second.
		setTimeout( function() {
			$spinner.fadeOut( 500, function() {
				$spinner.remove();
			} );
		}, 2000 );
	};

	/**
	 * Add our BGCS tab.
	 *
	 * @since 1.1.2
	 *
	 * @param object $clicked a jQuery object, the element clicked that triggered the BGCS tab to be added.
	 */
	this.addTab = function( $clicked ) {

		// There may be multiple menus, find the one that is visible.
		var $mediaRouter = $( '.media-router:visible' ),

			// Define the html that makes up our tab.
			$tab = $(
				'<a href="#" class="media-menu-item boldgrid-connect-search hidden">BoldGrid Connect Search</a>'
			),

			// Check if there is already a visible "BoldGrid Connect Search" tab.
			$bgcsTab = $mediaRouter.find( '.boldgrid-connect-search' ),

			// Get our "Media Library" tab.
			$libraryTab = $mediaRouter.find(
				'.media-menu-item:contains("' + _wpMediaViewsL10n.mediaLibraryTitle + '")'
			),

			// Get our "Upload Files" tab.
			$uploadTab = $(
				'.media-menu-item:visible:contains("' + _wpMediaViewsL10n.uploadFilesTitle + '")'
			),
			clickedText = $clicked.text(),

			// Find the number of active tabs.
			activeTabs = $mediaRouter.find( '.media-menu-item.active' ).length;

		/*
		 * Sometimes we need to know which element caused the tab to be added.
		 *
		 * As an example, for quite some time BGCS was only added in the page 'n post editor for
		 * add media. The editor however now has the ability to add section backgrounds. Instead of
		 * knowing that we're simply in the editor, we need to know if we're adding media or adding
		 * a section background.
		 *
		 * Under certain circumstances, add a data-added-by attribute to the BGCS tab so we know
		 * what click event caused it to be added.
		 */
		if ( $clicked.hasClass( 'add-image-controls' ) ) {
			$tab.attr( 'data-added-by', 'section-background' );
		} else if ( _wpMediaViewsL10n.addToGalleryTitle === clickedText ) {

			// "Add to Gallery".
			$tab.attr( 'data-added-by', 'add-to-gallery' );
		} else if ( _wpMediaViewsL10n.createGalleryTitle === clickedText ) {

			// "Create Gallery".
			$tab.attr( 'data-added-by', 'create-gallery' );
		}

		/*
		 * There are some cases when we don't need to add the tab. If we
		 * don't see either the "Upload files" or "Media Library" tabs, then
		 * abort.
		 */
		if ( 0 === $libraryTab.length || 0 === $uploadTab.length ) {
			return;
		}

		/*
		 * There are some instances, though rare, that no tabs are selected. If
		 * this is the case, click the "Media Library" tab.
		 */
		if ( 0 === activeTabs.length ) {
			$libraryTab[0].click();
		}

		/*
		 * Take action if the tab already exists. For example, the user may have
		 * been on the tab already and clicked 'x' to close the modal, then they
		 * reopened the modal.
		 */
		if ( 1 === $bgcsTab.length ) {

			/*
			 * If the tab is active, 'reset' things by clicking the "Media
			 * Library" tab.
			 */
			if ( $bgcsTab.hasClass( 'active' ) ) {
				$libraryTab[0].click();
				return;
			} else {
				return;
			}
		}

		$mediaRouter.append( $tab );
		$tab.fadeIn( 500 );
	};

	/**
	 * @summary Hide the media frame toolbar.
	 *
	 * For example, when loading the BoldGrid Connect Search tab.
	 *
	 * @since 1.2.10
	 */
	this.hideToolbar = function() {
		$( '.media-frame-toolbar' ).addClass( 'hidden' );
		$( '.media-frame-content' ).addClass( 'bottom-0' );
	};

	/**
	 * @summary Init vars, called during document ready.
	 *
	 * @since 1.6.1
	 */
	this.initVars = function() {
		self.i18n = BoldGridInspirationsMediaTab;

		/*
		 * A list of jQuery selectors used throughout this class.
		 *
		 * @since 1.1.4
		 */
		self.selectors = {
			insertMedia: '.media-menu-item:contains("' + _wpMediaViewsL10n.insertMediaTitle + '")',
			mediaLibrary: '.media-menu-item:contains("' + _wpMediaViewsL10n.mediaLibraryTitle + '")',
			createGallery: '.media-menu-item:contains("' + _wpMediaViewsL10n.createGalleryTitle + '")',
			addToGallery: '.media-menu-item:contains("' + _wpMediaViewsL10n.addToGalleryTitle + '")'
		};

		/*
		 * A list of selectors, when clicked, that cause the BGCS tab to be added.
		 *
		 * Note: When updating this list, ensure each entry ends with a comma.
		 *
		 * @since 1.1.2
		 */
		self.addTabTriggers =

			// P&PB: The image "Change" button added to the TinyMCE toolbar.
			'div[aria-label="' +
			self.i18n.Change +
			'"],' +

			/*
			 * Add Media.
			 *
			 * # Add Media.
			 * # Add Media > Insert Media.
			 * # Add Media > Create Gallery.
			 * # Add Media > Gallery > Add to Gallery.
			 */
			// Add Media.
			'#insert-media-button,' +
			'[aria-label="' +
			_wpMediaViewsL10n.addMedia +
			'"],' + // WordPress 5.0, Classic block TinyMCE toolbar.
			// Add Media > Insert Media.
			self.selectors.insertMedia +
			',' +

			// Add Media > Create Gallery.
			self.selectors.createGallery +
			',' +

			// Add Media > Gallery > Add to Gallery.
			self.selectors.addToGallery +
			',' +

			/*
			 * Customizer.
			 *
			 * # Header > Add new image.
			 * # Background > Select image.
			 * # Background > thumbnail.
			 * # Site Icon > Select image.
			 * # Site Logo > Select image.
			 */
			// Header > Add new image.
			'#customize-control-header_image .button.new,' +

			// Background > Select image.
			'#background_image-button,' +
			'#customize-control-background_image .actions button,' + // WordPress 5.0.
			// Background > thumbnail.
			'.customize-control-background img.attachment-thumb,' +

			// Site Icon > Select image.
			'#site_icon-button,' +

			// Site Logo > Select image.
			'#boldgrid_logo_setting-button,' +

			// Widget > Image.
			'[id^=customize-control-widget_media_image] .select-media,' +

			/*
			 * BoldGrid Editor - Column options.
			 *
			 * # Insert Media
			 */
			// Insert Media
			// @todo: This isn't quite working.
			'[data-action="add-media"],' +

			/*
			 * BoldGrid Editor - Top menu buttons.
			 *
			 * # Change Image.
			 * # Add > Media.
			 * # Section Background > Add Image.
			 */
			// Change Image.
			'[data-action="menu-image-change"],' +

			// Add Media.
			'[data-action="menu-add"] .add-media,' +

			// Section Background > Add Image.
			'[data-type="background"] .add-image-controls,' +

			// Gutenberg.
			'.wp-block-image button,' +
			'[aria-label="' +
			self.i18n.editImage +
			'"],' +

			// WordPress 5.0 - Classic editor's tiny "Add Media" icon.
			'.block-library-classic__toolbar .dashicons-admin-media';
	};

	/**
	 * @summary Refresh the Media Library.
	 *
	 * @link http://wordpress.stackexchange.com/questions/78230/trigger-refresh-for-new-media-manager-in-3-5
	 *
	 * @since 1.1.4
	 */
	this.refreshMediaLibrary = function() {
		var frame = parent.wp.media.frame;

		if ( null !== frame.content.get() ) {
			frame.content.get().collection.props.set( { ignore: +new Date() } );
			frame.content.get().options.selection.reset();
		} else {
			frame.library.props.set( { ignore: +new Date() } );
		}
	};

	/**
	 * @summary Whenever a media button is clicked, remove the "Image Search" tab.
	 *
	 * Media button are for example "Add Media".
	 *
	 * Normally, when adding 'tabs' to the wp.media, they're added in the left menu. BoldGrid
	 * Connect Search started off as a left menu item, but for easier accessability, it was added
	 * as a main tab next to "Upload Files" and "Insert Media". We no longer need the link in the
	 * left menu, so remove it.
	 *
	 * @since 1.2.10
	 */
	this.removeImageSearch = function() {
		setTimeout( function() {
			$( 'a.media-menu-item:contains(\'' + self.i18n.imageSearch + '\')' ).remove();
		}, 200 );
	};

	/**
	 * @summary Take action when a media frame is opened.
	 *
	 * @since 1.2.10
	 */
	this.onFrameOpen = function() {

		// Make sure we're not already listening.
		if ( wp.media.frame.boldgridListening === undefined ) {

			/*
			 * Ensure bottom toolbar is visible.
			 *
			 * When you click the BGCS tab, the toolbar at the bottom is removed. It needs to be visible
			 * at all times except when on the BGCS tab.
			 *
			 * Below we are taking action on both when a media modal is opened, and when different
			 * states are activated.
			 */
			wp.media.frame.on( 'open', function() {
				self.showToolbar();
				self.removeImageSearch();
			} );

			wp.media.frame.on( 'activate', function() {
				self.showToolbar();
			} );

			// Take note that we're now listening to this frame.
			wp.media.frame.boldgridListening = true;
		}
	};

	/**
	 * Event handler for tab clicks.
	 *
	 * @since 1.1.2
	 */
	this.onTabClick = function() {

		// Tab clicks in the top menu.
		$( document.body ).on( 'click', '.media-router .media-menu-item', function() {
			var $content = $( '.media-frame-content:visible' ),

				// Our BGCS iframe.
				$iframe = $content.find( '#boldgrid_connect_search' ),

				// The content for the "Media Library" tab.
				$library = $content.find( '.attachments-browser' ),

				// The media router.
				$mediaRouter = $( '.media-router:visible', window.parent.document ),
				$priorTab = $mediaRouter.find( '.media-menu-item.active' ),
				$newTab = $( this ),

				// The "Media Library" tab.
				$libraryTab = $mediaRouter.find( self.selectors.mediaLibrary ),

				// The tab clicked.
				$tab = $( this ),

				// The content for the "Upload Files" tab.
				$uploader = $content.find( '.uploader-inline-content' ),

				// The "BoldGrid Connect Search" tab.
				$bgcsTab = $mediaRouter.find(
					'.media-menu-item.boldgrid-connect-search',
					window.parent.document
				);

			/*
			 * The function below adds an action to the opening of wp.media.frame.
			 *
			 * The frame must be created before we can add an action to the on open, and at this
			 * point we know the frame is open.
			 */
			self.onFrameOpen();

			/*
			 * In order for BGCS to work properly, there needs to be an
			 * .attachments-browser within the DOM. That needed element
			 * is created when the user clicks the "Media Library" tab.
			 * If we've clicked the BGCS tab, and our last tab wasn't
			 * the "Media Library", then we don't have a library. Click
			 * the "Media Library" tab to generate our library, then
			 * click the BGCS tab.
			 */
			if ( $newTab.is( $bgcsTab ) && ! $priorTab.is( $libraryTab ) ) {
				$libraryTab[0].click();
				$bgcsTab[0].click();
				return;
			}

			// Whenever the "Media Library" tab is clicked, refresh the library.
			if ( $newTab.is( $libraryTab ) ) {
				self.refreshMediaLibrary();
			}

			// Toggle the '.active' state of the tabs.
			$( '.media-router:visible .media-menu-item' ).removeClass( 'active' );
			$tab.addClass( 'active' );

			// If we have clicked on the BoldGrid tab.
			if ( $tab.hasClass( 'boldgrid-connect-search' ) ) {

				// Hide the uploader and the library.
				$uploader.addClass( 'hidden' );
				$library.addClass( 'hidden' );

				// If we don't already have our BoldGrid iframe, add it.
				if ( 0 === $iframe.length ) {
					self.addLoadingMessage();
					$content.append( self.iframe );
				}
				$iframe.removeClass( 'hidden' );

				self.hideToolbar();
			} else {

				// Hide the BGCS iframe.
				$iframe.addClass( 'hidden' );

				// Show the uploader and library.
				$uploader.removeClass( 'hidden' );
				$library.removeClass( 'hidden' );

				self.showToolbar();
			}
		} );
	};

	/**
	 * Configure our BoldGrid Connect Search iframe.
	 *
	 * @since 1.1.2
	 */
	this.setIframe = function() {

		// Configure our post_id parameter for the iframe.
		var post_id_param = 'undefined' === typeof IMHWPB.post_id ? '' : '&post_id=' + IMHWPB.post_id,
			ref;

		// Configure our referrer parameter for the iframe.
		if ( 'object' == typeof window._wpCustomizeSettings ) {
			ref = 'dashboard-customizer';
		} else if ( 'post' == pagenow || 'page' == pagenow ) {
			ref = 'dashboard-post';
		} else {
			ref = 'dashboard-media';
		}

		self.iframe =
			'<iframe src="media-upload.php?chromeless=1' +
			post_id_param +
			'&tab=image_search&ref=' +
			ref +
			'" id="boldgrid_connect_search"></iframe>';
	};

	/**
	 * @summary Show the media frame toolbar.
	 *
	 * We hide it at times, when loading the BoldGrid Connect Search tab. This function shows it,
	 * because other media frame tools need it.
	 *
	 * @since 1.2.10
	 */
	this.showToolbar = function() {
		$( '.media-frame' ).removeClass( '.hide-toolbar' );
		$( '.media-frame-toolbar' ).removeClass( 'hidden' );
		$( '.media-frame-content' ).removeClass( 'bottom-0' );
	};
};

IMHWPB.InsertMediaTabManager( jQuery );
