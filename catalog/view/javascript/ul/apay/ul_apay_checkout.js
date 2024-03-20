(
	function( $ ) {
		'use strict';

		$( function() {
			$( 'form.checkout' ).
				on( 'checkout_place_order_woo-unlimit-apay', function() {
					return true;
				} );
		} );
	}( jQuery )
);

function getTotalAmount() {
	var rows = $( 'table tbody tr' );

	if ( rows.length > 0 ) {
		var lastRow = rows.last();

		var cells = lastRow.find( 'td.text-end' );

		if ( cells.length > 0 ) {
			var totalCell = cells.last();
			var totalAmountText = totalCell.text().trim();

			return totalAmountText.match( /[\d.-]+/ )[0];
		}
	}
	return 0;
}

const unlimit = {
    applePay: {
        // Function to handle payment when the Apple Pay button is clicked/pressed.
        beginPayment: function (e) {
            e.preventDefault()
            var storeName = this.getCheckoutConfigParam('store_name', '')

            const totalValue = getTotalAmount();

            var totalForDelivery = {
                label: storeName,
                type: "final",
                amount: totalValue
            }

            // Create the Apple Pay payment request as appropriate.
            var paymentRequest = {
                countryCode: 'US',
                currencyCode: this.getCheckoutConfigParam('currency', ''),
                merchantCapabilities: ['supports3DS'],
                supportedNetworks: ['amex', 'masterCard', 'visa', 'elo', 'discover'],
                total: totalForDelivery
            }
            console.log(paymentRequest);

            const session = new ApplePaySession(3, paymentRequest)

            // Setup handler for validation the merchant session.
            session.onvalidatemerchant = function (event) {
                console.log('Event: Validate merchant')
                console.log(event)
                var self = this;
                var url = event.validationURL;
                jQuery.post(this.getCheckoutConfigParam('validatemerchant_url', ''),
                    {
                        url,
                        action: 'validate_merchant',
                        nonce: this.getCheckoutConfigParam('validatemerchant_nonce', ''),
                        merchantIdentifier: self.getMerchantIdentifier(),
                        displayName: storeName
                    }, function (data) {
                        var merchantSession = JSON.parse(data)
                        // cleaning the data
                        const sanitize = JSON.parse(data)
                        sanitize.signature = 'REDACTED'
                        sanitize.merchantSessionIdentifier = 'REDACTED'
                        sanitize.merchantIdentifier = 'REDACTED'

                        var text = JSON.stringify(sanitize, undefined, 4)
                        // Stop the session if merchantSession is not valid
                        if (typeof merchantSession === 'string' || 'statusCode' in merchantSession) {
                            console.log('paymentSession failed:');
                            console.log(text);
                            self.cancelPaymentSession(session)
                            return
                        }
                        if (!(
                            'merchantIdentifier' in merchantSession &&
                            'merchantSessionIdentifier' in merchantSession &&
                            ('nOnce' in merchantSession || 'nonce' in merchantSession)
                        )) {
                            let errorDescription = 'merchantSession is invalid. Payment Session cancelled by Apple Pay Demo Site.\n'
                            if (!('merchantIdentifier' in merchantSession)) {
                                errorDescription += 'merchantIdentifier is not found in merchantSession.\n'
                            }
                            if (!('merchantSessionIdentifier' in merchantSession)) {
                                errorDescription += 'merchantSessionIdentifier is not found in merchantSession.\n'
                            }
                            if (!('nOnce' in merchantSession)) {
                                errorDescription += 'nonce is not found in merchantSession\n'
                            }
                            errorDescription += text
                            console.log(errorDescription)
                            self.cancelPaymentSession(session)
                            return
                        }

                        console.log(text)
                        if (session !== null) {
                            session.completeMerchantValidation(merchantSession)
                        }
                    }, 'text')
                    .fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr.responseText)
                        if (session !== null) {
                            self.cancelPaymentSession(session)
                        }
                    })
            }.bind(this)

            session.onpaymentmethodselected = function onpaymentmethodselected(event) {
                console.log('Event: onpaymentmethodselected', event);
                const update = {
                    newTotal: totalForDelivery
                }
                console.log('Event: completePaymentMethodSelection', event);
                session.completePaymentMethodSelection(update)
            }.bind(this)

            session.onshippingmethodselected = function onshippingmethodselected(event) {
                console.log('Event: onshippingmethodselected', event);
                const update = {};
                session.completeShippingMethodSelection(update)
            }

            // Setup handler to receive the token when payment is authorized.
            session.onpaymentauthorized = function (event) {
                console.log('Event: onpaymentauthorized', event);
                var paymentSignature = JSON.stringify(event.payment);
                jQuery('#unlimit-form').find('[name="cardpay_custom_apay[signature]"]').val(paymentSignature);
                window.setTimeout(function () {
                    const update = { status: ApplePaySession.STATUS_SUCCESS, errors: [] }

                    session.completePayment(update)
                    console.log('\n\ncompletePayment executed with the following parameters:\n')
                    console.log(JSON.stringify({
                        status: update.status,
                        errors: update.errors
                    }, undefined, 4))

                    const placeOrderButton = document.getElementById('btnSubmit');
                    placeOrderButton.click();
                  }, 2000)
            }.bind(this)

            session.oncouponcodechanged = event => {
                console.log('Event: oncouponcodechanged', event);
                // Define ApplePayCouponCodeUpdate

                session.completeCouponCodeChange({});
            };

            // Start the session to display the Apple Pay sheet.
            console.log('Session begin!')
            session.begin()
        },
        cancelPaymentSession: function(session) {
            console.error("Cancelling session: ", session);
            if (session !== null) {
                session.abort()
            }
        },
        showButton: function () {
            console.log('Show Button')
            var button = jQuery('#apple-pay-button');
            button.attr('lang', this.getPageLanguage())
            button.on('click', this.beginPayment.bind(this))
            console.log(button.removeClass('d-none'));
            console.log(button.attr('class'));
            if (this.supportsSetup()) {
                console.log('Show Support Button')
                button.addClass('apple-pay-button-with-text')
                button.addClass('apple-pay-button-black-with-text')
            } else {
                console.log('Hide Support Button')
                button.addClass('apple-pay-button')
                button.addClass('apple-pay-button-black')
            }

            button.removeClass('d-none')
        },
        showError: function (text) {
            console.log(text)
            var error = jQuery('.apple-pay-error')
            error.text(text)
            error.removeClass('d-none')
        },
        supportedByDevice: function () {
            console.log('Check ApplePaySession')
            return 'ApplePaySession' in window
        },
        supportsSetup: function () {
            console.log('Check openPaymentSetup')
            return 'openPaymentSetup' in ApplePaySession
        },
        getPageLanguage: function () {
            return jQuery('html').attr('lang') || 'en'
        },
        getMerchantIdentifier: function () {
            return this.getCheckoutConfigParam('merchant_id', '')
        },
        getCheckoutConfigParam: function (type, defaultValue) {
            if (typeof oc_unlimit_apay_params === 'undefined' || typeof oc_unlimit_apay_params[type] === 'undefined') {
                return defaultValue;
            }
            return oc_unlimit_apay_params[type];
        }
    }
};
