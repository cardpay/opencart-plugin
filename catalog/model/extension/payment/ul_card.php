<?php

class ModelExtensionPaymentULCard extends Model
{
    public function getMethod()
    {
        $this->load->language('extension/payment/ul_card');


        return array(
            'code' => 'ul_card',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_ul_card_sort_order'),
        );
    }
}
