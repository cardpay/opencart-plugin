<?php

include_once dirname(__FILE__) . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';

$ul_util = new ULOpencartUtil();
$moduleVersion = $ul_util->getModuleVersion();

// Heading
$_['heading_title'] = 'Unlimint Credit Card' . ' (v' .$moduleVersion . ')';

// Text
$_['text_payment'] = 'Pagamento';
$_['text_success'] = 'Successo, suas modificações foram salvas!';
$_['text_ul_card'] = '<img src="view/image/payment/ul_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br /><b> Cartão de Crédito</b>';
$_['text_argentina'] = 'Argentina';
$_['text_brasil'] = 'Brasil';
$_['text_colombia'] = 'Colombia';
$_['text_chile'] = 'Chile';

// Entry
$_['entry_notification_url'] = 'Sua URL de notificação é: ';
$_['entry_notification_url_tooltip'] = '<span class="help"> Esta URL será utilizada para notificar automaticamente as alterações de status dos pagamentos. Copie a URL e clique' .
    '<a href="https://www.unlimint.com/mlb/ferramentas/notificacoes" target="_blank">aqui</a>para configurar esta opção na sua conta Unlimint.</span>';
$_['entry_autoreturn'] = 'Auto Retorno';
$_['entry_autoreturn_tooltip'] = '<span class="help"> Habilita o retorno automático para a sua loja depois do pagamento. </span>';
$_['entry_client_id'] = 'Client ID : ';
$_['entry_client_id_tooltip'] = 'Para obter esta informação: <a href="https://www.unlimint.com/mla/herramientas/aplicaciones" target="_blank">Arg</a> ou <a href="https://www.unlimint.com/mlm/herramientas/aplicaciones" target="_blank">Mex</a> ou
                                                                     <a href="https://www.unlimint.com/mlv/herramientas/aplicaciones" target="_blank">Ven</a> ou <a href="https://www.unlimint.com/mlb/ferramentas/aplicacoes" target="_blank">Bra</a>';

$_['entry_client_secret'] = 'Client Secret : ';
$_['entry_client_secret_tooltip'] = 'Para obter esta informação: <a href="https://www.unlimint.com/mla/herramientas/aplicaciones" target="_blank">Arg</a> ou <a href="https://www.unlimint.com/mlm/herramientas/aplicaciones" target="_blank">Mex</a> ou
                                                                     <a href="https://www.unlimint.com/mlv/herramientas/aplicaciones" target="_blank">Ven</a> ou <a href="https://www.unlimint.com/mlb/ferramentas/aplicacoes" target="_blank">Bra</a>';

$_['entry_installments'] = 'Quantidade máxima de parcelas';
$_['entry_payments_not_accept'] = 'Marque quais meios de pagamento você deseja aceitar:';
$_['entry_payments_not_accept_tooltip'] = '';

$_['entry_status'] = 'Status:';
$_['entry_production'] = 'Production Mode:';
$_['entry_country'] = 'País das vendas:';
$_['entry_sort_order'] = 'Sort order:';
$_['entry_sponsor'] = 'Patrocinador ID: ';

$_['entry_url'] = 'URL da loja: ';
$_['entry_url_tooltip'] = '<span class="help">Insira aqui a URL da sua loja<br /> (Sempre escreva com <b>http://</b> ou <b>https://</b> )<br/><i>Ex. http://www.minhaloja.com/loja/</i><br /></span>';
$_['entry_debug'] = 'Modo Debug:';
$_['entry_debug_tooltip'] = '<span class="help">Habilite para exibir os erros no checkout</span>';

$_['entry_sandbox'] = 'Modo Sandbox: ';
$_['entry_coupon'] = 'Cúpom de Desconto: ';
$_['entry_coupon_tooltip'] = '<span class="help">* Opção válida apenas para sites participantes de campanhas de cupom.</span>';
$_['entry_sandbox_tooltip'] = '<span class="help">Sandbox é utilizado para testar o Checkout e Notificações de pagamento sem precisar de um cartão válido para aprovar a compra de teste.</span>';
$_['entry_type_checkout'] = 'Tipo de Checkout: ';
$_['entry_category'] = 'Categoria:';
$_['entry_category_tooltip'] = '<span class="help">Selecione a categoria que melhor descreve a sua loja</span>';

$_['entry_order_status'] = 'Status padrão da compra: ';
$_['entry_order_status_general'] = 'Selecione os status a serem exibidos quando a compra estiver: ';
$_['entry_order_status_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas.</span>';
$_['entry_order_status_completed'] = 'Completa:';
$_['entry_order_status_completed_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento foi <b>aprovado</b>.</span>';
$_['entry_order_status_pending'] = 'Pendente:';
$_['entry_order_status_pending_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas ainda não pagas.</span>';
$_['entry_order_status_canceled'] = 'Cancelada:';
$_['entry_order_status_canceled_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento foi <b>cancelado</b> </span>';
$_['entry_order_status_in_process'] = 'Em progresso:';
$_['entry_order_status_in_process_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento está <b>sendo analisado</b></span>';
$_['entry_order_status_rejected'] = 'Rejeitada:';
$_['entry_order_status_rejected_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento foi <b>rejeitado</b></span>';
$_['entry_order_status_refunded'] = 'Estornada:';
$_['entry_order_status_refunded_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento foi <b>estornado</b></span>';
$_['entry_order_status_in_mediation'] = 'Mediação:';
$_['entry_order_status_in_mediation_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento está <b> em mediação</b></span>';
$_['entry_order_status_chargeback'] = 'Chargeback';
$_['entry_order_status_chargeback_tooltip'] = '<span class="help">Selecione o status padrão para suas vendas cujo pagamento está <b>Chargeback</b></span>';
$_['entry_terminal_code'] = 'Terminal Code:';
$_['entry_public_key_tooltip'] = '<span class="help">Terminal Code para utilizar o checkout card. Para obtê-la, clique <a target="_blank" href="https://www.unlimint.com/mlb/account/credentials">aqui</a></span>';
$_['entry_access_token'] = 'Terminal Password:';
$_['entry_access_token_tooltip'] = '<span class="help">Terminal Password para utilizar o checkout card. Para obtê-lo, clique <a target="_blank" href="https://www.unlimint.com/mlb/account/credentials">aqui</a></span>';

// Error
$_['error_permission'] = 'Desculpe, você não possui permissão para modificar o módulo Unlimint';
$_['error_client_id'] = 'Desculpe, o <b>Client Id</b> é obrigatório.';
$_['error_client_secret'] = 'Desculpe, o <b>Client Secret</b> é obrigatório.';
$_['error_access_token'] = '<b>Terminal Password</b> inválido. Valide suas credenciais selecionando seu país: 
	<a href="https://www.unlimint.com/mla/account/credentials">Argentina</a>,
	<a href="https://www.unlimint.com/mlb/account/credentials">Brazil</a>,
	<a href="https://www.unlimint.com/mlc/account/credentials">Chile</a>,
	<a href="https://www.unlimint.com/mco/account/credentials">Colombia</a>,
	<a href="https://www.unlimint.com/mlm/account/credentials">Mexico</a>,
	<a href="https://www.unlimint.com/mpe/account/credentials">Peru</a>,
	<a href="https://www.unlimint.com/mlu/account/credentials">Uruguay</a> ou
	<a href="https://www.unlimint.com/mlv/account/credentials">Venezuela</a>';
$_['error_terminal_code'] = '<b>Terminal Code</b> inválido. Valide suas credenciais selecionando seu país: 
	<a href="https://www.unlimint.com/mla/account/credentials">Argentina</a>,
	<a href="https://www.unlimint.com/mlb/account/credentials">Brazil</a>,
	<a href="https://www.unlimint.com/mlc/account/credentials">Chile</a>,
	<a href="https://www.unlimint.com/mco/account/credentials">Colombia</a>,
	<a href="https://www.unlimint.com/mlm/account/credentials">Mexico</a>,
	<a href="https://www.unlimint.com/mpe/account/credentials">Peru</a>,
	<a href="https://www.unlimint.com/mlu/account/credentials">Uruguay</a> ou
	<a href="https://www.unlimint.com/mlv/account/credentials">Venezuela</a>';
$_['error_sponsor_span'] = 'ID do Patrocinador inválido. Este campo não é obrigatório, se você não conhece seu Patrocinador, limpe este campo!';

// installments
$_['18'] = '18';
$_['15'] = '15';
$_['12'] = '12';
$_['9'] = '9';
$_['6'] = '6';
$_['3'] = '3';
$_['1'] = '1';
