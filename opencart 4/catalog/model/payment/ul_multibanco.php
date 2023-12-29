<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlMultibanco extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_multibanco');

		$option_data['ul_multibanco'] = [
			'code' => 'ul_multibanco.ul_multibanco',
			'name' => $this->config->get('payment_ul_multibanco_payment_title')
		];

		return [
			'code'       => 'ul_multibanco',
			'name'       => $this->config->get('payment_ul_multibanco_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_multibanco_sort_order')
		];
	}
}
