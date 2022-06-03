<?php

class ModelExtensionPaymentUlPix extends Model
{
    public function getMethod()
    {
        $this->load->language('extension/payment/ul_pix');

        return [
            'code' => 'ul_pix',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_ul_pix_sort_order'),
        ];
    }
}
