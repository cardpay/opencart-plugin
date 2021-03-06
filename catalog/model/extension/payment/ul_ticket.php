<?php

class ModelExtensionPaymentUlTicket extends Model
{
    public function getMethod(): array
    {
        $this->load->language('extension/payment/ul_ticket');

        return [
            'code' => 'ul_ticket',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_ul_ticket_sort_order'),
        ];
    }
}
