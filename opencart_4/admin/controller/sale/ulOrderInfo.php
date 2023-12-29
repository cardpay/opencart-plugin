<?php

namespace Unlimit;

use Opencart\System\Engine\Controller;

class UlOrderInfo extends Controller
{
    public const OC_PROCESSING_STATUS_ID = 2;
    private const EXTENSION_PAYMENT_UL_CARD = 'extension/unlimit/payment/ul_card';

    protected function renderButtons(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_CARD);
        $order_id   = (int)$this->request->get['order_id'];
        $order_info = $this->model_sale_order->getOrder($order_id);

        if ($order_info['payment_method']['code'] !== 'ul_card.ul_card') {
            return;
        }

        $data = [
            'order_id'   => $order_id,
            'user_token' => $this->session->data['user_token'],
            'buttons'    => [],
            'labels'     => [
                'ul_button_capture' => $this->language->get('ul_button_capture'),
                'ul_button_cancel'  => $this->language->get('ul_button_cancel'),
            ]
        ];

        if (((int)$order_info['order_status_id'] === self::OC_PROCESSING_STATUS_ID) &&
            ((int)$this->config->get('payment_ul_card_capture_payment') !== 1)) {
            $data['buttons']['capture'] = true;
            $data['buttons']['cancel']  = true;
        }

        $data['dialogs'] = [
            'ARE_YOU_SURE'     => $this->language->get('ul_q01'),
            'THE_PAYMENT'      => $this->language->get('ul_q02'),
            'PAYMENT_WAS_NOT'  => $this->language->get('ul_q03'),
            'PAYMENT_HAS_BEEN' => $this->language->get('ul_q04'),
            'SUCCESSFULLY'     => $this->language->get('ul_q05'),
            'CANCEL'           => $this->language->get('ul_q06'),
            'CAPTURE'          => $this->language->get('ul_q07'),
            'CANCELLED'        => $this->language->get('ul_q08'),
            'CAPTURED'         => $this->language->get('ul_q09'),
            'USER_TOKEN'       => $_REQUEST['user_token'],
            'ORDER_ID'         => $_REQUEST['order_id'],
            'URL_AJAX'         => $this->url->link(self::EXTENSION_PAYMENT_UL_CARD . '.ajaxButton',
                'user_token=' . $_REQUEST['user_token'], true),
        ];
        $t               = $this->load->view('extension/unlimit/payment/ul_buttons', $data);
        echo $t;
    }

    public function info(): void
    {
        $this->renderButtons();
    }
}
