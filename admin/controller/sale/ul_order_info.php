<?php

namespace Unlimit;

use Opencart\System\Engine\Controller;
use ULRefunds;

require_once __DIR__ . '/../../../system/library/ul_refunds.php';

class UlOrderInfo extends Controller
{
    public const OC_PROCESSING_STATUS_ID = 2;
    private const UL_PAYMENT_EXTENSION_PATH = 'extension/unlimit/ul_payment';
    private const EXTENSION_PAYMENT_UL_CARD = 'extension/unlimit/payment/ul_card';
    private const EXTENSION_UNLIMIT_PAYMENT_PATH = 'extension/unlimit/payment/';

    protected function render_buttons(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_CARD);
        $order_id = (int)$this->request->get['order_id'];
        $order_info = $this->model_sale_order->getOrder($order_id);

        $prefix = [
            'ul_card.ul_card',
            'ul_gpay.ul_gpay',
            'ul_mbway.ul_mbway',
            'ul_paypal.ul_paypal',
            'ul_apay.ul_apay',
        ];

        if (!in_array($order_info['payment_method']['code'], $prefix)) {
            return;
        }

        $ul_refund = (new ULRefunds())->set_config($this->config)->set_db($this->db);

        $data = $this->prepare_data($order_id, $order_info, $ul_refund);

        $t = $this->load->view('extension/unlimit/payment/ul_buttons', $data);
        echo $t;
    }

    private function prepare_data($order_id, $order_info, $ul_refund)
    {
        $data = [
            'order_id' => $order_id,
            'user_token' => $this->session->data['user_token'],
            'buttons' => $this->prepare_buttons($order_info, $ul_refund),
            'labels' => $this->prepare_labels(),
            'dialogs' => $this->prepare_dialogs($order_info),
            'payment_method' => strstr(
                $order_info['payment_method']['code'],
                '.',
                true
            ),
        ];

        return $data;
    }

    private function prepare_buttons($order_info, $ul_refund)
    {
        $buttons = [];

        if ($this->can_capture($order_info)) {
            $buttons['capture'] = true;
            $buttons['cancel'] = true;
        }

        if ($ul_refund->can_cefund($order_info)) {
            $buttons['refund'] = true;
        }

        return $buttons;
    }

    private function can_capture($order_info)
    {
        $isProcessingStatus = (int)$order_info['order_status_id'] === self::OC_PROCESSING_STATUS_ID;
        $isCapturePaymentNotSet = (int)$this->config->get('payment_ul_card_capture_payment') !== 1;
        $isUlCard = $order_info['payment_method']['code'] === 'ul_card.ul_card';

        return $isProcessingStatus && $isCapturePaymentNotSet && $isUlCard;
    }

    private function prepare_labels()
    {
        return [
            'ul_button_capture' => $this->language->get('ul_button_capture'),
            'ul_button_cancel' => $this->language->get('ul_button_cancel'),
            'ul_button_refund' => $this->language->get('ul_button_refund'),
        ];
    }

    private function prepare_dialogs($order_info)
    {
        return [
            'ARE_YOU_SURE' => $this->language->get('ul_q01'),
            'THE_PAYMENT' => $this->language->get('ul_q02'),
            'PAYMENT_WAS_NOT' => $this->language->get('ul_q03'),
            'PAYMENT_HAS_BEEN' => $this->language->get('ul_q04'),
            'SUCCESSFULLY' => $this->language->get('ul_q05'),
            'CANCEL' => $this->language->get('ul_q06'),
            'CAPTURE' => $this->language->get('ul_q07'),
            'CANCELLED' => $this->language->get('ul_q08'),
            'CAPTURED' => $this->language->get('ul_q09'),
            'REFUND' => $this->language->get('ul_q10'),
            'REFUNDED' => $this->language->get('ul_q11'),
            'USER_TOKEN' => $_REQUEST['user_token'],
            'ORDER_ID' => $_REQUEST['order_id'],
            'URL_AJAX' => $this->url->link(
                self::EXTENSION_UNLIMIT_PAYMENT_PATH . strstr(
                    $order_info['payment_method']['code'],
                    '.',
                    true
                ) . '.ajax_button',
                'user_token=' . $_REQUEST['user_token'],
                true
            ),
            'URL_GET_REFUND' => $this->url->link(
                self::UL_PAYMENT_EXTENSION_PATH . '.ajax_refund_form',
                'user_token=' . $_REQUEST['user_token'] . '&order_id=' . $_REQUEST['order_id'],
                true
            )
        ];
    }

    public function info(): void
    {
        $this->render_buttons();
    }
}
