<?php

$err_payment = "We could not process your payment.";
// Text
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$admin = strpos($url, 'admin') !== false ? '' : './admin/';

$_['text_title'] = 'Credit Card - Unlimint';
$_['other_card_option'] = "Other Card";

$_['habilitar_cupom_desconto'] = 'Habilitar Cupom de Desconto:';
$_['aplicar'] = 'Aplicar';
$_['aguarde'] = 'Aguarde';
$_['cupom_obrigatorio'] = 'Cupom é obrigatório.';
$_['campanha_nao_encontrado'] = 'Não foi encontrado uma campanha com o cupom informado.';
$_['cupom_nao_pode_ser_aplicado'] = 'O cupom não pode ser aplicado para esse valor.';
$_['remover'] = 'Remover';

$_['cupom_utilizado'] = 'Cupom já utilizado.';
$_['cupom_invalido'] = 'Por favor entre com um cupom válido.';
$_['valor_minimo_invalido'] = 'Sua compra não atingiu o valor mínimo ou o montante máximo.';
$_['erro_validacao_cupom'] = 'Um erro ocorreu durante a validação do cupom. Tente novamente.';

$_['unlimint_coupon'] = 'Cupom Unlimint';

$_['you_save'] = 'Você teve';
$_['desconto_exclusivo'] = 'com o desconto exclusivo';

$_['total_compra'] = 'Total da sua compra:';
$_['total_desconto'] = 'Valor total da sua compra com o desconto do Unlimint:';
$_['upon_aproval'] = 'Válido após confirmação de compra.';
$_['see_conditions'] = 'Consulte condições.';

//Payment messages
$_['S145'] = $err_payment;
$_['S160'] = $err_payment;
$_['S129'] = "payment_method_id does not process payments for the selected amount. Choose another card or another payment method.";
$_['S150'] = "You can not make payments.";
$_['S151'] = "You can not make payments.";
$_['S204'] = "Payment method is not available at this time. Choose another card or another payment method.";
$_['S801'] = "Try again in a few minutes.";

// Credit Card messages
$_['accredited'] = "Done, your payment was accredited! You will see the charge for amount in your bill as statement_descriptor.";
$_['pending_contingency'] = "We are processing the payment. In less than an hour we will e-mail you the results.";
$_['pending_review_manual'] = "We are processing the payment. In less than 2 business days we will tell you by e-mail whether it has accredited or we need more information.";
$_['cc_rejected_bad_filled_card_number'] = "Check the card number.";
$_['cc_rejected_bad_filled_date'] = "Check the expiration date.";
$_['cc_rejected_bad_filled_other'] = "Check the information.";
$_['cc_rejected_bad_filled_security_code'] = "Check the security code.";
$_['cc_rejected_blacklist'] = $err_payment;
$_['cc_rejected_call_for_authorize'] = "You must authorize to payment_method_id the payment of amount to Unlimint";
$_['cc_rejected_card_disabled'] = "Call to your credit card company to activate your card. The phone is on the back of your card.";
$_['cc_rejected_card_error'] = $err_payment;
$_['cc_rejected_duplicated_payment'] = "You already made a payment for that amount. If you need to repay, use another card or other payment method.";
$_['cc_rejected_high_risk'] = 'Your payment was rejected.Choose another payment method. We recommend cash.';
$_['cc_rejected_insufficient_amount'] = "Your card do not have sufficient funds.";
$_['cc_rejected_invalid_installments'] = "payment_method_id does not process payments in installments installments.";
$_['cc_rejected_max_attempts'] = "You have reached the limit of allowed attempts. Choose another card or another payment method.";
$_['cc_rejected_other_reason'] = "Your credit card company did not process the payment.";

//Checkout único
$_['cucoupon_empty'] = "Please, inform your coupon code";
$_['cuapply'] = "Apply";
$_['curemove'] = "Remove";
$_['cudiscount_info1'] = "You will save";
$_['cudiscount_info2'] = "with discount from";
$_['cudiscount_info3'] = "Total of your purchase:";
$_['cudiscount_info4'] = "Total of your purchase with discount:";
$_['cudiscount_info5'] = "*Upon payment approval";
$_['cudiscount_info6'] = "Terms and Conditions of Use";
$_['cucoupon_of_discounts'] = "Discount Coupon";
$_['culabel_other_bank'] = "Other Bank";
$_['culabel_choose'] = "Choose";
$_['cupayment_method'] = "Payment Method";
$_['cucredit_card_number'] = "Card number";
$_['cuexpiration_month'] = "Expiration month";
$_['cuexpiration_year'] = "Expiration year";
$_['cuexpiration_date'] = "Expiration date";
$_['cuyear'] = "Year";
$_['cumonth'] = "Month";
$_['cucard_holder_name'] = "Cardholder Name";
$_['cusecurity_code'] = "CVV2/CVC2";
$_['cudocument_type'] = "Document Type";
$_['cudocument_number'] = "CPF";
$_['cunumofinstallments'] = "Select number of installments";
$_['cuissuer'] = "Issuer";
$_['cuinstallments'] = "Installments";
$_['cuyour_card'] = "Your Card";
$_['cuother_cards'] = "Other Cards";
$_['cuother_card'] = "Other Card";
$_['cuended_in'] = "ended in";
$_['cubtn_pay'] = "Pay";

//Checkout único erros
$_['cue205'] = "Card number is not valid";
$_['cueE301'] = "Please fill out card number";
$_['cue208'] = "Invalid Expiration Date";
$_['cue209'] = 'Please fill out an expiration date';
$_['cue325'] = $_['cue208'];
$_['cue326'] = $_['cue208'];
$_['cue221'] = "Please fill out card holder name";
$_['cue316'] = "Card holder name is not valid";
$_['cue224'] = "Please fill out a CVV2/CVC2";

$_['cueE302'] = "This CVV2/CVC2 is not valid";
$_['cueE203'] = "This CVV2/CVC2 is not valid";
$_['cue212'] = "Parameter docType can not be null/empty";
$_['cue322'] = "Invalid Document Type";
$_['cue214'] = "Parameter docNumber can not be null/empty";
$_['cue324'] = "CPF is invalid";
$_['cueE324'] = "Please fill out a CPF";
$_['cue213'] = "The parameter cardholder.document.subtype can not be null or empty";
$_['cue323'] = "Invalid Document Sub Type";
$_['cue220'] = "Please select number of installments";
$_['cueEULTY'] = "Please, inform the coupon code";
