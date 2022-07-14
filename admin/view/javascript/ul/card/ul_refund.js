function ulSwitchProductRefund(sender) {
    const classProductTotal = jQuery('.ul-product-total');
    const classProductQty = jQuery('.ul-product-qty');
    if (jQuery(sender).is(":checked")) {
        classProductQty.show();
        classProductTotal.show();
    } else {
        classProductQty.hide();
        classProductTotal.hide();
    }
}

function ulRefundRecalc() {
    let total = 0;
    jQuery('.ul-product-total').each(function () {
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
