<?php

$err_payment = 'We could not process your payment.';

// Text
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$admin = strpos($url, 'admin') !== false ? '' : './admin/';
$_['text_title'] = 'Boleto - Unlimint';

//Payment messages
$_['S400'] = "We could not process your payment. Please, try again.";
$_['S129'] = "payment_method_id does not process payments for the selected amount. Choose another card or another payment method.";
$_['S145'] = $err_payment;
$_['S150'] = "You can not make payments.";
$_['S151'] = "You can not make payments.";
$_['S160'] = $err_payment;
$_['S204'] = "Payment method is not available at this time. Choose another card or another payment method.";
$_['S801'] = "Try again in a few minutes.";
$_['payment_button'] = 'Pay';
