/* globals ajaxurl, Inspiration, wp, _, jQuery */

var IMHWPB = IMHWPB || {};

/**
 * Inspirstions, design first.
 *
 * @since 1.2.3
 *
 * @memberof IMHWPB
 */
IMHWPB.InspirationsDesignFirst = function( $, configs ) {
	var self = this;

	this.configs = configs;

	if ( 'function' === typeof IMHWPB.Ajax ) {
		self.ajax = new IMHWPB.Ajax( configs );
	}

	self.$categories = $( '#categories' );

	self.categories = '';

	self.$themes = $( '.theme-browser .themes' );
	self.themes = '';

	self.$theme = '';
	self.$pageset = '';

	/**
	 * An object of generic builds.
	 *
	 * @since 1.2.6
	 */
	self.genericBuilds = {};

	/**
	 * An area for notices in step 2, Content.
	 *
	 * @since 1.2.6
	 */
	self.$contentNotices = $( '#step-content-notices p' );

	/**
	 * An array of distinct themes returned from our call to get generic builds.
	 *
	 * @since 1.2.6
	 */
	self.distinctThemes = [];

	/**
	 * The container of social media icons in the Inspirations survey.
	 *
	 * @since 1.3.4
	 */
	self.$socialIndex = $( '#social-media-index' );

	/**
	 * The selected sub category id in step 1.
	 *
	 * @since 1.2.5
	 */
	self.subCategoryId = '0';

	/**
	 * Theme release channel.
	 *
	 * @since 1.2.5
	 */
	self.themeReleaseChannel =
		configs === undefined ? 'stable' : configs.settings.theme_release_channel;

	/**
	 * Theme preview.
	 *
	 * @since 1.2.9
	 */
	self.$themePreview = $( '#screen-content iframe#theme-preview' );

	/**
	 * The last theme previwed within fancybox.
	 *
	 * @since 1.3.1
	 */
	self.$themePreviewed = '';

	/**
	 * The top menu.
	 *
	 * @since 1.3.2
	 */
	self.$topMenu = $( '.top-menu' );

	/**
	 * The appropriate scrolltop value to align a theme with the "Category Filter" heading.
	 *
	 * @since 1.3.1
	 */
	self.themeTop = self.themeTop =
		self.$topMenu.outerHeight() +
		parseInt( self.$topMenu.css( 'margin-bottom' ) ) +
		$( '#wpadminbar' ).outerHeight();

	// Scroll position.
	self.scrollPosition = '';

	/**
	 * Cache the $('.wrap') jQuery object.
	 *
	 * @since 1.3.3
	 */
	self.$wrap = $( '.wrap' );

	/**
	 * Enable or disable all actions on the page.
	 *
	 * The "body .waiting" class adds the "wait" icon to various elements on the page.
	 * Top menu links with a .disabled class do nothing when clicked.
	 *
	 * @since 1.2.3
	 *
	 * @memberof IMHWPB.InspirationsDesignFirst
	 */
	this.allActions = function( effect ) {
		var $bottomButtons = $( '.boldgrid-plugin-card .bottom .button' ),
			$categories = $( '#categories' );

		if ( 'disable' === effect ) {
			$( 'body' ).addClass( 'waiting' );
			self.$topMenu.find( 'a' ).addClass( 'disabled' );
			$categories.addClass( 'disabled' );
			$( '#build-summary .button' ).attr( 'disabled', true );
			$bottomButtons.attr( 'disabled', true );
		} else {
			$( 'body' ).removeClass( 'waiting' );
			self.$topMenu.find( 'a:not([data-disabled])' ).removeClass( 'disabled' );
			$categories.removeClass( 'disabled' );
			$( '#build-summary .button' ).attr( 'disabled', false );
			$bottomButtons.attr( 'disabled', false );
		}
	};

	/**
	 * User chooses a theme
	 *
	 * @since 1.2.3
	 */
	this.chooseTheme = function() {

		// Immediately hide the iframe to give a better transition effect.
		self.$themePreview.css( 'visibility', 'hidden' );

		// Load the theme title and sub category title.
		$( '#sub-category-title' ).html(
			'- ' + self.$theme.closest( '.theme' ).attr( 'data-sub-category-title' )
		);
		$( '#theme-title' ).html( self.$theme.closest( '.theme' ).attr( 'data-theme-title' ) );

		self.toggleStep( 'content' );

		self.initPagesets();
	};

	/**
	 *
	 */
	this.toggleCheckbox = function() {
		var $subCategory = $( 'input[name="sub-category"]:checked' );
		$subCategory.parent().css( 'background', 'blue' );
	};

	/**
	 * Toggle build features in Step 2.
	 *
	 * @since 1.3.6
	 *
	 * @param jQuery object $feature The feature clicked.
	 */
	this.toggleFeature = function( $feature ) {
		var $featureInput = $feature.find( 'input[type="checkbox"]' ),
			$toggle = $feature.find( '.toggle' ).data( 'toggles' ),
			newCheckedProp = ! $featureInput.is( ':checked' );

		// If we're waiting on something, don't allow the user to toggle features.
		if ( $( 'body' ).hasClass( 'waiting' ) ) {
			return;
		}

		$featureInput.prop( 'checked', newCheckedProp );

		$toggle.toggle( newCheckedProp );

		self.loadBuild();
	};

	/**
	 * @summary Actions to take when a device preview button is clicked.
	 *
	 * @since 1.2.3
	 */
	this.devicePreviews = function() {
		var $previewContainer = $( '#preview-container' );

		self.$wrap.on( 'click', '.devices button', function() {
			var $button = $( this ),
				iframeClass;

			/*
			 * If we're waiting on a preview to load, don't allow the user to click different device
			 * previews.
			 */
			if ( $( 'body' ).hasClass( 'waiting' ) ) {

				// When you click automatically, focus will be added. Remove it.
				if ( $button.is( ':focus' ) ) {
					$button.blur();
				}

				return;
			}

			/*
			 * If we're clicking on a device preview button, we're forcing dimensions. We can remove
			 * the default highlight (which is based upon the preview's dimensions.
			 */
			$( '.devices button' ).removeClass( 'highlight' );

			// Determine which preview button we've clicked on.
			if ( $button.hasClass( 'preview-desktop' ) ) {
				iframeClass = 'preview-desktop';
			} else if ( $button.hasClass( 'preview-tablet' ) ) {
				iframeClass = 'preview-tablet';
			} else {
				iframeClass = 'preview-mobile';
			}

			/*
			 * If the button is already active and we're clicking on it again, disable a device
			 * preview and just show the preview full width (the default behavior / view when going
			 * to step 2 for the first time.
			 */
			if ( $button.hasClass( 'active' ) ) {
				$button.removeClass( 'active' ).blur();

				$previewContainer.removeClass();
				self.highlightDeviceButton();

				return;
			}

			// Remove the active class from the previously active button.
			$( '.devices .active' )
				.attr( 'aria-pressed', 'false' )
				.removeClass( 'active' );

			// Mark the current device preview button as active.
			$( this )
				.attr( 'aria-pressed', 'true' )
				.addClass( 'active' );

			$previewContainer.removeClass().addClass( iframeClass );
		} );
	};

	/**
	 * @summary Init fancybox.
	 *
	 * @since 1.3.1
	 */
	this.fancybox = function() {
		$( '.fancybox' ).fancybox( {
			type: 'image',
			openSpeed: 500,
			closeSpeed: 250,
			beforeLoad: function() {
				$( 'body' ).addClass( 'fancyboxed' );
			},
			afterLoad: function() {
				var $link = $( this.element[0] );

				self.$themePreviewed = $link.closest( '.theme' );

				this.title =
					'<a class="button button-primary hide-if-no-customize fancybox-select">' +
					Inspiration.select +
					'</a>' +
					'<h2 class="theme-name" >' +
					self.$themePreviewed.find( 'h2.theme-name' ).html() +
					'</h2>';

				/*
				 * When previewing a large theme screenshot, scroll the body to that theme.
				 *
				 * The "complex" scrollTop calculation simply ensures a nice scroll position,
				 * positioning the theme flush with the "Category Filter" heading.
				 */
				$( 'html, body' ).animate(
					{
						scrollTop: self.$themePreviewed.offset().top - self.themeTop
					},
					250
				);
			},
			afterClose: function() {
				$( 'body' ).removeClass( 'fancyboxed' );
			},
			helpers: {
				title: {
					type: 'inside'
				},
				overlay: {
					locked: false
				}
			}
		} );
	};

	/**
	 * @summary Choose a theme being previewed.
	 *
	 * @since 1.3.1
	 */
	this.fancyboxSelect = function() {
		self.$theme = self.$themePreviewed;

		$.fancybox.close();

		self.chooseTheme();
	};

	/**
	 * Event handler for the back button on step 2.
	 */
	this.backButton = function() {
		$( '.inspirations.button-secondary' ).on( 'click', function() {
			self.toggleStep( 'design' );
		} );
	};

	/**
	 * @summary Bind the click of various elements.
	 *
	 * @since 1.2.5
	 */
	this.bindClicks = function() {

		// Whenever a "Next" button is clicked, toggle to the next step.
		self.$wrap.on( 'click', '.button.next-step', self.toggleStep );

		// Handle the click of the "get started" button on the "Welcome" screen.
		self.$wrap.on( 'click', '#screen-welcome .button-primary', self.toggleStep );

		/*
		 * During step 1, if there is an error fetching categories, we'll give the user a button to
		 * try again. Handle the click of that try again button.
		 */
		self.$wrap.on( 'click', '#try-categories-again', self.initCategories );

		/*
		 * During step 1, if there is an error fetching themes, we'll give the user a button to try
		 * again. Handle the click of that try again button.
		 */
		self.$wrap.on( 'click', '#try-themes-again', self.initThemes );

		/*
		 * During step 1, if we click to preview a theme, there is a select option. Handle the click
		 * of that select option, which chooses the previewed theme and proceeds to the live preview.
		 */
		$( 'body' ).on( 'click', '.fancybox-select', self.fancyboxSelect );

		/*
		 * During step 2, if there is an error fetching pagesets, we'll give the user a button to
		 * try again. Handle the click of that try again button.
		 */
		self.$wrap.on( 'click', '#try-pagesets-again', self.initPagesets );

		/*
		 * During step 2, if there is an error building a site preview, we'll give the user a button
		 * to try again. Handle the click of that try again button.
		 */
		self.$wrap.on( 'click', '#try-build-again', self.loadBuild );

		// During step 2, you can toggle build features (like a blog). Handle click of those features.
		self.$wrap.on( 'click', '.feature-option', function() {
			var $feature = $( this );
			self.toggleFeature( $feature );
		} );

		/*
		 * During step 3, we give the user the option to click on a social media icon and configure
		 * that social media site. Handle the click of those social media buttons.
		 */
		self.$wrap.on( 'click', '#social-media-index span', function() {
			var $icon = $( this );
			self.socialMediaAdd( $icon );
		} );

		/*
		 * During step 3, we give the user the ability to click x / delete a social media site from
		 * their configuration. Handle the click of that x.
		 */
		self.$wrap.on( 'click', '.social-media .fa-times', function() {
			var $deleteIcon = $( this );
			self.socialMediaRemove( $deleteIcon );
		} );

		/*
		 * During step 3, we give the user the ability to toggle the "Do not display" checkbox for
		 * various items in the form. Handle the click of those checkboxes.
		 */
		self.$wrap.on( 'change', '.survey-field .option :checkbox', function() {
			var $checkbox = $( this );
			self.surveyToggleDisplay( $checkbox );
		} );
	};

	/**
	 *
	 */
	this.bindInstallModal = function() {

		// Step 2 Next button.
		$( '#screen-content .button-primary' ).click( function() {
			self.toggleStep( 'contact' );
		} );

		// Step 3 Go back button.
		$( '#screen-contact .button-secondary' ).on( 'click', function() {
			self.toggleStep( 'content' );
		} );

		// Contact step, handle "finish and install" button.
		$( '#screen-contact .button-primary' ).on( 'click', function() {
			if ( ! self.validateContact() ) {
				return;
			}

			self.allActions( 'disable' );

			$( this ).after( '<span class="spinner inline spinner-left"></span>' );

			// Uncomment for testing, deploy to new tab to quickly test deploy several times.
			// $( '#post_deploy' ).attr( 'target', '_blank' ); self.allActions( 'enable' );

			$( '#post_deploy' ).submit();
		} );
	};

	/**
	 * Checks to see if the mobile menu is actually displayed.
	 *
	 * @return boolean
	 */
	this.isMobile = function() {
		return 0 === $( '.wp-filter:visible' ).length ? false : true;
	};

	/**
	 * Toggle the mobile menu open and closed.
	 */
	this.mobileToggle = function() {
		$( '.left' ).toggle( 'slow' );
		$( '.drawer-toggle' ).toggleClass( 'open' );
	};

	/**
	 * Force the mobile menu to close.
	 */
	this.mobileCollapse = function() {
		var $mobileMenu = $( '.left' );
		if ( $mobileMenu.is( ':visible' ) ) {
			self.mobileToggle();
		}
	};

	this.mobileMenuToggle = function() {
		$( '.drawer-toggle' ).on( 'click', function() {
			self.mobileToggle();
		} );
	};

	/**
	 * @summary Actions to take when the window is resized.
	 *
	 * This method is triggered from init().
	 *
	 * @since 1.2.5
	 */
	this.onResize = function() {

		/*
		 * When the window is resized, wait 0.4 seconds and readjust the highlighted device preview
		 * button.
		 */
		$( window ).resize( function() {
			clearTimeout( $.data( this, 'resizeTimer' ) );

			$.data(
				this,
				'resizeTimer',
				setTimeout( function() {
					self.highlightDeviceButton();
				}, 400 )
			);
		} );
	};

	/**
	 * @summary Handles the Show All filter.
	 *
	 * @since 1.2.3
	 */
	this.showAll = function() {
		self.$wrap.on( 'click', '[data-sort="show-all"]', function() {
			var $all = $( '[data-sub-category-id="0"]' ),
				ref = $all.parent( '.sub-category' );

			// Remove all active classes from sub categories.
			$( '.sub-category.active' ).removeClass( 'active' );

			// Check radio.
			$all.prop( 'checked', true );

			// Check radio check.
			if ( $all.is( ':checked' ) ) {
				ref.addClass( 'active' );
			}

			// Collapse mobile.
			self.mobileCollapse();

			// Update filter text.
			self.updateFilterText( 'All' );

			// Display all themes.
			self.toggleSubCategory( '0' );

			// Toggle the current class for show all.
			self.toggleShowAll( ref );
		} );
	};

	/**
	 * @summary Add a social media site to a user's info in step 3.
	 *
	 * @since 1.3.2
	 *
	 * @param jQuery object $icon The social media icon the user clicked on.
	 */
	this.socialMediaAdd = function( $icon ) {
		var dataIcon = $icon.attr( 'data-icon' ),
			data = {
				icon: dataIcon,
				url: $icon.attr( 'data-sample-url' )
			},

			// Our $icon is a span, $faIcon is the actual icon.
			$faIcon = $icon.find( 'i.fa' ),
			template = wp.template( 'social-media' );

		// If this icon is .disabled, the user cannot add another, abort.
		if ( $icon.hasClass( 'disabled' ) || $icon.hasClass( 'disabled-via-toggle' ) ) {
			return;
		}

		// If this is the plus icon, show all other icons and abort.
		if ( $faIcon.hasClass( 'fa-plus' ) ) {

			// Show all other icons.
			self.$socialIndex.find( '[data-icon]' ).removeClass( 'hidden' );

			// Toggle the plus / minus sign.
			$faIcon.removeClass( 'fa-plus' ).addClass( 'fa-minus' );

			// Update the title tag to say "Show fewer".
			self.switchAttributes( $icon, 'title', 'data-alt-title' );

			return;
		}

		// If this is the minus icon, hide the non default icons.
		if ( $faIcon.hasClass( 'fa-minus' ) ) {

			// Hide non default icons.
			self.$socialIndex.find( '[data-hidden]' ).addClass( 'hidden' );

			// Toggle the plus / minus sign.
			$faIcon.addClass( 'fa-plus' ).removeClass( 'fa-minus' );

			// Update the title tag to say "Show more".
			self.switchAttributes( $icon, 'title', 'data-alt-title' );

			return;
		}

		/*
		 * We're only allowing one social media network of each type to be added. Once we've added
		 * a social network, disable the button so it cannot be clicked again.
		 */
		$icon.addClass( 'disabled' );

		$( '#social-media' ).append( template( data ) );
	};

	/**
	 * @summary Show the default social media entries in step 3.
	 *
	 * For example, by default only Facebook and Twitter may show.
	 *
	 * @since 1.3.2
	 */
	this.socialMediaDefaults = function() {
		var defaults = self.$socialIndex.find( '[data-added]' );

		defaults.each( function( index ) {
			self.socialMediaAdd( $( this ) );
		} );
	};

	/**
	 * @summary Remove a social media site from a user's info in step 3.
	 *
	 * @since 1.3.2
	 *
	 * @param jQuery object $icon The x icon the user clicked to delete the social media entry.
	 */
	this.socialMediaRemove = function( $deleteIcon ) {
		var $container = $deleteIcon.closest( '.social-media' ),
			provider = $container.attr( 'data-provider' );

		$container.remove();

		self.$socialIndex.find( '[data-icon="' + provider + '"]' ).removeClass( 'disabled' );
	};

	/**
	 * @summary Sort all builds based upon "All Order".
	 *
	 * Definitions:
	 * # CategoryOrder: The order a theme should appear when viewing themes by category.
	 * # AllOrder: When viewing all theme / category combinations, the order in which a particular
	 *   theme should appear.
	 * # SubCategoryDisplayOrder: The order in which sub categories are sorted.
	 *
	 * @since 1.2.3
	 */
	this.sortAll = function() {
		var themeCount;

		self.setDistinctThemes();

		themeCount = self.distinctThemes.length;

		self.genericBuilds.sort( function( a, b ) {

			/*
			 * If a theme does not have a CategoryOrder, set it to themeCount, which does the same
			 * thing as setting it to be the last theme displayed in the category.
			 */
			a.CategoryOrder = null === a.CategoryOrder ? themeCount : a.CategoryOrder;
			b.CategoryOrder = null === b.CategoryOrder ? themeCount : b.CategoryOrder;

			/*
			 * Based upon the theme's CategoryOrder and the SubCategoryDisplayOrder, calculate this
			 * theme's AllOrder.
			 */
			a.AllOrder = ( parseInt( a.SubCategoryDisplayOrder ) - 1 ) * themeCount + a.CategoryOrder;
			b.AllOrder = ( parseInt( b.SubCategoryDisplayOrder ) - 1 ) * themeCount + b.CategoryOrder;

			return parseInt( a.AllOrder ) > parseInt( b.AllOrder ) ? 1 : -1;
		} );
	};

	/**
	 * @summary Sort Categories.
	 *
	 * @since 1.2.6
	 */
	this.sortCategories = function( sortBy ) {

		// The "Category Filter" heading.
		var $categoryHeading = $( '.category-filter', self.$categories ),

			// Sorted categories.
			$sortedCategories = $( '.sub-category', self.$categories ).sort( function( a, b ) {
				var aSort = parseInt( $( a ).attr( sortBy ) ),
					bSort = parseInt( $( b ).attr( sortBy ) );

				return aSort > bSort ? 1 : -1;
			} );

		// Insert our sorted categories after the category heading.
		$sortedCategories.insertAfter( $categoryHeading );
	};

	/**
	 * @summary Sort themes.
	 *
	 * @since 1.2.3
	 */
	this.sortThemes = function( sortBy ) {

		/*
		 * When this function was originally written, it assumed that we were on the "Design" step
		 * and the themes we wanted to sort were visible. As of 2.0.0, the themes are loaded in the
		 * background while the user is on the "Welcome" step.
		 *
		 * If we're sorting the "All" category (data-all-order), then sort all the themes. Otherwise,
		 * save resources and only sort the visible themes.
		 */
		var $themes = 'data-all-order' === sortBy ? $( '.themes .theme' ) : $( '.themes .theme:visible' );

		$themes
			.sort( function( a, b ) {
				var aSort = parseInt( $( a ).attr( sortBy ) ),
					bSort = parseInt( $( b ).attr( sortBy ) );

				if ( ! aSort ) {
					return 1;
				}

				return aSort > bSort ? 1 : -1;
			} )
			.prependTo( '.themes' );
	};

	/**
	 * Toggle the show all current class.
	 */
	this.toggleShowAll = function( o ) {
		var $showAll = $( '[data-sort="show-all"]' ),
			$subcatId = o.find( '[data-sub-category-id]' ).data( 'sub-category-id' );

		// Add current class to show all filter if previewing all themes.
		$showAll.addClass( 'current' );

		// If we aren't clicking on All remove that class.
		if ( 0 !== $subcatId ) {
			$showAll.removeClass( 'current' );
		}
	};

	/**
	 * Update the filter text on the mobile view.
	 */
	this.updateFilterText = function( text ) {
		$( '.theme-count' ).text( text );
	};

	/**
	 * Subcategories event handler.
	 */
	this.subcategories = function() {

		// Subcategories.
		self.$wrap.on( 'click', '.sub-category', function() {
			var $subCategory = $( this ).find( 'input[name="sub-category"]' ),
				$subcategoryName = $( this )
					.find( '.sub-category-name' )
					.text(),
				subCategoryId = $subCategory.attr( 'data-sub-category-id' ),
				ref = $( this ),
				$categories = $( '#categories' );

			// Do nothing if the themes are still loading.
			if ( $categories.hasClass( 'disabled' ) ) {
				return;
			}

			/*
			 * Keep track of the sub category id the user clicked.
			 *
			 * If the user is clicking a sub category that's already showing (IE they click Fashion and
			 * then click Fashion immediately again), abort. We don't want the builds to be continually
			 * shuffled.
			 */
			if ( subCategoryId === self.subCategoryId ) {
				return;
			} else {
				self.subCategoryId = subCategoryId;
			}

			// Reset scroll position.
			window.scrollTo( 0, 0 );

			// Remove any active classes.
			$( '.sub-category.active' ).removeClass( 'active' );

			// Mark subcategory as active.
			$subCategory.prop( 'checked', true );

			// Add active class.
			if ( $subCategory.is( ':checked' ) ) {
				ref.addClass( 'active' );
			}
			self.updateFilterText( $subcategoryName );

			// Toggle the show all filter.
			self.toggleShowAll( ref );

			// Mobile actions.
			if ( self.isMobile() ) {

				// Collapse the menu when selection is made.
				self.mobileToggle();
			}

			// Always toggle subcategory.
			self.toggleSubCategory( subCategoryId );
		} );
	};

	/**
	 * @summary Toggle inputs on the survey.
	 *
	 * When a user clicks "Do not display", toggle the "disabled" class of the inputs.
	 *
	 * @since 1.3.2
	 *
	 * @param jQuery object $checkbox The checkbox the user clicked.
	 */
	this.surveyToggleDisplay = function( $checkbox ) {
		var $container = $checkbox.closest( '.survey-field' );

		$container.find( 'input[type="text"]' ).toggleDisabled();

		// If we are disabling social media, prevent the user from adding more networks.
		if ( 'social-media' === $container.attr( 'id' ) ) {
			self.$socialIndex.find( 'span' ).toggleClass( 'disabled-via-toggle' );
		}
	};

	/**
	 * @summary Switch attributes of a jQuery object.
	 *
	 * @since 1.3.4
	 *
	 * @param jQuery object $object
	 * @param string        a       The first attribution name.
	 * @param string        b       The second attribution name.
	 */
	this.switchAttributes = function( $object, a, b ) {
		var originalA = $object.attr( a ),
			originalB = $object.attr( b );

		$object.attr( a, originalB ).attr( b, originalA );
	};

	/**
	 * Selects theme to load to continue on to step 2 of inspirations.
	 */
	this.selectTheme = function() {
		self.$wrap.on( 'click', '.theme .theme-actions .button-primary', function() {
			self.$theme = $( this );
			self.chooseTheme();
		} );
	};

	/**
	 * @summary Set distinct themes.
	 *
	 * @since 1.2.6
	 */
	this.setDistinctThemes = function() {
		var i = 0;

		for ( i; i < self.genericBuilds.length; i++ ) {
			if ( -1 === _.indexOf( self.distinctThemes, self.genericBuilds[i].ThemeName ) ) {
				self.distinctThemes.push( self.genericBuilds[i].ThemeName );
			}
		}
	};

	/**
	 * Sets the hover colors class.
	 */
	this.hoverColors = function() {

		// Hovers.
		self.$wrap.on(
			'mouseenter mouseleave',
			'.sub-category, .pageset-option, .coin-option',
			function() {
				$( this ).toggleClass( 'blue' );
			}
		);
	};

	/**
	 * @summary Based upon the preview size, highlight which device preview is closest.
	 *
	 * For example, if your preview is only 300px wide, highlight the phone preview button.
	 *
	 * @since 1.2.5
	 */
	this.highlightDeviceButton = function() {

		// Get the active button.
		var $activeButton = $( '.devices button.active' ),
			previewWidth;

		// If we have an active button, there's no need to highlight at this point, abort.
		if ( 0 < $activeButton.length ) {
			return;
		}

		// Remove our highlight class from all buttons, we'll add it back in a moment.
		$( '.devices button' ).removeClass( 'highlight' );

		// Determine width of our preview.
		previewWidth = $( '#preview-container' ).outerWidth();

		// Highlight the appropriate device button.
		if ( 320 >= previewWidth ) {
			$( '.devices .preview-mobile' ).addClass( 'highlight' );
		} else if ( 768 > previewWidth ) {
			$( '.devices .preview-tablet' ).addClass( 'highlight' );
		} else {
			$( '.devices .preview-desktop' ).addClass( 'highlight' );
		}
	};

	/**
	 * Click event handler for pageset options section.
	 */
	this.pagesetOptions = function() {

		// Pageset Options.
		self.$wrap.on( 'click', '.pageset-option', function() {

			// If we're waiting on something, don't allow the user to select a different pageset.
			if ( $( 'body' ).hasClass( 'waiting' ) ) {
				return;
			}

			var $pagesetInput = $( this ).find( 'input[name="pageset"]' );

			$( '.pageset-option.active' ).removeClass( 'active' );

			$pagesetInput.prop( 'checked', true );

			if ( $pagesetInput.is( ':checked' ) ) {
				$( this ).addClass( 'active' );
			}

			self.$pageset = $( 'input[name="pageset"]:checked' );

			self.loadBuild();
		} );
	};

	/**
	 * @summary Remove a category.
	 *
	 * @since 1.4.9
	 *
	 * @param string id Category id.
	 */
	this.removeCategory = function( id ) {
		delete self.categories[id];

		$( '.sub-category [data-sub-category-id="' + id + '"]' )
			.closest( '.sub-category' )
			.slideUp( 1000, function() {
				$( this ).remove();
			} );
	};

	/**
	 * Click event handler for coin budget options section.
	 */
	this.coinOptions = function() {

		// Coin Budgets.
		self.$wrap.on( 'click', '.coin-option', function() {

			// If we're waiting on something, don't allow the user to select a different budget.
			if ( $( 'body' ).hasClass( 'waiting' ) ) {
				return;
			}

			var $currentBudget = $( '.coin-option.active' ),
				$newBudget = $( this );

			// Toggle the active class.
			$currentBudget.removeClass( 'active' );
			$newBudget.addClass( 'active' );

			self.loadBuild();
		} );
	};

	/**
	 * Loads the iframe for the theme preview.
	 */
	this.iframeLoad = function() {
		self.$themePreview.on( 'load', function() {
			$( '#screen-content .boldgrid-loading' ).fadeOut( function() {
				self.allActions( 'enable' );
				self.$themePreview.css( 'visibility', 'visible' );
			} );
		} );
	};

	/**
	 * Manages the steps (tabs) of inspirations.
	 */
	this.steps = function() {
		self.$wrap.on( 'click', '.top-menu [data-step]', function() {
			var $link = $( this ),
				step = $link.attr( 'data-step' );

			if ( $link.hasClass( 'disabled' ) ) {
				return;
			} else {
				self.toggleStep( step );
			}
		} );
	};

	/**
	 * Init.
	 *
	 * @since 1.2.3
	 */
	this.init = function() {
		self.initFullScreen();

		$( function() {
			if ( '' === Inspiration.isDeploy ) {
				self.initInspirationsPage();
			} else {
				self.initDeployPage();
			}
		} );
	};

	/**
	 * Init the deploy page.
	 *
	 * @since 1.7.0
	 */
	this.initDeployPage = function() {
		$( window ).bind( 'load', self.onDeployFinish );
	};

	/**
	 * Steps to take when a deployment has finished.
	 *
	 * @since 1.7.0
	 */
	this.onDeployFinish = function() {

		/*
		 * Redirect the user to the My Inspirations page.
		 *
		 * Because of output buffering issues, we can't always guarantee that the user will see the
		 * deploying page show items as they're being installed. Sometimes they will just see the
		 * whole page load at once.
		 */
		window.location.href = Inspiration.myInspirationUrl;
	};

	this.initInspirationsPage = function() {
		self.bindClicks();

		var promptingForKey = self.$wrap.find( '#screen-api-key' ).length;
		if ( promptingForKey ) {
			$( '#container_boldgrid_api_key_notice' )

				// Remove the option to dismiss the notice.
				.find( '.notice-dismiss' )
				.remove()
				.end()

				// Ensure the page doesn't refresh after the key is saved successfully.
				.addClass( 'no-refresh' )

				// Move the notice to the "enter api key" step.
				.prependTo( '#screen-api-key' );

			// After the key has been saved successfully, wait 2 seconds, then go to design step.
			$( 'body' ).on( 'boldgrid-key-saved', function( e, data ) {
				self.ajax.setApiKey( data.api_key );
				self.ajax.setSiteHash( data.site_hash );

				self.initCategories();

				setTimeout( function() {
					self.toggleStep();
				}, 2000 );
			} );
		} else {
			self.initCategories();
		}

		self.toggleCheckbox();
		self.devicePreviews();
		self.backButton();
		self.mobileMenuToggle();
		self.subcategories();
		self.selectTheme();
		self.showAll();
		self.hoverColors();
		self.coinOptions();
		self.pagesetOptions();
		self.iframeLoad();
		self.steps();
		self.bindInstallModal();
		self.onResize();
		self.socialMediaDefaults();
		self.initFeatureToggles();
		self.bindOverwriteCheck();
		self.newKeyAdded();
	};

	/**
	 * Steps to take if a new key has been added.
	 *
	 * If we have a notice showing a new key has been added, we need to make sure it can be seen
	 * and that it is moved to the correct location.
	 *
	 * @since 1.7.0
	 */
	this.newKeyAdded = function() {
		var $notice = $( '.notice.bglib-key-added' );

		if ( ! $notice ) {
			return;
		}

		$notice

			// Admin notices within Inspirations are hidden by default. Make it visible.
			.addClass( 'bginsp-deploy-notice' )

			// Add the notice to the current visible screen.
			.prependTo( $( 'div[id^="screen-"]:visible' ) );
	};

	/**
	 * Require user to check box in order to overwrite site.
	 *
	 * @since 1.7.0
	 */
	this.bindOverwriteCheck = function() {
		$( '#screen-content-check-warning .next-step' ).on( 'click', function( e ) {
			var $confirm = $( '#bginsp_confirm_overwrite' ),
				checked = $confirm.find( 'input:checkbox' ).prop( 'checked' ),
				$notice = $confirm.find( 'span' );

			e.preventDefault();

			if ( checked ) {
				$notice.css( 'display', 'none' );
				return true;
			} else {
				$notice.css( 'display', 'block' );
				return false;
			}
		} );
	};

	/**
	 * Init the list of categories.
	 *
	 * @since 1.2.3
	 */
	this.initCategories = function() {
		var failureMessage, failAction, success_action;

		// Show a loading message to the user that we're fetching categories.
		self.$categories.html( Inspiration.fetchingCategories + ' <span class="spinner inline"></span>' );

		// Define a message for users when fetching themes has failed.
		failureMessage =
			Inspiration.errorFetchingCategories +
			' ' +
			Inspiration.tryFewMinutes +
			'<br />' +
			'<button class="button" id="try-categories-again">' +
			Inspiration.tryAgain +
			'</button>';

		// Display a 'Try again' message to the user if our call to get active categories fails.
		failAction = function() {
			self.$categories.html( failureMessage );
		};

		success_action = function( msg ) {
			var template = wp.template( 'init-categories' );

			self.categories = msg.result.data.categories;

			/*
			 * If our categories are not valid or we have 0 categories, show a 'Try again' message
			 * and abort.
			 */
			if ( self.categories === undefined || $.isEmptyObject( self.categories ) ) {
				self.$categories.html( failureMessage );

				return;
			}

			// Add the pseudo "Default" category, which is our theme showcase.
			self.categories.default = {
				subcategories: [
					{
						displayOrder: 1,
						name: 'Default',
						id: 'default'
					}
				]
			};

			self.$categories.html( template( self.categories ) );

			self.sortCategories( 'data-display-order' );

			self.initThemes();
		};

		self.ajax.ajaxCall(
			{ inspirations_mode: 'standard' },
			'get_categories',
			success_action,
			failAction
		);
	};

	/**
	 * Init Feature Toggles.
	 *
	 * For example, the toggle for Blog in step 2.
	 *
	 * @since 1.3.7
	 */
	this.initFeatureToggles = function() {
		$( '#blog-toggle' )
			.toggles( {
				checkbox: $( '[name="install-blog"]' ),
				click: false,
				drag: false,
				text: {
					on: '',
					off: ''
				},
				height: 15,
				width: 40
			} )
			.find( '.toggle-on' )
			.addClass( 'blue' );
	};

	/**
	 * @summary Init pagesets.
	 *
	 * After the ajax request comes back with pagesets, choose the base pageset and continue to load
	 * that site into the iframe.
	 *
	 * @since 1.2.5
	 */
	this.initPagesets = function() {

		// Define a message for users when fetching pagesets has failed.
		var failureMessage =
				Inspiration.errorFetchingPagesets +
				' ' +
				Inspiration.tryFewMinutes +
				'<br />' +
				'<button class="button" id="try-pagesets-again">' +
				Inspiration.tryAgain +
				'</button>',
			categoryId = self.$theme.closest( '.theme' ).attr( 'data-category-id' ),
			pagesetFail,
			pagesetSuccess;

		// Reset any previous error messages.
		self.$contentNotices.html( '' );

		// Error function: If we failed to retrieve pagesets, show a 'Try again' message to the user.
		pagesetFail = function() {
			self.$contentNotices.html( failureMessage );
		};

		// Success function: We successfully fetched pagesets.
		pagesetSuccess = function( msg ) {
			var template = wp.template( 'pagesets' );

			// If we have 0 pagesets, show a try again notice and abort.
			if ( 0 === $( msg.result.data.pageSets ).length ) {
				self.$contentNotices.html( failureMessage );
				return;
			}

			$( '#pageset-options' ).html( template( msg.result.data.pageSets ) );

			self.$pageset = $( 'input[name="pageset"]:checked' );

			self.loadBuild();
		};

		self.ajax.ajaxCall(
			{ category_id: categoryId },
			'get_category_page_sets',
			pagesetSuccess,
			pagesetFail
		);
	};

	/**
	 * @summary Init Themes.
	 *
	 * @since 1.2.3
	 */
	this.initThemes = function() {
		var template = wp.template( 'theme' ),
			data = {
				site_hash: self.configs.site_hash,
				theme_release_channel: self.themeReleaseChannel,
				inspirations_version: Inspiration.inspirationsVersion
			},
			getGenericSuccess,
			getGenericFail,
			getGenericComplete,
			failureMessage;

		self.allActions( 'disable' );

		// Define a message for users when fetching themes has failed.
		failureMessage =
			Inspiration.errorFetchingThemes +
			' ' +
			Inspiration.tryFewMinutes +
			'<br />' +
			'<button class="button" id="try-themes-again">' +
			Inspiration.tryAgain +
			'</button>';

		// Show a loading message to the user that we're fetching themes.
		self.$themes.html( Inspiration.fetchingThemes + ' <span class="spinner inline"></span>' );

		/*
		 * This is the error function passed to our api call to get generic themes. If there is a
		 * failure, we'll display a 'Try again' notice to the user.
		 */
		getGenericFail = function() {
			self.$themes.html( failureMessage );
		};

		getGenericSuccess = function( msg ) {
			var defaultBuilds = 0;

			/*
			 * Review the count of themes returned.
			 *
			 * If 0 themes are returned, show a 'Try again' message and abort.
			 * Else, assign themes to self.genericBuilds and sort them.
			 */
			if ( 0 === msg.result.data.length ) {
				self.$themes.html( failureMessage );
				return;
			} else {
				self.genericBuilds = msg.result.data;
				self.sortAll();
			}

			// Empty the themes container. We'll fill it with themes below.
			self.$themes.empty();

			_.each( self.genericBuilds, function( build ) {

				/*
				 * Default themes are printed twice. This allows for
				 * "Pavilion / Real Estate" to show in both the Default category
				 * and the Real Estate category.
				 */
				if ( build.isDefault ) {
					defaultBuilds++;
					self.$themes.append( template( { configs: IMHWPB.configs, build: build } ) );
					build.isDefault = false;
				}

				self.$themes.append( template( { configs: IMHWPB.configs, build: build } ) );
			} );

			if ( 0 === defaultBuilds ) {
				self.removeCategory( 'default' );
			}

			self.sortThemes( 'data-all-order' );

			$( 'img.lazy' ).lazyload( { threshold: 400 } );

			self.fancybox();
		};

		/**
		 * Complete action.
		 *
		 * @since 1.7.0
		 */
		getGenericComplete = function() {
			self.allActions();
		};

		self.ajax.ajaxCall( data, 'get_generic', getGenericSuccess, getGenericFail, getGenericComplete );
	};

	/**
	 * Load a new build on the Content tab.
	 *
	 * @since 1.2.3
	 */
	this.loadBuild = function() {
		var data,
			successAction,
			failAction,
			failureMessage = Inspiration.errorBuildingPreview + ' ' + Inspiration.tryFewMinutes,
			timeoutMessage = Inspiration.previewTimeout + ' ' + Inspiration.tryFewSeconds,
			tryAgainButton =
				'<button class="button" id="try-build-again">' + Inspiration.tryAgain + '</button>',

			// Should our request for a build be for a generic build?
			requestGeneric = false,
			hasBlog = $( '[name="install-blog"]' ).is( ':checked' );

		/*
		 * By default, we will not request a generic build. The only time we will request a generic
		 * build is if we're looking at all the default values:
		 * # Pageset:		Default
		 * # Coin budget:	20
		 * # Blog:			False
		 */
		if ( '1' === self.$pageset.attr( 'data-is-default' ) && ! hasBlog ) {
			requestGeneric = true;
		}

		// Disable all actions.
		self.allActions( 'disable' );

		// Reset any previous error messages.
		self.$contentNotices.html( '' );

		// Load our loading graphic.
		self.$themePreview.css( 'visibility', 'hidden' );
		$( '#screen-content .boldgrid-loading' ).fadeIn();

		failAction = function( msg ) {
			var message = failureMessage;

			if ( 'timeout' === msg.statusText ) {
				message = timeoutMessage;
			}

			self.loadBuildFail( message + '<br />' + tryAgainButton );

			self.allActions( 'enable' );
		};

		successAction = function( msg ) {
			var $screenContent = $( '#screen-content' ),
				$iframe = $screenContent.find( 'iframe#theme-preview' ),
				url;

			/*
			 * If there was an error building the site, show the user a try again button and abort.
			 *
			 * Else, load the preview for them.
			 */
			if ( 200 !== msg.status ) {
				self.loadBuildFail( failureMessage + '<br />' + tryAgainButton );
				return;
			} else {
				url = msg.result.data.profile.preview_url;

				$iframe.attr( 'src', url ).attr( 'data-build-cost', msg.result.data.profile.coins );

				$( '[name=boldgrid_build_profile_id]' ).val( msg.result.data.profile.id );

				self.highlightDeviceButton();
			}

			self.allActions( 'enable' );
		};

		data = {
			build_profile_id: self.$theme.closest( '.theme' ).attr( 'data-build-id' ),
			theme_id: self.$theme.closest( '.theme' ).attr( 'data-theme-id' ),
			cat_id: self.$theme.closest( '.theme' ).attr( 'data-category-id' ),
			sub_cat_id: self.$theme.closest( '.theme' ).attr( 'data-sub-category-id' ),
			page_set_id: self.$pageset.attr( 'data-page-set-id' ),
			pde: self.$theme.closest( '.theme' ).attr( 'data-pde' ),
			wp_language: 'en-US',

			// By default, option to change coin budget has been removed in x.x.x. Defaulting to 20.
			coin_budget: 20,
			theme_version_type: self.themeReleaseChannel,
			page_version_type: self.themeReleaseChannel,
			site_hash: self.configs.site_hash,
			inspirations_mode: 'standard',
			is_generic: requestGeneric,
			has_blog: hasBlog
		};

		// Set form.
		$( '[name=boldgrid_build_profile_id]' ).val( data.build_profile_id );
		$( '[name=boldgrid_cat_id]' ).val( data.cat_id );
		$( '[name=boldgrid_sub_cat_id]' ).val( data.sub_cat_id );
		$( '[name=boldgrid_theme_id]' ).val( data.theme_id );
		$( '[name=boldgrid_page_set_id]' ).val( data.page_set_id );
		$( '[name=boldgrid_api_key_hash]' ).val( data.site_hash );
		$( '[name=boldgrid_pde]' ).val( data.pde );
		$( '[name=coin_budget]' ).val( data.coin_budget );

		self.ajax.ajaxCall( data, 'get_build_profile', successAction, failAction );
	};

	/**
	 * @summary Build preview failed to load.
	 *
	 * @since 1.3.9
	 *
	 * @param string Notice to display to user.
	 */
	this.loadBuildFail = function( notice ) {
		$( '#screen-content .boldgrid-loading' ).fadeOut( function() {
			self.$contentNotices.html( notice );
			self.allActions( 'enable' );
		} );
	};

	/**
	 * Initialize the full screen feature.
	 *
	 * @since 1.7.0
	 */
	this.initFullScreen = function() {
		$( 'html' ).on( 'click', '.top-menu .notice-dismiss', function() {
			$( 'body' ).toggleClass( 'bginsp-full-screen' );
		} );
	};

	/**
	 * Toggle steps.
	 *
	 * @since 1.3
	 */
	this.toggleStep = function( step ) {
		var $thisStep, previewHeight, $activeStep, screens;

		// If step not passed in as a string (it will be the "next" button instead), get the next step.
		if ( 'string' !== typeof step ) {
			$activeStep = self.$wrap.find( '[id^="screen-"]:visible' );
			step = $activeStep
				.nextAll( '[id^="screen-"]:hidden' )
				.first()
				.attr( 'id' )
				.slice( 7 );
		}

		// $thisStep refers to the link in the menu.
		$thisStep = $( '[data-step="' + step + '"]' );

		previewHeight = 'content' === step ? '100%' : '99%';

		// Not all steps will have a menu item in the top.
		if ( $thisStep.length ) {

			/*
			 * Once you've been to a step, remove it's disabled settings.
			 *
			 * The disabled class means the link isn't clickable. The attribute data-disabled means the
			 * link shouldn't be enabled.
			 *
			 * For example, if we're waiting on something to load, we'll disable all steps (add disabled
			 * classs). After that item loads, we'll enable all the steps that should be enabled (those
			 * that don't have data-disabled).
			 */
			$thisStep.removeClass( 'disabled' ).removeAttr( 'data-disabled' );

			// Toggle .active class for steps at the top of the page.
			$( '[data-step]' ).removeClass( 'active next boldgrid-orange-important' );
			$thisStep
				.addClass( 'active boldgrid-orange-important' )
				.next()
				.addClass( 'next' );
		}

		// Toggle the step's container.
		self.$wrap.find( '[id^="screen-"]' ).addClass( 'hidden' );
		$( '#screen-' + step ).removeClass( 'hidden' );

		// Toggle the menu's class based on the screen.
		screens = self.getAllScreens();
		self.$topMenu.removeClass( screens.join( ' ' ) );
		self.$topMenu.addClass( step );

		/*
		 * Misc steps.
		 *
		 * Configure scroll position.
		 * Confiure top menu class.
		 */
		switch ( step ) {
			case 'design':
				$( document ).scrollTop( self.scrollPosition );

				/*
				 * Sometimes the screenshot placeholders will be fully loaded before the user gets
				 * to the design step. In this case, we need to trigger the screenshots to load.
				 */
				$( 'img.lazy' ).lazyload( { threshold: 400 } );

				break;
			case 'api-key':

				// We hid the key prompt on page load. Now is the time to show it.
				$( '#container_boldgrid_api_key_notice' ).show();
				break;
			default:
				$( document ).scrollTop( 0 );
				break;
		}

		/*
		 * Ensure preview iframe has scrollbars.
		 *
		 * Issue appears to be due to this Chrome bug:
		 * https://bugs.chromium.org/p/chromium/issues/detail?id=641881
		 *
		 * Essentially we're changing the height from 99% to 100%, which gets them showing again.
		 */
		self.$themePreview.css( 'height', previewHeight );
	};

	/**
	 * @summary Toggle a sub category.
	 *
	 * Show only themes belonging to a sub category.
	 *
	 * @since 1.2.3
	 */
	this.toggleSubCategory = function( subCategoryId ) {
		var fancyboxAnchor = '.theme-screenshot a',
			isDefault = 'default' === subCategoryId,
			showAll = '0' === subCategoryId,
			hideSelector = '.theme[data-is-default!="true"]',
			showSelector = '.theme[data-is-default="true"]',
			$categoryName = $( '.theme-name .sub-category-name' );

		if ( ! isDefault ) {
			hideSelector = showSelector + ',.theme[data-sub-category-id!="' + subCategoryId + '"]';
			showSelector = '.theme[data-sub-category-id="' + subCategoryId + '"]';
		}

		$categoryName.toggle( isDefault || showAll );

		if ( '0' === subCategoryId ) {
			$( '.theme[data-sub-category-id]' )
				.removeClass( 'hidden' )

				// Add fancybox class, which adds theme back to the gallery.
				.find( fancyboxAnchor )
				.addClass( 'fancybox' );

			self.sortThemes( 'data-all-order' );
		} else {
			$( showSelector )
				.removeClass( 'hidden' )

				/*
				 * Add the fancybox class back, otherwise thumbnail will link directly to thumbnail,
				 * rather than open thumbnail in fancybox gallery.
				 */
				.find( fancyboxAnchor )
				.addClass( 'fancybox' );

			$( hideSelector )
				.addClass( 'hidden' )
				.appendTo( '.themes' )

				// Remove fancybox class, which removes theme from the gallery.
				.find( fancyboxAnchor )
				.removeClass( 'fancybox' );

			self.sortThemes( 'data-category-order' );
		}

		$( 'img.lazy' ).lazyload( { threshold: 400 } );
	};

	/**
	 * @summary Validate the survey.
	 *
	 * Currently only validates the email address.
	 *
	 * @since 1.3.9
	 */
	this.validateContact = function() {
		var $email = $( '[name*=survey\\[email\\]\\[value\\]]' ),
			displayEmail = false === $( '[name*=survey\\[email\\]\\[do-not-display\\]]' ).is( ':checked' ),
			$invalidMessage = $email.closest( '.survey-field' ).find( '.invalid' ),
			isValidEmail;

		// Trim all text inputs.
		$( '.survey-field' ).boldgridFindAndTrim();

		isValidEmail = BoldGrid.Utility.validateEmail( $email.val() );

		// The user does not want to display their email address, don't worry about validating it.
		if ( ! displayEmail ) {
			return true;
		}

		if ( isValidEmail ) {
			$invalidMessage.hide();
		} else {
			$invalidMessage.show();
		}

		return isValidEmail;
	};

	/**
	 * Get all of the screens.
	 *
	 * @since 1.7.0
	 *
	 * @return array An array of screen ids.
	 */
	this.getAllScreens = function() {
		var screens = [];

		$( '.wrap [id^="screen-"]' ).each( function() {
			screens.push(
				$( this )
					.attr( 'id' )
					.slice( 7 )
			);
		} );

		return screens;
	};

	self.init();
};

IMHWPB.InspirationsDesignFirst( jQuery, IMHWPB.configs );
