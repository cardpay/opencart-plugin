<?php
namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlPix extends \Opencart\System\Engine\Model
{
    /**
     * @return array
     */
    public function getMethods(): array
    {
        $this->load->language('extension/unlimit/payment/ul_pix');

        $option_data['ul_pix'] = [
            'code' => 'ul_pix.ul_pix',
            'name' => $this->config->get('payment_ul_pix_payment_title')
        ];
        return [
            'code'       => 'ul_pix',
            'name'       => $this->config->get('payment_ul_pix_payment_title'),
            'terms'      => '',
            'option'     => $option_data,
            'sort_order' => $this->config->get('payment_ul_pix_sort_order')
        ];
    }
}
