<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlSepa extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_sepa');

		$option_data['ul_sepa'] = [
			'code' => 'ul_sepa.ul_sepa',
			'name' => $this->config->get('payment_ul_sepa_payment_title')
		];

		return [
			'code'       => 'ul_sepa',
			'name'       => $this->config->get('payment_ul_sepa_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_sepa_sort_order')
		];
	}
}
