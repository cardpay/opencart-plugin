<?php
namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlTicket extends \Opencart\System\Engine\Model
{
    public function getMethods(): array
    {
        $this->load->language('extension/unlimit/payment/ul_ticket');

        $option_data['ul_ticket'] = [
            'code' => 'ul_ticket.ul_ticket',
            'name' => $this->config->get('payment_ul_ticket_payment_title')
        ];
        return [
            'code'       => 'ul_ticket',
            'name'       => $this->config->get('payment_ul_ticket_payment_title'),
            'terms'      => '',
            'option'     => $option_data,
            'sort_order' => $this->config->get('payment_ul_ticket_sort_order')
        ];
    }
}
