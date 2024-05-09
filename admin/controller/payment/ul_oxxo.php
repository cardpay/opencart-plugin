<?php

namespace Opencart\Admin\Controller\Extension\Unlimit\Payment;

use Opencart\Admin\Controller\Extension\Unlimit\UlPayment;

class UlOxxo extends UlPayment
{
    public const CODE = 'payment_ul_oxxo_';
    private const EXTENSION_PAYMENT_UL_OXXO = 'extension/unlimit/payment/ul_oxxo';

    public function index(): void
    {
        $this->load->language('extension/unlimit/payment/ul_oxxo');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->response->setOutput(
            $this->load->view(
                self::EXTENSION_PAYMENT_UL_OXXO,
                $this->load_common_footer(array_merge(self::POST_FIELDS, ['payment_page']))
            )
        );
    }

    /**
     * save method
     *
     * @return void
     */
    public function save(): void
    {
        // loading example payment language
        $this->load->language('payment/ul_oxxo');

        $json = [];

        // checking file modification permission
        if (!$this->user->hasPermission('modify', 'payment/ul_oxxo')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting(static::CODE, $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
