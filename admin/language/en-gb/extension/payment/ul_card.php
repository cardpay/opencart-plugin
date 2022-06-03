<?php

include_once __DIR__ . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';
include_once __DIR__ . '/../../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimint Credit Card';

// Text
$_['text_ul_card'] = '<img src="view/image/payment/credit_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Check the payments methods that you want to accept:';
$_['entry_url'] = 'Store Url: ';

$_['entry_capture_payment'] = 'Capture Payment:';
$_['entry_installment_enabled'] = 'Installment Enabled:';
$_['entry_ask_cpf'] = 'Ask CPF:';
$_['entry_dynamic_descriptor'] = 'Dynamic Descriptor:';

// Error
$_['error_access_token'] = '<b>Terminal Password</b> invalid.';
$_['error_public_key'] = '<b>Terminal Code</b> invalid.';

// Help
$_['help_capture_payment'] = 'If set to No, the amount will not be captured but only blocked. With No option selected payments will be captured automatically in 7 days from the time of creating the preauthorized transaction. In installment case with No option selected installments will be declined automatically in 7 days from the time of creating the preauthorized transaction.';
$_['help_installment_enabled'] = 'If set to Yes then installment payments field will be presented on payment form and installment payments can be possible for processing.';

$_['ul_button_cancel'] = 'Cancel payment';
$_['ul_button_capture'] = 'Capture payment';
$_['ul_button_refund'] = 'Refund payment';
$_['ul_q01'] = 'Are you sure you want to';
$_['ul_q02'] = 'the payment?';
$_['ul_q03'] = 'Payment was not';
$_['ul_q04'] = 'Payment has been';
$_['ul_q05'] = 'successfully';
$_['ul_q06'] = 'cancel';
$_['ul_q07'] = 'capture';
$_['ul_q08'] = 'cancelled';
$_['ul_q09'] = 'captured';
$_['ul_q10'] = 'refund';
$_['ul_q11'] = 'refunded';

$_['ul_product'] = 'Product';
$_['ul_model'] = 'Model';
$_['ul_price'] = 'Price';
$_['ul_quantity'] = 'Quantity';
$_['ul_amount'] = 'Amount';
$_['ul_refund'] = 'Refund';

$_['ul_restock'] = 'Restock refunded items';
$_['ul_already'] = 'Amount already refunded';
$_['ul_available'] = 'Total available to refund';
$_['ul_re_amount'] = 'Refund amount';
$_['ul_re_reason'] = 'Reason for refund (optional)';
$_['ul_totals'] = 'Totals';
$_['ul_cancel'] = 'Cancel';

$_['invalid_refund_amount'] = 'Invalid refund amount';
$_['ajax_form_e1'] = 'Order total amount must be more than 0 to capture the payment';
$_['ajax_form_e2'] = 'Order total amount must not exceed the blocked amount to capture the payment';
