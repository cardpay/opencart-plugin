window.addEventListener( 'load', function() {
	if ( localStorage.getItem( 'activeTab' ) ) {
		localStorage.removeItem( 'activeTab' );
	}

	$( '#form_ul' ).submit( function( e ) {
		const prefix = 'payment_ul_apay_';

		validateAltMethodForm( prefix, e );

		const isValidMerchantId = validateUlAdminField(prefix + 'merchant_id', 128, 'Apple merchant id', false);
		const isValidMerchantCert = validateUlAdminFileField(prefix + 'merchant_certificate', 'Payment processing certificate');
		const isValidMerchantKey = validateUlAdminFileField(prefix + 'merchant_key', 'Merchant identity certificate');

		if (!isValidMerchantId || !isValidMerchantCert || !isValidMerchantKey) {
			e.preventDefault(e);
		}
	} );
} );
(
	function() {
		buttonSave();
	}
)();

const validateUlAdminFileField = function (fieldName, errorField) {
    const errorMessageId = fieldName + '-error';
    const errorMessageField = $(`[id=${errorMessageId}]`);
    if (errorMessageField) {
        errorMessageField.remove();
    }

    const adminField = $(`#${fieldName}`);
    const existingadminField = $(`#${fieldName+'_existing'}`);
    const labelField = $(`label[for=${fieldName}]`);
    if (labelField.length > 0) {
        errorField = labelField.text().replace(':', '');
    }
    if (adminField) {
        const fieldValue = adminField.val();
        const exisitngFieldValue = existingadminField.val();
        if (
			(!fieldValue || fieldValue.trim().length === 0) &&
			(!exisitngFieldValue || exisitngFieldValue.trim().length ===0)
		) {
            showUlAdminError(errorMessageId, ul_empty_error.replace('%s', errorField));
            return false;
        }
    }

    return true;
}

function saveConfigs() {
	$( '#form_ul' ).submit();
}
