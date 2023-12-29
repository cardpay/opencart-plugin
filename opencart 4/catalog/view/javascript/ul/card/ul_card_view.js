(
	function() {
		setTimeout( function() {
			$( '#ul-form' ).show( 'slow' );
		}, 2500 );
	}
)();

function getMessage( data ) {
	const {div_error, btn_dismiss, url_message} = extracted( data,
		'ul_custom' );
	$.get( url_message, function success( rtn ) {
		const paymentReturn = JSON.parse( rtn );
		const text = document.createTextNode( paymentReturn['message'] );
		div_error.innerHTML = '';
		div_error.appendChild( text );
		div_error.appendChild( btn_dismiss );
		document.getElementById( 'ul_custom' ).appendChild( div_error );
	} );
}

function buildAmount( amount ) {
	return finalAmount( amount );
}

function getCardIssuers() {
	const publicKey = document.getElementById( 'public_key' ).value;
	const paymentMethodId = document.getElementById( 'paymentType' ).value;
	Unlimit.setPublishableKey( publicKey );
	Unlimit.getIssuers( paymentMethodId, function( httpStatus, dt ) {
		let option;
		const select = document.getElementById( 'issuer' );
		let i = dt.length;

		while ( i > 0 ) {
			if ( dt[i].name !== 'default' ) {
				option = new Option( dt[i].name, dt[i].id );
				option.style.background = 'url("' + dt[i].secure_thumbnail +
				                          '") 98% 50% no-repeat';
			} else {
				option = new Option( 'Otro', dt[i].id );
			}
			i --;
			select.appendChild( option );
		}
	} );
}

const cardType = document.getElementById( 'cardType' );

if ( cardType ) {
	cardType.addEventListener( 'change', cardTypeEventListener );
}

function cardTypeEventListener() {
	const paymentType = document.getElementById( 'paymentType' );
	const bg = document.querySelector( 'input[data-checkout="cardNumber"]' );

	if ( paymentType.value.indexOf( 'visa' ) > - 1 ||
	     paymentType.value.indexOf( 'master' ) > - 1 ) {
		if ( this.value === 'deb' ) {
			paymentType.value = this.value + paymentType.value;
			bg.style.background = bg.style.background.replace( 'visa.gif',
				'debvisa.gif' ).replace( 'master.gif', 'debmaster.gif' );
			//document.getElementById('divInstallments')    //why in the hell is this thing here?
		} else {
			paymentType.value = paymentType.value.replace( 'deb', '' );
			bg.style.background = bg.style.background.replace( 'debvisa.gif',
				'visa.gif' ).replace( 'debmaster.gif', 'master.gif' );
		}
	}
}

function pay( payment, urlBackend ) {
	const cardNumber = document.getElementById( 'cc_num' );
	payment.issuer_id = cardNumber.hasAttribute( 'data-card-issuer' ) ?
		cardNumber.getAttribute( 'data-card-issuer' ) :
		payment.issuer_id;

	payment.payment_method_id = cardNumber.hasAttribute(
		'data-card-payment-method-id' ) ?
		cardNumber.getAttribute( 'data-card-payment-method-id' ) :
		payment.payment_method_id;

	$.ajax( {
		type: 'POST',
		url: urlBackend,
		data: payment,
		success: function success( data ) {
			const responsePayment = JSON.parse( data );
			ModuleAnalytics.setToken( response.token );
			ModuleAnalytics.setPaymentId( response.paymentId );
			ModuleAnalytics.setPaymentType( response.paymentType );
			ModuleAnalytics.setCheckoutType( response.checkoutType );
			ModuleAnalytics.put();

			document.getElementById( 'ulr-form' ).style = 'margin-left: 22%';
			const acceptableStatus = ['approved', 'in_process'];
			if ( acceptableStatus.indexOf( responsePayment.status ) > - 1 ) {
				const urlSite = window.location.href.split( 'index.php' )[0];
				let location = urlSite.slice( - 1 ) === '/' ?
					urlSite :
					urlSite + '/';
				location += 'index.php?route=checkout/success';
				localStorage.removeItem( 'payment' );
				window.location.href = location;
			} else {
				delete responsePayment.request_type;
				getMessage( responsePayment );
			}
		},
	} );
}

const checkForm = {
	check: function( obj ) {
		let checked = true;
		const f = this;
		const ufForm = $( '#ul-form' );
		ufForm.find( '.ul-error' ).hide();
		ufForm.find( '[data-checkout]' ).each( function( i, item ) {
			checked = f.processInput( $( item ).data( 'checkout' ),
				$( item ).val(), checked );
		} );
		return checked;
	},

	processInput: function( iType, val, checked ) {
		let success = '';
		switch ( iType ) {
			case 'ulCardNumber':
				success = validatePhone('Card');
				checked = success && checked;
				break;
			case 'cardNumber':
				success = this.checkCardNumber( this.ulInteger( val ) );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'cardholderName':
				success = this.checkCardholderName( val );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'cardExpirationMonth':
				success = this.checkCardExpirationMonth(
					this.ulInteger( val ) );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'cardExpirationYear':
				success = this.checkCardExpirationYear( this.ulInteger( val ) );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'cardExpirationDate':
				success = this.checkCardExpirationDate( val );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'securityCode':
				success = this.checkSecurityCode( this.ulInteger( val ) );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'docNumber':
				success = this.checkDocNumber( this.ulInteger( val ) );
				checked = (
					          success === ''
				          ) && checked;
				break;
			case 'installments':
				success = this.areUlInstallmentsValid();
				checked = (
					          success === ''
				          ) && checked;
				break;
		}
		const fi = $.find( '[data-main="#' + iType + '"]' );
		if ( fi.length > 0 ) {
			$( fi ).hide();
		}
		if ( success !== '' ) {
			$( '#ul-error-' + success ).show();
		}
		return checked;
	},

	ulInteger: function( v ) {
		v = v + '';
		return v.replace( /\D/g, '' );
	},

	checkCardNumber: function( value ) {
		if ( value.length === 0 ) {
			return 'E301';
		}
		return this.moonAlghorithm( value ) ? '' : '205';
	},

	checkCardholderName: function( value ) {
		if ( value.length === 0 ) {
			return '221';
		}
		return (
			value.length >= 2
		) ? '' : '316';
	},

	checkCardExpirationMonth: function( value ) {
		return (
			value.length >= 1
		) ? '' : '208';
	},

	checkCardExpirationYear: function( value ) {
		return (
			value.length === 4
		) ? '' : '325';
	},

	checkCardExpirationDate: function( value ) {
		if ( value.length === 0 ) {
			return '209';
		}
		const parts = value.split( '/' );
		const m = parseInt( parts[0] ) - 1;
		const y = parseInt( '20' + parts[1] );
		const currentDate = new Date();
		if ( (
			     m < - 1
		     ) || (
			     m > 11
		     ) || (
			     y > currentDate.getFullYear() + 40
		     ) ) {
			return '208';
		}
		const date = new Date( y, m, '01' );
		let inst = 1;
        if (document.getElementById( 'ul-installments' )) {
            inst = parseInt(
                document.getElementById( 'ul-installments' ).value );
            if ( isNaN( inst ) ) {
                inst = 1;
            }
        }
		const lastDate = new Date(
			currentDate.setMonth( currentDate.getMonth() + inst - 1 ) );
		return (
			date >=
			new Date( lastDate.getFullYear(), lastDate.getMonth(), '01' )
		) ? '' : '208';
	},

	checkSecurityCode: function( value ) {
		if ( value.length === 0 ) {
			return '224';
		}
		return (
			value.length > 2
		) ? '' : 'E302';
	},

	checkDocNumber: function( value ) {
		if ( value.length === 0 ) {
			return 'E324';
		}
		return (
			isUlCpfValid( value )
		) ? '' : '324';
	},

	areUlInstallmentsValid: function() {
		const installments = jQuery( '#ul-installments' );
		if ( !installments ) {
			return '';
		}
		return (
			installments.val() !== ''
		) ? '' : '220';
	},

	moonAlghorithm: function( value ) {
		let ch = 0;
		const num = String( value ).replace( /\D/g, '' );
		const isOdd = num.length % 2 !== 0;

		if ( '' === num ) {
			return false;
		}

		for ( let i = 0; i < num.length; i ++ ) {
			let n = parseInt( num[i], 10 );

			ch += (
				      isOdd | 0
			      ) === (
				      i % 2
			      ) && 9 < (
				      n *= 2
			      ) ?
				(
					n - 9
				) :
				n;
		}

		return 0 === (
			ch % 10
		);
	},
};

function ul_check_form( obj ) {
	return checkForm.check( obj );
}

function ul_check_input( iType, val ) {
	return checkForm.processInput( iType, val, true );
}
