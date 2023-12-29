<?php

include_once __DIR__ . '/../../../../../catalog/controller/extension/payment/lib/ul_util.php';
include_once __DIR__ . '/../../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimint Credit Card';

// Text
$_['text_ul_card'] = '<img src="view/image/payment/credit_card.png" alt="Unlimint" title="Unlimint" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Marque los métodos de pago que desea aceptar:';
$_['entry_url'] = 'URL da Loja: ';

$_['entry_capture_payment'] = 'Capturar Pago:';
$_['entry_installment_enabled'] = 'Cuota habilitada:';
$_['entry_ask_cpf'] = 'Solicitar CPF:';
$_['entry_dynamic_descriptor'] = 'Descripción de la compra en la declaración:';

// Help
$_['help_capture_payment'] = 'Si se establece en No, el valor no se capturará sino que solo se bloqueará. Sin tener una opción seleccionada, los pagos se capturarán automáticamente dentro de los 7 días desde el momento de la creación de la transacción preautorizada. En caso de pago a plazos sin la opción seleccionada, los plazos se rechazarán automáticamente en un plazo de 7 días desde el momento de la creación de la transacción preautorizada.';
$_['help_installment_enabled'] = 'Si se establece en Sí, el campo de la cuota se mostrará en el formulario de pago y se podrá procesar la cuota.';

$_['ul_button_cancel'] = 'Cancelar el pago';
$_['ul_button_capture'] = 'Capturar pago';
$_['ul_button_refund'] = 'Reembolso';
$_['ul_q01'] = 'Estás seguro que quieres';
$_['ul_q02'] = 'Pago?';
$_['ul_q03'] = 'El pago no ha sido ejecutado';
$_['ul_q04'] = 'El pago ha sido ejecutado ';
$_['ul_q05'] = 'con éxito';
$_['ul_q06'] = 'cancelar';
$_['ul_q07'] = 'captura';
$_['ul_q08'] = 'cancelado';
$_['ul_q09'] = 'capturado';
$_['ul_q10'] = 'reembolso';
$_['ul_q11'] = 'devuelto';

$_['ul_product'] = 'Productos';
$_['ul_model'] = 'Modelo';
$_['ul_price'] = 'Precio';
$_['ul_quantity'] = 'La cantidad';
$_['ul_amount'] = 'Valor';
$_['ul_refund'] = 'Reembolso';

$_['ul_restock'] = 'Reabastecer artículos reembolsados';
$_['ul_already'] = 'Importe ya reembolsado';
$_['ul_available'] = 'Total disponible para reembolso';
$_['ul_re_amount'] = 'Cantidad devuelta';
$_['ul_re_reason'] = 'Motivo del reembolso (opcional)';
$_['ul_totals'] = 'Totales';
$_['ul_cancel'] = 'Cancelar';

$_['invalid_refund_amount'] = 'Monto de reembolso no válido';
$_['ajax_form_e1'] = 'El monto total del pedido debe ser mayor que 0 para capturar el pago';
$_['ajax_form_e2'] = 'El monto total del pedido no debe exceder el monto bloqueado para capturar el pago';
