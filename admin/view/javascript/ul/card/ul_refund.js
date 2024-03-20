function ulSwitchProductRefund(sender) {
    const classProductTotal = jQuery('.ul-product-total')
    const classProductQty = jQuery('.ul-product-qty')
    if (jQuery(sender).is(':checked')) {
        classProductQty.show()
        classProductTotal.show()
    } else {
        classProductQty.hide()
        classProductTotal.hide()
    }
}

function ulRefundChangeQty(inputField) {
    const price = parseFloat(jQuery(inputField).data('price'));
    const quantity = parseFloat(jQuery(inputField).val());

    if (!isNaN(price) && !isNaN(quantity)) {
        jQuery(`#ul-product-${jQuery(inputField).data('id')}-total`).val(price * quantity);
        ulRefundRecalc();
    } else {
        console.error('Invalid input');
    }
}

function ulRefundRecalc() {
    let total = 0;

    jQuery('.ul-product-total, .ul-total-total').each((index, element) => {
        const value = parseFloat(jQuery(element).val());
        if (!isNaN(value)) {
            total += value;
        }
    });

    jQuery('#ul-refund').val(parseFloat(total).toFixed(2));
}

const ulRefundPayment = function () {
    let returns = 0
    const storeId = jQuery('#ul-store-id').val()
    const products = []
    const totals = []

    jQuery('.ul-refund-val').each(function (i, item) {
        returns = returns + (
            (
                jQuery(item).val() > 0
            ) ? 1 : 0
        )
    })

    if (jQuery('#ul-restock').is(':checked') && (
        returns > 0
    )) {
        jQuery('.ul-product-qty').each(function (i, item) {
            const id = jQuery(item).data('id')
            const amountId = 'ul-product-' + id + '-total'
            const product = {
                product_id: id,
                quantity: jQuery(item).val(),
                amount: jQuery('#' + amountId).val(),
            }
            if (product.quantity > 0 || product.amount > 0) {
                products.push(product)
            }
        })
        jQuery('.ul-total-total').each(function (i, item) {
            const id = jQuery(item).data('id')
            const total = {
                total_id: id,
                amount: jQuery(item).val(),
            }
            if (total.amount > 0) {
                totals.push(total)
            }
        })

    }

    const refund = parseFloat(jQuery('#ul-refund').val())

    if (isNaN(refund)) {
        return
    }

    const data = {
        store_id: storeId,
        products: products,
        totals: totals,
        refund: refund,
        reason: jQuery('#ul-reason').val(),
    }
    ulProcessPayment('refund', BANKCARD_ALERT_TRANSLATIONS['REFUNDED'],
        BANKCARD_ALERT_TRANSLATIONS['REFUND'], {data: data})
}
