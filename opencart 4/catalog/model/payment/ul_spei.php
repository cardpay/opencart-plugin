<?php

namespace Opencart\Catalog\Model\Extension\Unlimit\Payment;

class UlSpei extends \Opencart\System\Engine\Model
{
	public function getMethods(): array
	{
		$this->load->language('extension/unlimit/payment/ul_spei');

		$option_data['ul_spei'] = [
			'code' => 'ul_spei.ul_spei',
			'name' => $this->config->get('payment_ul_spei_payment_title')
		];

		return [
			'code'       => 'ul_spei',
			'name'       => $this->config->get('payment_ul_spei_payment_title'),
			'terms'      => '',
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_ul_spei_sort_order')
		];
	}
}
