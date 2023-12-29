(
	function( $ ) {
		'use strict';

		$( function() {
			$( 'form.checkout' ).
				on( 'checkout_place_order_woo-unlimit-ticket', function() {
					return validateUlBoletoCpf();
				} );
		} );
	}( jQuery )
);

function validateUlBoletoCpf() {
	return validateUlCpf( 'docnumber', 'cpf-error' );
}
