<?php
namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;
class UlCard extends \Opencart\System\Engine\Model
{
    public function getMethods(array $address = []): array {

        $this->load->language('extension/unlimit/payment/ul_card');

        $option_data['ul_card'] = [
            'code' => 'ul_card.ul_card',
            'name' => $this->config->get('payment_ul_card_payment_title')
        ];
        return [
            'code'       => 'ul_card',
            'name'       => $this->config->get('payment_ul_card_payment_title'),
            'terms'      => '',
            'option'     => $option_data,
            'sort_order' => $this->config->get('payment_ul_card_sort_order')
        ];
    }
}