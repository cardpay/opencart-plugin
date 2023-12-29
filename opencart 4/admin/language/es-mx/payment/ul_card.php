<?php

include_once __DIR__ . '/../../ul_general.php';
include_once __DIR__ . '/ul_common.php';

// Heading
$_['heading_title'] = 'Unlimit Credit Card';

// Text
$_['text_ul_card'] = '<img src="../extension/unlimit/admin/view/image/payment/credit_card.png" alt="Unlimit" title="Unlimit" style="border: 1px solid #EEEEEE; background-color: white;" /><br />';

// Entry
$_['entry_payments_not_accept'] = 'Marque los métodos de pago que desea aceptar:';

$_['entry_capture_payment']               = 'Capturar pago:';
$_['entry_installment_enabled']           = 'Cuota habilitada:';
$_['entry_ask_cpf']                       = 'Solicitar CPF:';
$_['entry_dynamic_descriptor']            = 'Descripción de la compra en la declaración:';
$_['text_issuer_financed']                = 'Emisor financiado';
$_['text_merchant_financed']              = 'Comerciante financiado';
$_['entry_installment_type']              = 'Tipo de cuota';
$_['help_installment_type']               = 'Debe seleccionarse solo si el ajuste "Cuota habilitada" está activado. Aquí se puede elegir el tipo de pago utilizado en el complemento comercial.
Puede leer más detalles sobre los tipos de cuotas <a href="https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#issuer-financed-if">IF (Emisor financiado) cuotas</a>, <a href="https://integration.unlimit.com/doc-guides/yjri881uncqhc-overview#merchant-financed-mf_hold">MF HOLD (Comerciante financiado) cuotas</a>.';
$_['entry_minimum_installment_amount']    = 'Importe mínimo de la cuota';
$_['help_minimum_installment_amount']     = 'Importe mínimo de cuota para pedidos con cuotas.
Aquí se puede completar la cantidad mínima de 1 cuota, por ejemplo, si tenemos 5 cuotas con 20 usd, la cantidad de 1 cuota, el monto total del pedido en este caso es de 100 usd.';
$_['entry_maximum_accepted_installments'] = 'Rango de cuotas permitidas';
$_['help_maximum_accepted_installments']  = 'Rango de cuotas permitidas,
para las cuotas "Comerciante financiado" se pueden completar en el rango de valores permitidos o varios valores permitidos que no están en una fila.
Todos los valores pueden ser del intervalo 1-12, por ejemplo: Rango de valores 3-7 (usando "-" como separador). Valores permitidos no en una fila 2, 3, 6, 8, 12 (usando "," como separador).
Para el tipo de Cuota "Emisor financiado" solo se pueden permitir valores no consecutivos de los siguientes: 3, 6, 9, 12, 18.
Si está vacío, se utilizarán los valores predeterminados (2-12 para "Comerciante financiado"  y 3, 6, 9, 12, 18 para "Emisor financiado").';


// Error
$_['error_access_token'] = '<b>Contraseña de terminal</b> no válido.';
$_['error_public_key']   = '<b>Código de terminal</b> no válido.';

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
