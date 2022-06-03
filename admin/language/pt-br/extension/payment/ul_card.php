<?php

include_once __DIR__ . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';
include_once __DIR__ . '/../../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimint Credit Card';

// Text
$_['text_ul_card'] = '<img src="view/image/payment/credit_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Verifique as formas de pagamento que deseja aceitar:';
$_['entry_url'] = 'URL da Loja: ';

$_['entry_capture_payment'] = 'Capturar Pagamento:';
$_['entry_installment_enabled'] = 'Parcelamento habilitado:';
$_['entry_ask_cpf'] = 'Solicitar CPF:';
$_['entry_dynamic_descriptor'] = 'Descritivo da compra no extrato:';

// Help
$_['help_capture_payment'] = 'Se definido como Não, o valor não será capturado, mas apenas bloqueado. Sem ter uma opção selecionada, os pagamentos serão capturados automaticamente em 7 dias a partir do momento da criação da transação pré-autorizada. No caso de parcelamento sem a opção selecionada, as parcelas serão recusadas automaticamente em 7 dias a partir do momento da criação da transação pré-autorizada.';
$_['help_installment_enabled'] = 'Se definido como Sim, o campo de parcelamento será apresentado no formulário de pagamento e o parcelamento poderá ser processado.';

$_['ul_button_cancel'] = 'Cancelar pagamento';
$_['ul_button_capture'] = 'Capturar pagamento';
$_['ul_button_refund'] = 'Pagamento de reembolso';
$_['ul_q01'] = 'Você tem certeza que deseja';
$_['ul_q02'] = 'Pagamento?';
$_['ul_q03'] = 'O pagamento não foi executado';
$_['ul_q04'] = 'O pagamento foi executado ';
$_['ul_q05'] = 'com sucesso';
$_['ul_q06'] = 'cancelar';
$_['ul_q07'] = 'capturar';
$_['ul_q08'] = 'cancelado';
$_['ul_q09'] = 'capturado';
$_['ul_q10'] = 'reembolso';
$_['ul_q11'] = 'devolveu';

$_['ul_product'] = 'Produtos';
$_['ul_model'] = 'Modelo';
$_['ul_price'] = 'Preço';
$_['ul_quantity'] = 'Quantidade';
$_['ul_amount'] = 'Valor';
$_['ul_refund'] = 'Reembolso';

$_['ul_restock'] = 'Reabastecer itens reembolsados';
$_['ul_already'] = 'Valor já reembolsado';
$_['ul_available'] = 'Total disponível para reembolso';
$_['ul_re_amount'] = 'Quantia de reembolso';
$_['ul_re_reason'] = 'Motivo do reembolso (opcional)';
$_['ul_totals'] = 'Totais';
$_['ul_cancel'] = 'Cancelar';

$_['invalid_refund_amount'] = 'Valor de reembolso não é válido.';
$_['ajax_form_e1'] = 'O valor total do pedido deve ser maior que 0 para capturar o pagamento';
$_['ajax_form_e2'] = 'O valor total do pedido não deve exceder o valor bloqueado para capturar o pagamento';
