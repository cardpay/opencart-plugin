function ulSwitchProductRefund(sender) {
    if (jQuery(sender).is(":checked")) {
        jQuery('.ul-product-qty').show();
        jQuery('.ul-product-total').show();
    } else {
        jQuery('.ul-product-qty').hide();
        jQuery('.ul-product-total').hide();
    }
}

function ulRefundRecalc() {
    let total = 0;
    jQuery(".ul-product-total").each(function () {
        total = total + parseFloat($(this).val());
    });
    jQuery(".ul-total-total").each(function () {
        total = total + parseFloat($(this).val());
    });
    jQuery("#ul-refund").val(total);
}

function ulRefundChangeQty(sender) {
    const price = parseFloat(jQuery(sender).data('price'));
    const val = parseFloat(jQuery(sender).val());
    jQuery(`#ul-product-${jQuery(sender).data('id')}-total`).val(price * val);
    ulRefundRecalc();
}