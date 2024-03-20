const ulCapturePayment = function () {
    ulProcessPayment('capture', BANKCARD_ALERT_TRANSLATIONS['CAPTURED'],
        BANKCARD_ALERT_TRANSLATIONS['CAPTURE'])
}

const ulCancelPayment = function () {
    ulProcessPayment('cancel', BANKCARD_ALERT_TRANSLATIONS['CANCELLED'],
        BANKCARD_ALERT_TRANSLATIONS['CANCEL'])
}

const ulProcessPayment = function (action, statusMessage, actionMessage, data) {
    if (!window.confirm(
        BANKCARD_ALERT_TRANSLATIONS['ARE_YOU_SURE'] + ' ' + actionMessage +
        ' ' +
        BANKCARD_ALERT_TRANSLATIONS['THE_PAYMENT'])) {
        return
    }

    if ('object' !== typeof data) {
        data = {}
    }
    data.action = action
    data.order_id = BANKCARD_ALERT_TRANSLATIONS['ORDER_ID']
    data.security = BANKCARD_ALERT_TRANSLATIONS['USER_TOKEN']

    const url = BANKCARD_ALERT_TRANSLATIONS['URL_AJAX']

    jQuery.ajax({
        url: url,
        data: data,
        type: 'POST',
        success: function (responseParsed) {
            const alertPaymentWasNot = BANKCARD_ALERT_TRANSLATIONS['PAYMENT_WAS_NOT']
            const errorMessage = alertPaymentWasNot + ' ' + statusMessage

            if (!responseParsed) {
                alert(errorMessage)
                return
            }

            if (responseParsed.success) {
                alert(BANKCARD_ALERT_TRANSLATIONS['PAYMENT_HAS_BEEN'] + ' ' +
                    statusMessage + ' ' +
                    BANKCARD_ALERT_TRANSLATIONS['SUCCESSFULLY'])
                location.reload()
            } else {
                if (responseParsed.data && responseParsed.data.error_message) {
                    alert(alertPaymentWasNot + ' ' + statusMessage + ': ' +
                        responseParsed.data.error_message)
                } else {
                    alert(errorMessage)
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Handle the error here
            console.error('AJAX request failed:', textStatus, errorThrown)
            alert('Error: ' + errorThrown)
        },
    })
}

