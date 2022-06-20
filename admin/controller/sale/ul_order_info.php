<?php

require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/ul_refunds.php";

class ControllerSaleUlOrderInfo extends Controller
{
    public const OC_PROCESSING_STATUS_ID = 2;

    protected function renderButtons($refund): void
    {
        $order_id = (int)$this->request->get['order_id'];
        $order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info['payment_code'] !== 'ul_card') {
            return;
        }
        $ul_refund = (new ULRefunds())->setConfig($this->config)->setDb($this->db);

        $data = [
            'order_id' => $order_id,
            'user_token' => $this->session->data['user_token'],
            'buttons' => [],
            'labels' => [
                'ul_button_capture' => $this->language->get('ul_button_capture'),
                'ul_button_cancel' => $this->language->get('ul_button_cancel'),
                'ul_button_refund' => $this->language->get('ul_button_refund'),
            ]
        ];
        if (
            ((int)$order_info['order_status_id'] === self::OC_PROCESSING_STATUS_ID) &&
            ((int)$this->config->get('payment_ul_card_capture_payment') !== 1)
        ) {
            $data['buttons']['capture'] = true;
            $data['buttons']['cancel'] = true;
        }
        if ($refund && $ul_refund->canRefund($order_info)) {
            $data['buttons']['refund'] = true;
        }

        $data['dialogs'] = [
            'ARE_YOU_SURE' => $this->language->get('ul_q01'),
            'THE_PAYMENT' => $this->language->get('ul_q02'),
            'PAYMENT_WAS_NOT' => $this->language->get('ul_q03'),
            'PAYMENT_HAS_BEEN' => $this->language->get('ul_q04'),
            'SUCCESSFULLY' => $this->language->get('ul_q05'),
            'CANCEL' => $this->language->get('ul_q06'),
            'CAPTURE' => $this->language->get('ul_q07'),
            'REFUND' => $this->language->get('ul_q10'),
            'REFUNDED' => $this->language->get('ul_q11'),
            'CANCELLED' => $this->language->get('ul_q08'),
            'CAPTURED' => $this->language->get('ul_q09'),
            'USER_TOKEN' => $_REQUEST['user_token'],
            'ORDER_ID' => $_REQUEST['order_id']
        ];
        $t = $this->load->view('extension/payment/ul_buttons', $data);
        echo $t;
    }

    public function edit(): void
    {
        $this->language->load('extension/payment/ul_card');
        $this->refund();
    }

    public function refund(): void
    {
        $this->renderButtons(true);
    }

    public function info(): void
    {
        $this->renderButtons(false);
    }
}
