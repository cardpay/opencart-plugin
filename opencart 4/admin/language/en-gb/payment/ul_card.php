<?php

include_once __DIR__ . '/../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimit Credit Card';

// Text
$_['text_ul_card'] = '<img src="../extension/unlimit/admin/view/image/payment/credit_card.png" alt="Unlimit" title="Unlimit" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Check the payments methods that you want to accept:';

$_['entry_capture_payment']               = 'Capture payment:';
$_['entry_installment_enabled']           = 'Installment enabled:';
$_['entry_ask_cpf']                       = 'Ask CPF:';
$_['entry_dynamic_descriptor']            = 'Dynamic descriptor:';
$_['text_issuer_financed']                = 'Issuer financed';
$_['text_merchant_financed']              = 'Merchant financed';
$_['entry_installment_type']              = 'Installment type';
$_['help_installment_type']               = 'Should be selected only if "Installment enabled" setting is switched on. Here can be choosed installment type used in trade plugin.
More details about installment types you can read <a href = "https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#issuer-financed-if" >Issuer financed installments</a>, <a href = "https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#merchant-financed-mf_hold" >Merchant financed installments</a>.';
$_['entry_minimum_installment_amount']    = 'Minimum installment amount';
$_['help_minimum_installment_amount']     = 'Minimum installment amount for order with installments.
Here can be filled minimum amount of 1 installment, f.e if we have 5 installments with 20 usd amount of 1 installment, total amount of order in this case is 100 usd';
$_['entry_maximum_accepted_installments'] = 'Allowed installments range';
$_['help_maximum_accepted_installments']  = 'Allowed installments range,
For "Merchant financed" installments can be filled in range of allowed values or several allowed values not in a row.
All values can be from interval 1-12, for example: Range of values 3-7 (using "-" as separator). Allowed values not in a row 2, 3, 6, 8, 12 (using "," as separator).
For "Issuer financed" installment type can be only allowed values not in a row from the following: 3, 6, 9, 12, 18.
If empty, then the default values will be used (2-12 for "Merchant financed" and 3, 6, 9, 12, 18 for "Issuer financed").';


// Error
$_['error_access_token'] = '<b>Terminal password</b> invalid.';
$_['error_public_key']   = '<b>Terminal code</b> invalid.';

// Help
$_['help_capture_payment']     = 'Setting is for regular payments and Merchant financed installments. If set to "No", the amount will not be captured but only blocked. By default with "No" option selected payments will be voided automatically in 7 days from the time of creating the preauthorized transaction. If you want payments to be captured automatically in 7 days (instead of being voided), please contact your account manager.';
$_['help_installment_enabled'] = 'If set to Yes then installment payments field will be presented on payment form and installment payments can be possible for processing.';

$_['ul_button_cancel']  = 'Cancel payment';
$_['ul_button_capture'] = 'Capture payment';
$_['ul_button_refund']  = 'Refund payment';
$_['ul_q01']            = 'Are you sure you want to';
$_['ul_q02']            = 'the payment?';
$_['ul_q03']            = 'Payment was not';
$_['ul_q04']            = 'Payment has been';
$_['ul_q05']            = 'successfully';
$_['ul_q06']            = 'cancel';
$_['ul_q07']            = 'capture';
$_['ul_q08']            = 'cancelled';
$_['ul_q09']            = 'captured';
$_['ul_q10']            = 'refund';
$_['ul_q11']            = 'refunded';

$_['ul_product']  = 'Product';
$_['ul_model']    = 'Model';
$_['ul_price']    = 'Price';
$_['ul_quantity'] = 'Quantity';
$_['ul_amount']   = 'Amount';
$_['ul_refund']   = 'Refund';

$_['ul_restock']   = 'Restock refunded items';
$_['ul_already']   = 'Amount already refunded';
$_['ul_available'] = 'Total available to refund';
$_['ul_re_amount'] = 'Refund amount';
$_['ul_re_reason'] = 'Reason for refund (optional)';
$_['ul_totals']    = 'Totals';
$_['ul_cancel']    = 'Cancel';

$_['invalid_refund_amount'] = 'Invalid refund amount';
$_['ajax_form_e1']          = 'Order total amount must be more than 0 to capture the payment';
$_['ajax_form_e2']          = 'Order total amount must not exceed the blocked amount to capture the payment';
