<?php

namespace Opencart\Admin\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . '/../ajax/ajax_form.php';
require_once __DIR__ . '/../sale/ul_refunds_form.php';

use Opencart\Admin\Controller\Extension\Unlimit\UlPayment;
use Unlimit\AjaxForm;

class UlGpay extends UlPayment
{
    public const CODE = 'payment_ul_gpay_';
    private const EXTENSION_PAYMENT_UL_GPAY = 'extension/unlimit/payment/ul_gpay';

    public function index(): void
    {
        $this->load->language('extension/unlimit/payment/ul_gpay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->response->setOutput(
            $this->load->view(
                self::EXTENSION_PAYMENT_UL_GPAY,
                $this->load_common_footer(array_merge(self::POST_FIELDS, ['merchant_id']))
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
        $this->load->language('payment/ul_gpay');

        $json = [];

        // checking file modification permission
        if (!$this->user->hasPermission('modify', 'payment/ul_gpay')) {
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

    public function ajax_button()
    {
        $ajax_form = new AjaxForm($this->registry);
        $ajax_form->ajax_button();
    }
}
