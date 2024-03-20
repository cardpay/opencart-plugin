<?php

include_once __DIR__ . '/../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimit Credit Card';

// Text
$_['text_ul_card'] = '<img src="../extension/unlimit/admin/view/image/payment/credit_card.png" alt="Unlimit" title="Unlimit" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Marque los métodos de pago que desea aceptar:';

$_['entry_capture_payment']               = 'Capturar pagamento:';
$_['entry_installment_enabled']           = 'Parcelamento habilitado:';
$_['entry_ask_cpf']                       = 'Solicitar CPF:';
$_['entry_dynamic_descriptor']            = 'Descritivo da compra no extrato:';
$_['text_issuer_financed']                = 'Financiado pelo emissor';
$_['text_merchant_financed']              = 'Financiado pelo comerciante';
$_['entry_installment_type']              = 'Tipo de parcela';
$_['help_installment_type']               = 'Deve ser selecionado somente se a configuração "Parcelamento habilitado" estiver ativada.
Aqui pode ser escolhido o Tipo de cuotamento usado no plugin de negociação. Mais detalhes sobre os tipos de parcelas, consulte <a href="https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#issuer-financed-if">IF (financiadas pelo emissor) Parcelas</a>,
 <a href="https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#merchant-financed-mf_hold">Parcelas MF HOLD (financiado pelo comerciante)</a>.';
$_['entry_minimum_installment_amount']    = 'Valor mínimo da parcela';
$_['help_minimum_installment_amount']     = 'Valor mínimo da parcela para pedido com parcelamento..
Aqui pode ser preenchido valor mínimo de 1 parcela, ou seja, se tivermos 5 parcelas com R$ 20 valor de 1 parcela, o valor total do pedido neste caso é de';
$_['entry_maximum_accepted_installments'] = 'Faixa de parcelas permitidas';
$_['help_maximum_accepted_installments']  = 'Faixa de parcelas permitidas, Para as parcelas "Financiado pelo comerciante" podem ser preenchidas faixas de valores permitidos ou vários valores permitidos não consecutivos.
Todos os valores podem ser do intervalo entre 1-12, por exemplo: Faixa de valores 3-7 (usando "-" como separador). Valores permitidos fora da linha 2, 3, 6, 8, 12 (usando "," como separador).
Para o tipo de prestação "Financiado pelo emissor" só podem ser permitidos valores não consecutivos a partir do seguinte: 3, 6, 9, 12, 18.';


// Error
$_['error_access_token'] = '<b>Senha do Terminal</b> no válido.';
$_['error_public_key']   = '<b>Código do terminal</b> no válido.';

// Help
$_['help_capture_payment']     = 'Se definido como Não, o valor não será capturado, mas apenas bloqueado. Sem ter uma opção selecionada, os pagamentos serão capturados automaticamente em 7 dias a partir do momento da criação da transação pré-autorizada. No caso de parcelamento sem a opção selecionada, as parcelas serão recusadas automaticamente em 7 dias a partir do momento da criação da transação pré-autorizada.';
$_['help_installment_enabled'] = 'Se definido como Sim, o campo de parcelamento será apresentado no formulário de pagamento e o parcelamento poderá ser processado.';

$_['ul_button_cancel']  = 'Cancelar pagamento';
$_['ul_button_capture'] = 'Capturar pagamento';
$_['ul_button_refund']  = 'Reembolso';
$_['ul_q01']            = 'Você tem certeza que deseja';
$_['ul_q02']            = 'Pago?';
$_['ul_q03']            = 'O pagamento não foi executado';
$_['ul_q04']            = 'O pagamento foi executado ';
$_['ul_q05']            = 'com sucesso';
$_['ul_q06']            = 'cancelar';
$_['ul_q07']            = 'capturar';
$_['ul_q08']            = 'cancelado';
$_['ul_q09']            = 'capturado';
$_['ul_q10']            = 'reembolso';
$_['ul_q11']            = 'devolveu';

$_['ul_product']  = 'Produtos';
$_['ul_model']    = 'Modelo';
$_['ul_price']    = 'Preço';
$_['ul_quantity'] = 'Quantidade';
$_['ul_amount']   = 'Valor';
$_['ul_refund']   = 'Reembolso';

$_['ul_restock']   = 'Reabastecer itens reembolsados';
$_['ul_already']   = 'Valor já reembolsado';
$_['ul_available'] = 'Total disponível para reembolso';
$_['ul_re_amount'] = 'Quantia de reembolso';
$_['ul_re_reason'] = 'Motivo do reembolso (opcional)';
$_['ul_totals']    = 'Totais';
$_['ul_cancel']    = 'Cancelar';

$_['invalid_refund_amount'] = 'Valor de reembolso não é válido.';
$_['ajax_form_e1']          = 'O valor total do pedido deve ser maior que 0 para capturar o pagamento';
$_['ajax_form_e2']          = 'O valor total do pedido não deve exceder o valor bloqueado para capturar o pagamento';
