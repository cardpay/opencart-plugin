<?php

/**
 * @property Loader $load
 * @property Language $language
 * @property Config $config
 */
class ModelExtensionPaymentULCard extends Model
{
    /**
     * @return array
     */
    public function getMethod(): array
    {
        $this->load->language('extension/payment/ul_card');

        return [
            'code' => 'ul_card',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_ul_card_sort_order'),
        ];
    }
}
