<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlApay extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_apay');

		$option_data['ul_apay'] = [
			'code' => 'ul_apay.ul_apay',
			'name' => $this->config->get('payment_ul_apay_payment_title')
		];

		return [
			'code'       => 'ul_apay',
			'name'       => $this->config->get('payment_ul_apay_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_apay_sort_order')
		];
	}
}
