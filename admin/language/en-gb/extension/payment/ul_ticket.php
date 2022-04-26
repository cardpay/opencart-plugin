<?php

include_once __DIR__ . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';

$ul_util = new ULOpencartUtil();
$moduleVersion = $ul_util->getModuleVersion();

// Heading
$_['heading_title'] = 'Unlimint Boleto' . ' (v' .$moduleVersion . ')';

// Text
$_['text_ul_ticket'] = '<img src="view/image/payment/boleto.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br /><b></b>';
$_['text_payment'] = 'Payment';
$_['text_success'] = 'Success, your modifications are done!';
$_['text_argentina'] = 'Argentina';
$_['text_brasil'] = 'Brasil';
$_['text_colombia'] = 'Colombia';
$_['text_chile'] = 'Chile';

//Tab
$_['tab_setting'] = 'Settings';
$_['tab_order_status'] = 'Order Status';

// Entry
$_['entry_notification_url'] = 'Your notification URL is: ';
$_['entry_autoreturn'] = 'Auto Return';
$_['entry_client_id'] = 'Client ID : ';

$_['entry_client_secret'] = 'Client Secret : ';

$_['entry_installments'] = 'Maximum accepted installments';
$_['entry_status'] = 'Status:';
$_['entry_production'] = 'Production Mode:';
$_['entry_country'] = 'Sales Country:';
$_['entry_sort_order'] = 'Sort order:';

$_['entry_sandbox'] = 'Sandbox mode: ';
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
$_['entry_access_token'] = 'Terminal Password:';
$_['entry_test_environment'] = 'Test Environment:';
$_['entry_callback_secret'] = 'Callback Secret:';
$_['entry_payment_title'] = 'Payment Title:';
$_['entry_log_to_file'] = 'Log to File:';
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
$_['error_public_key'] = 'Sorry, <b>Terminal Code</b> is mandatory.';
$_['error_access_token'] = 'Sorry, <b>Terminal Password</b> is mandatory.';
$_['error_sponsor_span'] = 'Sponsor ID invalid. This field is not mandatory, if you dont know your Sponsor, please clean this field!';

// Help
$_['help_terminal_password'] = 'Get your credentials, visit the unlimint.com';
$_['help_test_environment'] = 'In test environment, the data is sent to the sandbox only. Test and prod credentials (terminal code, terminal password, callback secret) are different.';
$_['help_status'] = 'It is a requirement that you have a SSL certificate, and the payment form to be provided under an HTTPS page.';
