<?php

// Text
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$admin = strpos($url, 'admin') !== false ? '' : './admin/';
$_['text_title'] = '<img src="' . $admin . 'view/image/payment/ul_ticket.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE;"> Boleto';

$_['payment_processing'] = "Processando o pagamento.";
$_['payment_title'] = "Pagamento";
$_['payment_button'] = "Pagar";
$_['S400'] = "Não foi possível processar seu pagamento. Por favor, tente novamente.";
