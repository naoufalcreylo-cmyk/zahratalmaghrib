/**
 * Copy-link button for the share block. The only front-end script in the theme.
 */
( function () {
	'use strict';

	function announce( button, message ) {
		var box = button.closest( '.zam-share' );
		var status = box && box.querySelector( '.zam-share__status' );
		if ( status ) {
			status.textContent = '';
			window.setTimeout( function () {
				status.textContent = message;
			}, 50 );
		}
	}

	function fallbackCopy( text ) {
		var field = document.createElement( 'textarea' );
		field.value = text;
		field.setAttribute( 'readonly', '' );
		field.style.position = 'fixed';
		field.style.opacity = '0';
		document.body.appendChild( field );
		field.select();
		var ok = false;
		try {
			ok = document.execCommand( 'copy' );
		} catch ( e ) {}
		document.body.removeChild( field );
		return ok;
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-zam-copy]' );
		if ( ! button ) {
			return;
		}
		var url = button.getAttribute( 'data-zam-copy' );
		var l10n = window.zamL10n || { copied: 'Link copied', failed: 'Copy failed' };

		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( url ).then(
				function () {
					announce( button, l10n.copied );
				},
				function () {
					announce( button, fallbackCopy( url ) ? l10n.copied : l10n.failed );
				}
			);
		} else {
			announce( button, fallbackCopy( url ) ? l10n.copied : l10n.failed );
		}
	} );
} )();
