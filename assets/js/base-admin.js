/**
 * This file is intended for js that will appear on every admin page.
 */

var IMHWPB = IMHWPB || {};

IMHWPB.BaseAdmin = function( $ ) {
	var self = this;

	// References to use as selectors.
	self.$wrap = jQuery( '.wrap' );
	self.$wrap_header = jQuery( 'h1', self.$wrap );

	jQuery( function() {

		// Within Dashboard >> Media >> BoldGrid Connect Search, self.init is
		// not a function. Therefore, only self.init if self.init is found to be
		// a function.
		if ( 'function' === typeof self.init ) {
			self.init();
		}
	} );

	/**
	 * Init.
	 *
	 * @since 1.0.10
	 */
	this.init = function() {

		/*
		 * Avoid this error: Uncaught TypeError: self.update_customizer_link is
		 * not a function.
		 *
		 * Only call self.update_customizer_link if it is a function.
		 */
		if ( 'function' === typeof self.update_customizer_link ) {
			self.update_customizer_link();
		}
	};

	/**
	 * Get parameter from URL
	 *
	 * @link http://www.jquerybyexample.net/2012/06/get-url-parameters-using-jquery.html
	 */
	this.GetURLParameter = function( sParam ) {
		var sPageURL = window.location.search.substring( 1 );
		var sURLVariables = sPageURL.split( '&' );
		for ( var i = 0; i < sURLVariables.length; i++ ) {
			var sParameterName = sURLVariables[i].split( '=' );
			if ( sParameterName[0] == sParam ) {
				return sParameterName[1];
			}
		}
	};

	/**
	 *
	 */
	this.show_pointer = function( element, selector ) {

		// Abort if necessary.
		if ( 'undefined' === typeof WPHelpPointerIndex ) {
			return;
		}

		// Get the pointer.
		var i = WPHelpPointerIndex[selector];
		pointer = WPHelpPointer.pointers[i];
		if ( 'undefined' == typeof pointer ) {
			return;
		}

		// If the pointer has not been dismissed, show it.
		var pointer_is_dismissed = jQuery( element ).attr( 'data-pointer-is-dismissed' );
		if ( 'yes' != pointer_is_dismissed ) {
			wp_help_pointer_open( i );
		}
	};

	/**
	 * Sort a column in a table.
	 *
	 * @thanks http://stackoverflow.com/questions/16588123/sorting-tables-by-columns-jquery
	 */
	this.sort_table_column = function( this_th ) {

		/**
		 * Get the th the user clicked on.
		 *
		 * For example, if you're sorting by date, it will be:
		 */
		/*
		 * <th class='sort-date sorted asc'>
		 *
		 * <a href=''>
		 *
		 * <span>Date</span>
		 *
		 * <span class="sorting-indicator"></span>
		 *
		 * </a>
		 *
		 * </th>
		 */
		var $this_th = jQuery( this_th ),
			sort_order;

		/**
		 * Get the current sort and define the new sort.
		 */
		if ( $this_th.hasClass( 'asc' ) ) {
			sort_order = 'desc';
			$this_th.removeClass( 'asc' ).addClass( 'desc' );
		} else {
			sort_order = 'asc';
			$this_th.removeClass( 'desc' ).addClass( 'asc' );
		}

		var $tbody = $this_th.closest( 'table' ).children( 'tbody' );
		$tbody
			.find( 'tr' )
			.sort( function( a, b ) {
				var tda = jQuery( a )
					.find( 'td:eq(' + $this_th.index() + ')' )
					.text();

				var tdb = jQuery( b )
					.find( 'td:eq(' + $this_th.index() + ')' )
					.text();

				if ( 'desc' == sort_order ) {
					return tda < tdb ? 1 : tda > tdb ? -1 : 0;
				} else {
					return tda > tdb ? 1 : tda < tdb ? -1 : 0;
				}
			} )
			.appendTo( $tbody );
	};

	/**
	 * Ensure "Customize" link goes to customize.php.
	 *
	 * There are several plugins, such as "theme check", that modify the link
	 * where "Customize" goes. This function will change it back to
	 * customize.php
	 */
	this.update_customizer_link = function() {
		var useAdminMenu = 0,
			currentUrl = encodeURIComponent( window.location.pathname + window.location.search ),
			pageNow = 'undefined' === typeof pagenow ? null : pagenow,
			returnUrl =
				'toplevel_page_boldgrid-inspirations' === pageNow ? BoldGridAdmin.dashboardUrl : currentUrl;

		// Set useAdminMenu.
		if (
			IMHWPB.configs !== undefined &&
			IMHWPB.configs.settings !== undefined &&
			IMHWPB.configs.settings.boldgrid_menu_option !== undefined
		) {
			useAdminMenu = IMHWPB.configs.settings.boldgrid_menu_option;
		}

		if ( 1 == useAdminMenu && 'undefined' != typeof pagenow && 'dashboard-network' != pagenow ) {

			// Configure the correct link.
			var correct_link = 'customize.php?return=' + returnUrl;

			// Apply this link to "Customize".
			jQuery( '#menu-appearance a.menu-top' ).attr( 'href', correct_link );
		}
	};

	/**
	 * Update the shopping cart total.
	 */
	this.update_header_cart = function( change ) {

		// Get the cart element.
		var $cart = jQuery( '#wp-admin-bar-pfp a' );

		// <span class="ab-icon"></span> (10)
		var cart_html = $cart.html();

		// Update the current price by change.
		var current_price = parseInt( cart_html.replace( /\D/g, '' ) );
		var new_price = current_price + parseInt( change );

		// <span class="ab-icon"></span> (20)
		var new_cart_html = cart_html.replace( '(' + current_price + ')', '(' + new_price + ')' );

		// Update the cart element.
		$cart.html( new_cart_html );
	};
};

new IMHWPB.BaseAdmin( jQuery );

/*
 * The BaseAdmin class was initially intended to be a utility type class for js. Because of the
 * way it was written however, it made it difficult to easily reuse the functions contained within.
 *
 * Going forward, you can declare utility type functions below, within BoldGrid.Utility.
 *
 * They can be called, for example:
 * BoldGrid.Utility.ucfirst( 'joe cool' );
 */

var BoldGrid = BoldGrid || {};

BoldGrid.Utility = {

	/**
	 * Make a string's first character uppercase.
	 *
	 * Inspired by php's ucfirst.
	 *
	 * @since 1.3.2
	 *
	 * @param  str
	 * @return string
	 */
	ucfirst: function( str ) {
		return str.charAt( 0 ).toUpperCase() + str.substr( 1 );
	},

	/**
	 * Validate an email address.
	 *
	 * @since 1.3.9
	 *
	 * @link http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
	 *
	 * @param  string $email
	 * @return bool
	 */
	validateEmail: function( email ) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test( email );
	}
};

/*
 * Register any jQuery extensions.
 *
 * @todo: These cannot be set within the BaseAdmin object, as other scripts register additional
 * instances of BaseAdmin, and that causes problems. Currently, there's only one jQuery extension.
 * Look into separating into another file if more extensions are created.
 */

/**
 * @summary Toggle the disabled attribute.
 *
 * @since 1.3.4
 *
 * @link http://stackoverflow.com/questions/11903293/toggle-disabled-attribute-in-jquery
 */
jQuery.fn.toggleDisabled = function() {
	return this.each( function() {
		this.disabled = ! this.disabled;
	} );
};

/**
 * @summary Trim a value.
 *
 * @since 1.3.9
 */
jQuery.fn.boldgridTrimVal = function() {
	this.val( this.val().trim() );
};

/**
 * @summary Find all values and trim them.
 *
 * @since 1.3.9
 */
jQuery.fn.boldgridFindAndTrim = function() {
	this.each( function() {
		jQuery( this )
			.find( 'input[type=text]' )
			.each( function() {
				jQuery( this ).boldgridTrimVal();
			} );
	} );
};
