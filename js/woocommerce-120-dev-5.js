/* global Cookies */
jQuery( function( $ ) {
	// Orderby
	$( '.woocommerce-ordering' ).on( 'change', 'select.orderby', function() {
		$( this ).closest( 'form' ).submit();
	});

	// Target quantity inputs on product pages
	$( 'input.qty:not(.product-quantity input.qty)' ).each( function() {
		var min = parseFloat( $( this ).attr( 'min' ) );

		if ( min >= 0 && parseFloat( $( this ).val() ) < min ) {
			$( this ).val( min );
		}
	});

	var noticeID   = $( '.woocommerce-store-notice' ).data( 'notice-id' ) || '',
		cookieName = 'store_notice' + noticeID;

	// Check the value of that cookie and show/hide the notice accordingly
	if ( 'hidden' === Cookies.get( cookieName ) ) {
		$( '.woocommerce-store-notice' ).hide();
	} else {
		$( '.woocommerce-store-notice' ).show();
	}

	// Set a cookie and hide the store notice when the dismiss button is clicked
	$( '.woocommerce-store-notice__dismiss-link' ).on( 'click', function( event ) {
		Cookies.set( cookieName, 'hidden', { path: '/' } );
		$( '.woocommerce-store-notice' ).hide();
		event.preventDefault();
	});

	// CHANGE: Remove "Make form field descriptions toggle on focus."

	$( '.woocommerce-input-wrapper' ).on( 'click', function( event ) {
		event.stopPropagation();
	} );

    // CHANGE: Remove "Make form field descriptions toggle on focus."

	// Common scroll to element code.
	$.scroll_to_notices = function( scrollElement ) {
		if ( scrollElement.length ) {
			$( 'html, body' ).animate( {
				scrollTop: ( scrollElement.offset().top - 100 )
			}, 1000 );
		}
	};

	// Show password visiblity hover icon on woocommerce forms
	$( '.woocommerce form .woocommerce-Input[type="password"]' ).wrap( '<span class="password-input"></span>' );
	// Add 'password-input' class to the password wrapper in checkout page.
	$( '.woocommerce form input' ).filter(':password').parent('span').addClass('password-input');
	$( '.password-input' ).append( '<span class="show-password-input"></span>' );

	$( '.show-password-input' ).on( 'click',
		function() {
			$( this ).toggleClass( 'display-password' );
			if ( $( this ).hasClass( 'display-password' ) ) {
				$( this ).siblings( ['input[type="password"]'] ).prop( 'type', 'text' );
			} else {
				$( this ).siblings( 'input[type="text"]' ).prop( 'type', 'password' );
			}
		}
	);
});
