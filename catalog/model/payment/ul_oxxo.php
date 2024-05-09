<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlOxxo extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_oxxo');

		$option_data['ul_oxxo'] = [
			'code' => 'ul_oxxo.ul_oxxo',
			'name' => $this->config->get('payment_ul_oxxo_payment_title')
		];

		return [
			'code'       => 'ul_oxxo',
			'name'       => $this->config->get('payment_ul_oxxo_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_oxxo_sort_order')
		];
	}
}
