<?php

include_once __DIR__ . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';

$ul_util = new ULOpencartUtil();
$moduleVersion = $ul_util->getModuleVersion();

// Heading
$_['heading_title'] = 'Unlimint Credit Card' . ' (v' . $moduleVersion . ')';

// Text
$_['text_payment'] = 'Payment';
$_['text_success'] = 'Success, your modifications are done!';
$_['text_ul_card'] = '<img src="view/image/payment/credit_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br /><b></b>';
$_['text_argentina'] = 'Argentina';
$_['text_brasil'] = 'Brasil';
$_['text_colombia'] = 'Colombia';
$_['text_chile'] = 'Chile';

//Tab
$_['tab_setting'] = 'Settings';
$_['tab_order_status'] = 'Order Status';

// Entry
$_['entry_payments_not_accept'] = 'Check the payments methods that you want to accept:';
$_['entry_payments_not_accept_tooltip'] = '';
$_['entry_status'] = 'Status:';
$_['entry_production'] = 'Production Mode:';
$_['entry_country'] = 'Sales Country:';
$_['entry_sort_order'] = 'Sort order:';
$_['entry_url'] = 'Store Url: ';
$_['entry_type_checkout'] = 'Type Checkout: ';
$_['entry_category'] = 'Category:';

$_['entry_order_status'] = 'Default order status: ';
$_['entry_order_status_general'] = 'Select the statuses to be shown when the order is: ';
$_['entry_order_status_completed'] = 'Completed:';
$_['entry_order_status_pending'] = 'Pending:';
$_['entry_order_status_canceled'] = 'Canceled:';
$_['entry_order_status_in_process'] = 'In Progress:';
$_['entry_order_status_rejected'] = 'Reject:';
$_['entry_order_status_refunded'] = 'Refunded:';
$_['entry_order_status_in_mediation'] = 'Mediation:';
$_['entry_order_status_chargeback'] = 'ChargeBack';
$_['entry_terminal_code'] = 'Terminal Code:';
$_['entry_terminal_password'] = 'Terminal Password:';
$_['entry_callback_secret'] = 'Callback Secret:';
$_['entry_test_environment'] = 'Test Environment:';
$_['entry_capture_payment'] = 'Capture Payment:';
$_['entry_installment_enabled'] = 'Installment Enabled:';
$_['entry_installments'] = 'Maximum accepted installments';
$_['entry_payment_title'] = 'Payment Title:';
$_['entry_ask_cpf'] = 'Ask CPF:';
$_['entry_log_to_file'] = 'Log to File:';
$_['entry_dynamic_descriptor'] = 'Dynamic Descriptor:';
$_['entry_new_payment_status'] = 'Order status when payment is new:';
$_['entry_payment_processing'] = 'Order status when payment is in process:';
$_['entry_payment_declined'] = 'Order status when payment is declined:';
$_['entry_payment_authorized'] = 'Order status when payment is authorized:';
$_['entry_payment_completed'] = 'Order status when payment is completed:';
$_['entry_payment_cancelled'] = 'Order status when payment is canceled:';
$_['entry_payment_charged_back'] = 'Order status when payment is charged back:';
$_['entry_payment_chargeback_resolved'] = 'Order status when chargeback is resolved:';
$_['entry_payment_refunded'] = 'Order status when payment is refunded:';
$_['entry_payment_voided'] = 'Order status when payment is voided:';
$_['entry_payment_terminated'] = 'Order status when payment is terminated:';
$_['entry_payment_status'] = 'Enabled';

// Error
$_['error_permission'] = 'Sorry, you don\'t have permission to to modify Unlimint';
$_['error_client_id'] = 'Sorry, your <b>Client Id</b> is mandatory.';
$_['error_client_secret'] = 'Sorry, <b>Client Secret</b> is mandatory.';
$_['error_access_token'] = '<b>Terminal Password</b> invalid.';
$_['error_public_key'] = '<b>Terminal Code</b> invalid.';
$_['error_sponsor_span'] = 'Sponsor ID invalid. This field is not mandatory, if you dont know your Sponsor, please clean this field!';

// Help
$_['help_terminal_password'] = 'Get your credentials, visit the unlimint.com';
$_['help_test_environment'] = 'In test environment, the data is sent to the sandbox only. Test and prod credentials (terminal code, terminal password, callback secret) are different.';
$_['help_capture_payment'] = 'If set to No, the amount will not be captured but only blocked. With No option selected payments will be captured automatically in 7 days from the time of creating the preauthorized transaction. In installment case with No option selected installments will be declined automatically in 7 days from the time of creating the preauthorized transaction.';
$_['help_payment_title'] = 'If set to No, the amount will not be captured but only blocked. With No option selected payments will be captured automatically in 7 days from the time of creating the preauthorized transaction. In installment case with No option selected installments will be declined automatically in 7 days from the time of creating the preauthorized transaction.';
$_['help_installment_enabled'] = 'If set to Yes then installment payments field will be presented on payment form and installment payments can be possible for processing.';
$_['help_status'] = 'It is a requirement that you have a SSL certificate, and the payment form to be provided under an HTTPS page.';

// installments
$_['18'] = '18';
$_['15'] = '15';
$_['12'] = '12';
$_['9'] = '9';
$_['6'] = '6';
$_['3'] = '3';
$_['1'] = '1';

$_['ul_button_cancel'] = 'Cancel payment';
$_['ul_button_capture'] = 'Capture payment';
$_['ul_q01'] = 'Are you sure you want to';
$_['ul_q02'] = 'the payment?';
$_['ul_q03'] = 'Payment was not';
$_['ul_q04'] = 'Payment has been';
$_['ul_q05'] = 'successfully';
$_['ul_q06'] = 'cancel';
$_['ul_q07'] = 'capture';
$_['ul_q08'] = 'cancelled';
$_['ul_q09'] = 'captured';
