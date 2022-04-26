<?php

class ModelExtensionPaymentULTicket extends Model
{
    public function getMethod()
    {
        $this->load->language('extension/payment/ul_ticket');

        return array(
            'code' => 'ul_ticket',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_ul_ticket_sort_order'),
        );
    }
}
