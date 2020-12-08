var IMHWPB = IMHWPB || {};

IMHWPB.StockImageSearch = function( configs, $ ) {
	var self = this;

	this.configs = configs;

	this.api_url = this.configs.asset_server;
	this.api_key = this.configs.api_key;

	this.api_param = 'key';
	this.api_key_query_str = this.api_param + '=' + this.api_key;

	this.last_query = '';
	this.page = 1;

	this.lang = BoldGridImageSearch;

	// include additional submodules
	self.ajax = new IMHWPB.Ajax( configs );
	self.baseAdmin = new IMHWPB.BaseAdmin();

	$c_imhmf = jQuery( '.imhwpb-media-frame' );
	$c_sr = jQuery( '#search_results', $c_imhmf );

	jQuery( function() {

		// When the page has finished loading, enable the search button.
		$( '#image_search .button-primary', $c_imhmf ).prop( 'disabled', false );

		// event handler: user clicks search
		jQuery( '#image_search', $c_imhmf ).on( 'submit', function() {
			self.reset_search();
			self.initiate_stock_image_search();
			return false;
		} );

		// event handler: user filters by license attribution
		jQuery( '#attribution', $c_imhmf ).on( 'click', function( value ) {
			self.toggle_search_results_by_requires_attribution();
		} );

		jQuery( '#search_results', $c_imhmf ).scroll( function() {
			self.search_results_scroll();
		} );
	} );

	// this function is triggered by the click/button/search event handler
	this.initiate_stock_image_search = function() {
		var query = jQuery( '#media-search-input', $c_imhmf ).val(),
			attribution = jQuery( '#attribution', $c_imhmf ).is( ':checked' ),
			resultsCount = 0,
			$imageProviderId = $( '#image_search [name="image_provider_id"]' );

		// if we're searching for a different word, reset the search
		if ( '' != self.last_query && query != self.last_query ) {
			self.reset_search();
		}
		self.last_query = query;

		// prevent empty searches
		if ( '' == query.trim() ) {
			alert( self.lang.noSearchTerm );
			return false;
		}

		// Are we already search?
		if ( 1 == self.currently_searching ) {
			return false;
		} else {
			self.currently_searching = 1;
		}

		// Show "searching" message
		if ( 1 == self.page ) {
			jQuery( $c_sr ).append(
				'<div class=\'loading_message pointer\'>' + self.lang.searching + '...</div>'
			);
		} else {
			jQuery( '.loading_message', $c_sr ).html( self.lang.searching + '...' );
		}

		// setup our variables
		var data = {
			query: query,
			free: jQuery( '#free', $c_imhmf ).val(),
			attribution: attribution,
			paid: jQuery( '#paid', $c_imhmf ).val(),
			palette: jQuery( '#palette', $c_imhmf ).val(),
			page: self.page,
			image_provider_id:
				! $imageProviderId.length || '-1' === $imageProviderId.val() ? null : $imageProviderId.val()
		};

		var api_call_image_search_success_action = function( msg ) {
			resultsCount = msg.result.data.length;

			/*
			 * If the user has unchecked "Attribution", remove any images needing attribution from
			 * the total count of search results.
			 *
			 * todo: The logic / code in much of this file is in need of a rewrite.
			 */
			if ( false === attribution && 0 < msg.result.data.length ) {
				$( msg.result.data ).each( function() {
					if ( true === this.requires_attribution ) {
						resultsCount--;
					}
				} );
			}

			// if we have search results
			if ( 0 < resultsCount ) {
				var source = jQuery( '#search-results-template' ).html();
				var template = Handlebars.compile( source );
				jQuery( '#search_results', $c_imhmf ).append( template( msg.result ) );

				// event handler: user clicks search result
				jQuery( 'li.attachment', $c_imhmf ).on( 'click', function() {
					self.event_handler_search_result_click( this );
				} );

				var $search_results = jQuery( '#search_results', $c_imhmf );

				jQuery( '.loading_message', $c_sr )
					.appendTo( $c_sr )
					.css( 'display', 'inherit' )
					.html( self.lang.scrollDown )
					.on( 'click', function() {
						self.initiate_stock_image_search();
						return false;
					} );

				// update the page value (page number for pagination)
				self.page++;

				// else [we have no search results]
			} else {
				var $search_results = jQuery( '#search_results', $c_imhmf );

				if ( '1' == self.page ) {
					var message = self.lang.noSearchResults;
				}

				var no_search_results = '1' == self.page ? self.lang.noSearchResults : self.lang.noMore;

				jQuery( '.loading_message', $c_sr )
					.appendTo( $c_sr )
					.css( 'display', 'inherit' )
					.html( no_search_results );
			}

			self.currently_searching = 0;

			// Toggle attribution:
			self.toggle_search_results_by_requires_attribution( false );
		};

		self.ajax.ajaxCall( data, 'image_search', api_call_image_search_success_action );
	};

	/**
	 *
	 */
	this.event_handler_search_result_click = function( result ) {
		var image_provider_id = jQuery( result ).data( 'image-provider-id' ),
			id_from_provider = jQuery( result ).data( 'id-from-provider' ),
			attachment_details = jQuery( '#attachment_details', $c_imhmf ),

			// Count of image sizes that have been flagged as recommended.
			recommendedCount,

			// The select element with options of different image sizes.
			$imageSelect;

		// show loading message...
		jQuery( attachment_details )
			.empty()
			.html(
				'<div class=\'loading_message white-bg\'><span class=\'spinner is-active\'></span>' +
					self.lang.loadingImageDetails +
					'</div>'
			);

		/**
		 * Toggle 'details selected' classes
		 */
		jQuery( 'li.attachment', $c_imhmf ).each( function() {
			if ( this != result ) {
				jQuery( this ).removeClass( 'details selected' );
			}
		} );
		jQuery( result ).toggleClass( 'details selected' );

		// configure data to send with ajax request
		var data = {
			image_provider_id: image_provider_id,
			id_from_provider: id_from_provider
		};

		// after ajax command, run this
		var api_call_image_get_details_success_action = function( msg ) {

			/*
			 * Determine if we had a successful call. Currently determined by
			 * whether or not an array of downloadable sizes was returned.
			 */
			var sizes = msg.result.data.sizes;
			var has_sizes = true == jQuery.isArray( sizes ) && 0 < jQuery( sizes ).length ? true : false;

			if ( has_sizes ) {

				/*
				 * We successfully fetched the details of the image. Display
				 * those attachment details for the user.
				 */
				var source = jQuery( '#attachment-details-template' ).html();
				var template = Handlebars.compile( source );
				jQuery( '#attachment_details', $c_imhmf ).html( template( msg.result.data ) );

				self.selectRecommendedImage();

				// After the attachment details pane has been loaded, set a few variables.
				$imageSelect = $( '#image_size' );
				recommendedCount = $imageSelect.find( 'option.recommended_image_size' ).length;

				/*
				 * If we have no recommended image sizes, by default select the last / largest image
				 * in the results.
				 *
				 * If we did not do this, when searching for large background images we would see
				 * 75px by 75px as the default option rather than a large image we need such as
				 * 1920px by 1080.
				 */
				if ( 0 === recommendedCount ) {
					$imageSelect.find( 'option:last' ).prop( 'selected', true );
				}

				// PreSelect Alignment if replacing an image
				self.select_image_alignment();

				/**
				 * Display the pointer if applicable.
				 */
				if ( 'undefined' != typeof WPHelpPointerIndex ) {
					var pointer_index = WPHelpPointerIndex['#image_size'];
					if ( 'undefined' != typeof pointer_index ) {
						if ( 'yes' != WPHelpPointer.pointers[pointer_index]['is-dismissed'] ) {
							setTimeout( function() {
								self.baseAdmin.show_pointer( jQuery( '#imaeg_size' ), '#image_size' );
							}, 1000 );
						}
					}
				}

				// event handler: user clicks "Insert into page"
				jQuery( '#download_and_insert_into_page' ).on( 'click', function() {
					self.download( $( this ) );
				} );
			} else {

				/*
				 * There was an issue fetching the image details. Display an
				 * applicable message.
				 */
				var source = jQuery( '#attachment-details-error-template' ).html();
				var template = Handlebars.compile( source );
				jQuery( '#attachment_details', $c_imhmf ).html( template() );
			}
		};

		/**
		 * ajax / reach out for the attachment details
		 */
		self.ajax.ajaxCall( data, 'image_get_details', api_call_image_get_details_success_action );
	};

	/**
	 * @summary Determine where we are downloading an image from.
	 *
	 * For example, we can be downloading it from within the Customizer, Dashboard > Media, etc.
	 *
	 * @since 1.1.9
	 *
	 * @return string.
	 */
	this.getAction = function() {
		var inCustomizer = 'dashboard-customizer' === self.baseAdmin.GetURLParameter( 'ref' ),

			// The BoldGrid Connect Search tab.
			$bgcsTab = $( '.media-menu-item.boldgrid-connect-search:visible', parent.document ),
			action = null;

		if ( 'dashboard-media' === self.baseAdmin.GetURLParameter( 'ref' ) ) {
			action = 'dashboard-media';
		} else if (
			'undefined' !== parent.wp.media.frame &&
			'replace-image' === parent.wp.media.frame._state
		) {
			action = 'replace-image';
		} else if ( 'section-background' === $bgcsTab.attr( 'data-added-by' ) ) {
			action = 'section-background';
		} else if ( 'add-to-gallery' === $bgcsTab.attr( 'data-added-by' ) ) {
			action = 'add-to-gallery';
		} else if ( 'create-gallery' === $bgcsTab.attr( 'data-added-by' ) ) {
			action = 'create-gallery';
		} else if ( parent.document.body.classList.contains( 'block-editor-page' ) ) {
			action = 'gutenberg';
		} else if ( 'function' === typeof parent.window.send_to_editor && ! inCustomizer ) {
			action = 'editor';
		} else if ( inCustomizer ) {
			action = 'customizer';
		}

		return action;
	};

	/**
	 * @summary Get a theme's header / background recommended width.
	 *
	 * For example, in the Customizer when you go to change your background image, it may
	 * say something like:
	 *
	 * Suggested image dimensions: 1200 × 280
	 *
	 * Not all themes do this, but for those that do, this function will parse that text and return
	 * the recommended width.
	 *
	 * @todo: Parsing the text to get the width isn't the best approach, but couldn't find these
	 * values in standard variables, such as wp.customize;
	 *
	 * @since 1.2.6
	 */
	this.getRecommendedWidth = function() {
		var $instructions = $( '.instructions', window.parent.document )
				.last()
				.html(),
			recommendedDimensions,
			recommendedWidth = null;

		if ( 'undefined' !== typeof $instructions && $instructions.length ) {
			recommendedDimensions = $instructions.split( ':' );

			// Note, that's not an 'x' below, it's an '×'.
			recommendedWidth = recommendedDimensions[1].split( '×' );
			recommendedWidth = parseInt( recommendedWidth[0].trim() );
		}

		return recommendedWidth;
	};

	/**
	 * Set the alignment to the current image's alignment
	 */
	this.select_image_alignment = function() {
		if ( parent.tinymce && parent.tinymce.activeEditor ) {
			var $current_selection = jQuery( parent.tinymce.activeEditor.selection.getNode() );
			var $alignment_sidebar = jQuery( '.attachments-browser select.alignment' );

			// Determine if the current selection has a class.
			if ( $current_selection.is( 'img' ) ) {
				var classes = $current_selection.attr( 'class' );
				var current_classes = [];
				if ( classes ) {
					current_classes = $current_selection.attr( 'class' ).split( /\s+/ );
				}

				var value_selection = 'none';
				jQuery.each( current_classes, function( index, class_item ) {
					if ( 'aligncenter' == class_item ) {
						value_selection = 'center';
						return false;
					} else if ( 'alignnone' == class_item ) {
						value_selection = 'none';
						return false;
					} else if ( 'alignright' == class_item ) {
						value_selection = 'right';
						return false;
					} else if ( 'alignleft' == class_item ) {
						value_selection = 'left';
						return false;
					}
				} );

				if ( $alignment_sidebar.length ) {
					$alignment_sidebar.val( value_selection ).change();
				}
			}
		}
	};

	/**
	 * @summary Select the recommended image size.
	 *
	 * Based on different scenarios, we'll recommend different image sizes to the user. For example,
	 * if the user is choosing a background image, they'll need a much larger image than if they
	 * were downloading an image to be used within a page.
	 *
	 * This function used to be in handle-bar-helpers, but over time more functionality has been
	 * needed, and so it has been moved here (and modified).
	 *
	 * @since 1.2.6
	 */
	this.selectRecommendedImage = function() {
		var action = self.getAction();

		$( '#image_size > option', $c_imhmf ).each( function() {
			var $option = $( this ),
				width = parseInt( $option.attr( 'data-width' ) ),
				low = 0,
				high = 0,
				originalHtml;

			// Based upon our action, determine the low and high range for our recommended image width.
			switch ( action ) {
				case 'editor':
				case 'gutenberg':
					low = 400;
					high = 1100;
					break;
				case 'customizer':
				case 'section-background':
					recommendedWidth = self.getRecommendedWidth();

					/*
					 * If the theme suggests a width, then use that.
					 *
					 * Else, suggest width based upon current monitor statistics:
					 * https://www.w3counter.com/globalstats.php
					 */
					if ( recommendedWidth ) {
						low = recommendedWidth - 300;
						high = recommendedWidth + 500;
					} else {
						low = 1024;
						high = 1920;
					}

					break;
			}

			// If this is a recommended image, flag it as so.
			if ( width >= low && width <= high ) {
				originalHtml = $option.html();

				$option
					.addClass( 'recommended_image_size' )
					.html( originalHtml + ' &#10004; Recommended size' );
			}
		} );

		// Select the last recommended image in the list.
		$( '#image_size > option.recommended_image_size', $c_imhmf )
			.last()
			.prop( 'selected', true );
	};

	/**
	 * @summary Download an image from the search results.
	 *
	 * @since 1.1.9
	 *
	 * @param jQuery object $anchor The "Download" button the user clicked.
	 */
	this.download = function( $anchor ) {
		var $c_ad = $( '#attachment_details' ),
			$image_size_option_selected = $( '#image_size option:selected', $c_imhmf ),

			// Are we currently downloading an image?
			$currently_downloading = $( '#currently_downloading_image', $c_ad );

		// Are we already downloading an image? If so, abort. Else, flag that we are.
		if ( '1' === $currently_downloading.val() ) {
			return;
		} else {
			$currently_downloading.val( '1' );
		}

		$anchor.attr( 'disabled', true ).text( self.lang.downloading );

		var data = {
			action: 'download_and_insert_into_page',
			id_from_provider: jQuery( '#id_from_provider', $c_imhmf ).val(),
			image_provider_id: jQuery( '#image_provider_id', $c_imhmf ).val(),
			image_size: jQuery( '#image_size', $c_imhmf ).val(),
			post_id: IMHWPB.post_id,
			title: jQuery( '#title', $c_ad ).val(),
			caption: jQuery( '#caption', $c_ad ).val(),
			alt_text: jQuery( '#alt_text', $c_ad ).val(),
			description: jQuery( '#description', $c_ad ).val(),
			alignment: jQuery( '#alignment', $c_ad ).val(),
			width: $image_size_option_selected.attr( 'data-width' ),
			height: $image_size_option_selected.attr( 'data-height' )
		};

		jQuery.post( ajaxurl, data, function( response ) {
			response = JSON.parse( response );

			$anchor.text( self.lang.imageDownloaded );

			self.downloadSuccess( response, $anchor );
		} );
	};

	/**
	 * Take different actions based upon where we're downloading the image from.
	 *
	 * @since 1.1.9
	 *
	 * @param object        response Our response from ajax / downloading our image.
	 * @param jQuery object $anchor   A reference to our Download button.
	 */
	this.downloadSuccess = function( response, $anchor ) {
		var action = self.getAction(),
			block,
			placeholder,
			$selectedBlock,
			isClassicBlock;

		switch ( action ) {
			case 'gutenberg':

				// @todo Works, but there probably is an easier way to get the active block.
				$selectedBlock = $( '.wp-block.is-selected', parent.document );

				placeholder = $selectedBlock.find( '[data-block]' ).attr( 'data-block' );
				isClassicBlock = 'core/freeform' === $selectedBlock.attr( 'data-type' );

				block = parent.wp.blocks.createBlock( 'core/image', {
					url: response.attachment_url
				} );

				if ( isClassicBlock ) {
					parent.window.send_to_editor( response.html_for_editor );
					parent.wp.media.frame.close();
				} else {
					parent.wp.media.frame.close();
					if ( placeholder === undefined ) {
						parent.wp.data.dispatch( 'core/editor' ).insertBlock( block );
					} else {
						parent.wp.data.dispatch( 'core/editor' ).replaceBlock( placeholder, block );
					}
				}

				break;
			case 'editor':
				parent.window.send_to_editor( response.html_for_editor );

				break;
			case 'dashboard-media':
				var anchor_to_view_attachment_details_media_library =
					'<a href="post.php?post=' +
					response.attachment_id +
					'&action=edit" target="_parent" class="button button-small view-image-in-library">' +
					self.lang.viewInLibrary +
					'</a>';

				$anchor.after( anchor_to_view_attachment_details_media_library );

				break;
			case 'replace-image':
			case 'customizer':
			case 'section-background':
			case 'add-to-gallery':
			case 'create-gallery':
				self.refresh_media_library();
				self.whenInLibrary( response.attachment_id, action );

				break;
		}
	};

	/**
	 * Refresh the images in the library.
	 */
	this.refresh_media_library = function() {
		var haveCollection =
				'undefined' !== typeof window.parent.wp.media.frame.content.get().collection,

			// Do we have a library?
			haveLibrary = 'undefined' !== typeof window.parent.wp.media.frame.library;

		if ( null !== window.parent.wp.media.frame.content.get() && haveCollection ) {
			window.parent.wp.media.frame.content.get().collection.props.set( {
				ignore: +new Date()
			} );
			window.parent.wp.media.frame.content.get().options.selection.reset();
		} else if ( haveLibrary ) {
			window.parent.wp.media.frame.library.props.set( {
				ignore: +new Date()
			} );
		}
	};

	/**
	 *
	 */
	this.reset_search = function() {
		self.page = 1;
		self.last_query = '';

		jQuery( $c_sr ).empty();
	};

	/**
	 *
	 */
	this.search_results_scroll = function() {
		var scrollTop = jQuery( '#search_results', $c_imhmf ).scrollTop();
		var height = jQuery( '#search_results', $c_imhmf ).height();
		var scrollHeight = jQuery( '#search_results', $c_imhmf )[0].scrollHeight;
		var pixels_bottom_unseen = scrollHeight - height - scrollTop;
		var loading_message_outer_height = jQuery( '.loading_message', $c_sr ).outerHeight( false );

		if ( pixels_bottom_unseen <= loading_message_outer_height ) {
			self.initiate_stock_image_search();
		}
	};

	/**
	 * @param bool fadeOut Should we fade out images or immediately hide them.
	 */
	this.toggle_search_results_by_requires_attribution = function( fadeOut ) {

		// determine whether or not "Attribution" is checked
		need_to_show = jQuery( '#attribution', $c_imhmf ).is( ':checked' );

		// If no value is passed in, fadeOut should be true.
		fadeOut = fadeOut === undefined ? true : fadeOut;

		// loop through each image in the search results
		jQuery( '#search_results li', $c_imhmf ).each( function( index, li ) {

			// grab the value of "data-requires-attribution"
			var li_requires_attribution = jQuery( li ).data( 'requires-attribution' );

			// if this image requires attribution
			if ( '1' == li_requires_attribution ) {

				// If the user checked "attribution"
				if ( true == need_to_show ) {

					// then fade this image in
					jQuery( li ).fadeIn();

					// else [the user unchecked "attribution"
				} else {

					// then fade this image out
					if ( fadeOut ) {
						jQuery( li ).fadeOut();
					} else {
						jQuery( li ).hide();
					}
				}
			}
		} );
	};

	/**
	 * @summary Take action when an image is found in the Media Library.
	 *
	 * This method is triggered after we have downloaded an image. We wait for the new image to
	 * appear in the Media Library, then we take action.
	 *
	 * This method uses an Interval to check the Media Library for the new image. The Interval is
	 * used as there does not seem to be an action triggered after a successful Media Library refresh.
	 *
	 * @since 1.1.9
	 *
	 * @param int attachmentId ID of the attachment we're looking for.
	 * @param string action The location we're downloading the image from. For example, Customizer.
	 */
	this.whenInLibrary = function( attachmentId, action ) {
		var // How much time has elapsed since we began looking for the image?
			elapsed = 0,

			// Wait up to 10 seconds for the new image to appear in the  library.
			elapsedLimit = 10000,

			// Has the new image been found in the library?
			found,

			// How often should we check to see if the new image is in the library.
			interval = 100,

			// An Interval to check for the new image in the library
			checkInLibrary,

			// A reference to the attachment in the library.
			$attachment;

		checkInLibrary = setInterval( function() {

			// Has our attachment been found in the Media Library?
			found =
				0 <
				$( '.attachments', window.parent.document ).children( '[data-id=' + attachmentId + ']' ).length;

			/*
			 * Take action based upon whether or not our new image is in the Media Library.
			 *
			 * If the image has been found:
			 * # Clear the interval, no need to keep looking.
			 * # Run additional actions based upon whether we're in the customizer or replacing an image.
			 *
			 * Else:
			 * # Increase the elapsed time. If we've reached our limit, clear the interval and abort.
			 */
			if ( found ) {
				clearInterval( checkInLibrary );

				$attachment = $( '.attachments', window.parent.document )
					.children( '[data-id=' + attachmentId + ']' )
					.find( '.attachment-preview' );

				switch ( action ) {
					case 'replace-image':
					case 'customizer':
					case 'section-background':
					case 'add-to-gallery':
					case 'create-gallery':

						// In the media library, click the image that was just downloaded. Then, click the select button.
						$attachment.click();
						$( '.media-toolbar .media-button.button-primary', window.parent.document ).click();

						break;
				}
			} else {
				elapsed += interval;

				if ( elapsed >= elapsedLimit ) {
					clearInterval( checkInLibrary );
				}
			}
		}, interval );
	};
};

new IMHWPB.StockImageSearch( IMHWPB.configs, jQuery );
