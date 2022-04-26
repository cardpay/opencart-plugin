<?php

class ControllerSaleUlOrderInfo extends Controller
{
    public function edit()
    {
        $this->language->load('extension/payment/ul_card');
        return $this->info('');
    }

    public function info($style = 'line-height:12px; height: 31px; ')
    {
        $order_id = (int)$this->request->get['order_id'];
        $order_info = $this->model_sale_order->getOrder($order_id);
        if (
            ($order_info['payment_code'] != 'ul_card') ||
            ($order_info['order_status_id'] != 2) ||
            ($this->config->get('payment_ul_card_capture_payment') == '1')
        ) {
            return;
        }


        $capture_button_label = $this->language->get('ul_button_capture');
        $cancel_button_label = $this->language->get('ul_button_cancel');

        echo "<script type='text/javascript'>
            window.onload = function() {
                jQuery(`
                    <button type='button' id='ul_button_cancel' class='btn btn-error pull-right' style='" . $style . "margin-left: 4px;' onclick='ulCancelPayment()'>$cancel_button_label</button>
                    <button type='button' id='ul_button_capture' class='btn pull-right ' style='" . $style . "margin-left: 4px;' onclick='ulCapturePayment()'>$capture_button_label</button>
                `).insertBefore('div.page-header');
            }
        </script>";
        $this->echo_bankcard_translations();
    }


    private function echo_bankcard_translations()
    {
        $bankcard_translations = [
            'ARE_YOU_SURE' => $this->language->get('ul_q01'),
            'THE_PAYMENT' => $this->language->get('ul_q02'),
            'PAYMENT_WAS_NOT' => $this->language->get('ul_q03'),
            'PAYMENT_HAS_BEEN' => $this->language->get('ul_q04'),
            'SUCCESSFULLY' => $this->language->get('ul_q05'),
            'CANCEL' => $this->language->get('ul_q06'),
            'CAPTURE' => $this->language->get('ul_q07'),
            'CANCELLED' => $this->language->get('ul_q08'),
            'CAPTURED' => $this->language->get('ul_q09'),
            'USER_TOKEN' => $_REQUEST['user_token'],
            'ORDER_ID' => $_REQUEST['order_id'],
        ];

        $bankcard_alert_translations = '{';
        foreach ($bankcard_translations as $key => $value) {
            $bankcard_alert_translations .= "\"$key\":\"$value\"";
            if (array_key_last($bankcard_translations) != $key) {
                $bankcard_alert_translations .= ',';
            }
        }
        $bankcard_alert_translations .= '}';

        echo "
            <script type='text/javascript' src='./view/javascript/ul/card/bankcard_settings_unlimin.js'></script>

			<script type='text/javascript'>
			if (typeof BANKCARD_ALERT_TRANSLATIONS === 'undefined') {
                var BANKCARD_ALERT_TRANSLATIONS = $bankcard_alert_translations;
            }
			</script>
		";
    }
}
