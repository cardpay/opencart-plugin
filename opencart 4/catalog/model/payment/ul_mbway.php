<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlMbway extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_mbway');

		$option_data['ul_mbway'] = [
			'code' => 'ul_mbway.ul_mbway',
			'name' => $this->config->get('payment_ul_mbway_payment_title')
		];

		return [
			'code'       => 'ul_mbway',
			'name'       => $this->config->get('payment_ul_mbway_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_mbway_sort_order')
		];
	}
}
