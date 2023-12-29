window.addEventListener( 'load', function() {
	if ( localStorage.getItem( 'activeTab' ) ) {
		localStorage.removeItem( 'activeTab' );
	}

	$( '#form_ul' ).submit( function( e ) {
		const prefix = 'payment_ul_gpay_';
		if ( !$( `#${prefix}terminal_code` ).length ) {
			return;
		}

		validateAltMethodForm( prefix, e );
	} );
} );
(
	function() {
		buttonSave();
	}
)();

function saveConfigs() {
	$( '#form_ul' ).submit();
}
