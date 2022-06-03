const ulCapturePayment = function () {
    ulProcessPayment('capture', BANKCARD_ALERT_TRANSLATIONS['CAPTURED'], BANKCARD_ALERT_TRANSLATIONS['CAPTURE']);
}

const ulRefundPayment = function () {
    let returns = 0;
    const store_id = jQuery('#ul-store-id').val();
    let products = [];
    let totals = [];

    jQuery('.ul-refund-val').each(function (i, item) {
        returns = returns + ((jQuery(item).val() > 0) ? 1 : 0);
    })

    if (jQuery('#ul-restock').is(":checked") && (returns > 0)) {
        jQuery('.ul-product-qty').each(function (i, item) {
            const id = jQuery(item).data('id');
            const amount_id = 'ul-product-' + id + '-total'
            let product = {
                product_id: id,
                quantity: jQuery(item).val(),
                amount: jQuery('#' + amount_id).val(),
            }
            if ((product.quantity > 0) || (product.amount > 0)) {
                products.push(product)
            }
        })
        jQuery('.ul-total-total').each(function (i, item) {
            const id = jQuery(item).data('id');
            let total = {
                total_id: id,
                amount: jQuery(item).val(),
            }
            if (total.amount > 0) {
                totals.push(total)
            }
        })

    }

    const refund = parseFloat(jQuery('#ul-refund').val());

    if (isNaN(refund)) {
        return;
    }

    let data = {
        store_id: store_id,
        products: products,
        totals: totals,
        refund: refund,
        reason: jQuery('#ul-reason').val()
    }
    ulProcessPayment('refund', BANKCARD_ALERT_TRANSLATIONS['REFUNDED'], BANKCARD_ALERT_TRANSLATIONS['REFUND'], {data: data});
}

const ulRefundShow = function () {
    jQuery('#ul-refund-form').show();
    jQuery('#ul-refund').focus();
}

const ulCancelPayment = function () {
    ulProcessPayment('cancel', BANKCARD_ALERT_TRANSLATIONS['CANCELLED'], BANKCARD_ALERT_TRANSLATIONS['CANCEL']);
}

const ulProcessPayment = function (action, status_message, action_message, data) {
    if (!window.confirm(BANKCARD_ALERT_TRANSLATIONS['ARE_YOU_SURE'] + ' ' + action_message + ' ' + BANKCARD_ALERT_TRANSLATIONS['THE_PAYMENT'])) {
        return;
    }

    if ("object" !== typeof data) {
        data = {}
    }
    data.action = action;
    data.order_id = BANKCARD_ALERT_TRANSLATIONS['ORDER_ID'];
    data.security = BANKCARD_ALERT_TRANSLATIONS['USER_TOKEN'];

    jQuery.ajax({
        url: 'index.php?route=ajax/ajax_form/ajaxButton&user_token=' + BANKCARD_ALERT_TRANSLATIONS['USER_TOKEN'],
        data: data,
        type: 'POST',
        success: function (responseParsed) {
            const alertPaymentWasNot = BANKCARD_ALERT_TRANSLATIONS['PAYMENT_WAS_NOT'];
            const errorMessage = alertPaymentWasNot + ' ' + status_message;
            if (!responseParsed) {
                alert(errorMessage);
                return;
            }

            if (responseParsed.success) {
                alert(BANKCARD_ALERT_TRANSLATIONS['PAYMENT_HAS_BEEN'] + ' ' + status_message + ' ' + BANKCARD_ALERT_TRANSLATIONS['SUCCESSFULLY']);
                location.reload();
            } else {
                if (responseParsed.data && responseParsed.data.error_message) {
                    alert(alertPaymentWasNot + ' ' + status_message + ': ' + responseParsed.data.error_message);
                } else {
                    alert(errorMessage);
                }
            }
        }
    });
}