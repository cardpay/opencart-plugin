<?php

// Text
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$admin = (false !== strpos($url, 'admin')) ? '' : './admin/';
$_['text_title'] = '<img src="' . $admin . 'view/image/payment/ul_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;"> - Cartão de Crédito';

$_['cupom_utilizado'] = 'Cupom já utilizado.';
$_['cupom_invalido'] = 'Por favor entre com um cupom válido.';
$_['valor_minimo_invalido'] = 'Sua compra não atingiu o valor mínimo ou o montante máximo.';
$_['erro_validacao_cupom'] = 'Um erro ocorreu durante a validação do cupom. Tente novamente.';

$_['payment_processing'] = "Processando o pagamento.";
$_['payment_title'] = "Pagamento";
$_['payment_button'] = "Pagar";
$_['other_card_option'] = "Outro Cartão";

// Credit Card messages
$_['accredited'] = "Seu pagamento foi recebido com sucesso!";
$_['pending_contingency'] = "Estamos processando o pagamento. Em menos de uma hora você receberá um e-mail com o resultado.";
$_['pending_review_manual'] = "Estamos processando o pagamento. Em até dois dias úteis enviaremos um email informado o sucesso da operação ou se precisamos de mais informações.";
$_['cc_rejected_bad_filled_card_number'] = "Verifique o número do cartão.";
$_['cc_rejected_bad_filled_date'] = "Verifique a data de expiração do cartão.";
$_['cc_rejected_bad_filled_other'] = "Verifique suas informações.";
$_['cc_rejected_bad_filled_security_code'] = "Verifique o código de segurança do cartão.";
$_['cc_rejected_call_for_authorize'] = "Você deve solicitar a autorização do pagamento ao Unlimint à sua operadora de cartão";
$_['cc_rejected_card_disabled'] = "Ligue para sua operadora de cartão e ative seu cartão.";
$_['cc_rejected_duplicated_payment'] = "Você já escolheu um meio de pagamento para essa transação. Caso precise efetuar um novo pagamento, utilize outro cartão.";
$_['cc_rejected_high_risk'] = 'Seu pagamento foi rejeitado. Por favor, utilize outro cartão.';
$_['cc_rejected_insufficient_amount'] = "Seu cartão não possui saldo suficiente.";
$_['cc_rejected_invalid_installments'] = "Esta bandeira de cartão não permite parcelamento na quantidade de vezes escolhida.";
$_['cc_rejected_max_attempts'] = "Você atingiu o limite de tentativas. Escolha outro cartão.";
$_['cc_rejected_other_reason'] = "Sua operadora de cartão não processou o pagamento.";

//Checkout único
$_['cucoupon_empty'] = "Por favor, informe o código de seu cupom";
$_['cuapply'] = "Aplicar";
$_['curemove'] = "Remover";
$_['cudiscount_info1'] = "Você poupou";
$_['cudiscount_info2'] = "Com desconto de";
$_['cudiscount_info3'] = "Total de sua compra:";
$_['cudiscount_info4'] = "Total de sua compra com desconto:";
$_['cudiscount_info5'] = "Após a aprovação do pagamento";
$_['cudiscount_info6'] = "Termos e condições de uso";
$_['cucoupon_of_discounts'] = "Cupom de desconto";
$_['culabel_other_bank'] = "Outro banco";
$_['culabel_choose'] = "Escolha";
$_['cupayment_method'] = "Método de pagamento";
$_['cucredit_card_number'] = "Número do cartão de crédito";
$_['cuexpiration_month'] = "Mês de expiração";
$_['cuexpiration_year'] = "Ano de expiração";
$_['cuexpiration_date'] = 'Data de expiração';
$_['cuyear'] = "Ano";
$_['cumonth'] = "Mês";
$_['cusecurity_code'] = "Código de segurança";
$_['cudocument_type'] = "Tipo de documento";
$_['cudocument_number'] = "Número do documento";
$_['cunumofinstallments'] = "Select number of installments";
$_['cuissuer'] = "Emissor";
$_['cuinstallments'] = "Parcelas";
$_['cuyour_card'] = "Seu cartão";
$_['cuother_cards'] = "Outros cartões";
$_['cuother_card'] = "Outro cartão";
$_['cuended_in'] = "Terminou em";
$_['cubtn_pay'] = "Pagar";

//Checkout único erros
$_['cue205'] = "Número do cartão não pode ser nulo";
$_['cueE301'] = "Número do cartão inválido";
$_['cue221'] = "Titular do cartão não pode ser nulo";
$_['cue316'] = "Titular do cartão inválido";
$_['cue224'] = "Código de segurança não pode ser nulo";
$_['cueE302'] = "Código de segurança inválido";
$_['cueE203'] = "Código de segurança inválido";
$_['cue212'] = "Tipo do documento não pode ser nulo";
$_['cue322'] = "Tipo do documento inválido";
$_['cue214'] = "Documento não pode ser nulo";
$_['cue324'] = "Documento inválido";
$_['cue213'] = "Titular do cartão não pode ser nulo";
$_['cue323'] = "Sub tipo inválido";
$_['cue220'] = "O parametro parcelas deve ser selecionado";
$_['cueEULTY'] = "Por favor, informe o código do cupom";
