<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlPaypal extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_paypal');

		$option_data['ul_paypal'] = [
			'code' => 'ul_paypal.ul_paypal',
			'name' => $this->config->get('payment_ul_paypal_payment_title')
		];

		return [
			'code'       => 'ul_paypal',
			'name'       => $this->config->get('payment_ul_paypal_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_paypal_sort_order')
		];
	}
}
