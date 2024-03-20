<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlGpay extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_gpay');

		$option_data['ul_gpay'] = [
			'code' => 'ul_gpay.ul_gpay',
			'name' => $this->config->get('payment_ul_gpay_payment_title')
		];

		return [
			'code'       => 'ul_gpay',
			'name'       => $this->config->get('payment_ul_gpay_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_gpay_sort_order')
		];
	}
}
