<?php

namespace Opencart\Admin\Controller\Extension\Unlimit\Payment;

use Opencart\Admin\Controller\Extension\Unlimit\UlPayment;
use Unlimit\AjaxForm;

class UlApay extends UlPayment
{
    public const CODE = 'payment_ul_apay_';
    private const EXTENSION_PAYMENT_UL_APAY = 'extension/unlimit/payment/ul_apay';
    private $error = [];

    public function index(): void
    {
        $this->load->language('extension/unlimit/payment/ul_apay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->response->setOutput(
            $this->load->view(
                self::EXTENSION_PAYMENT_UL_APAY,
                $this->load_common_footer(['merchant_id', 'merchant_certificate', 'merchant_key'])
            )
        );
    }

    public function load_common_footer(array $posts_fields): array
    {
        $data = $this->get_data($posts_fields, static::CODE);

        $data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment',
            true
        );
        $data['save'] = $this->url->link(
            'extension/example_payment/payment/example_payment.save',
            'user_token=' . $this->session->data['user_token']
        );
        $data['back'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment'
        );
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($this->error)) {
                $this->model_setting_setting->editSetting(static::CODE, $data);
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $data;
    }

    public function get_data(array $posts, string $prefix): array
    {
        $posts = array_merge(self::POST_FIELDS, $posts);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $merchant_crt = $_FILES['payment_ul_apay_merchant_certificate'];
            $merchant_crt_value = $this->request->post[$prefix . 'merchant_certificate_existing'] ?? '';
            $this->validate_file($merchant_crt, $merchant_crt_value, 'certificate');
            if (empty($this->error) && $this->uploadFiles($merchant_crt)) {
                $data[static::CODE . 'merchant_certificate'] = $merchant_crt['error'] === 4 ?
                    $merchant_crt_value :
                    $merchant_crt['name'];
            } else {
                $data['unlimit_apay_error'] = $this->error;
                $this->error = [];
            }

            $merchant_key = $_FILES['payment_ul_apay_merchant_key'];
            $merchant_key_value = $this->request->post[$prefix . 'merchant_key_existing'] ?? '';
            $this->validate_file($merchant_key, $merchant_key_value, 'key');
            if (empty($this->error) && $this->uploadFiles($merchant_key)) {
                $data[static::CODE . 'merchant_key'] = $merchant_key['error'] === 4 ?
                    $merchant_key_value :
                    $merchant_key['name'];
            } else {
                $data['unlimit_apay_error'] = array_merge($data['unlimit_apay_error'], $this->error);
            }

            foreach ($posts as $field) {
                $fieldname = $prefix . $field;
                if (isset($this->request->post[$fieldname])) {
                    $data[$fieldname] = $this->request->post[$fieldname];
                }
            }
        } else {
            foreach ($posts as $field) {
                $fieldname = $prefix . $field;
                $value = $this->config->get($fieldname);
                $data[$fieldname] = $value;
            }
        }

        return $data ?? [];
    }

    public function validate_file($file, $value, $key)
    {
        if ($file['error'] === 4) {
            if (empty($value)) {
                $this->error[] = $this->language->get('empty_merchant_' . $key);
            }
            return false;
        }
        $mimeType = mime_content_type($file['tmp_name']);
        $name = explode('.', $file['name']);
        $ext = end($name);
        if ($mimeType !== 'text/plain' || $ext !== 'pem') {
            $this->error[] = $this->language->get('invalid_merchant_' . $key);
            return false;
        }
        return true;
    }

    public function uploadFiles($file)
    {
        if ($file['error'] === 4) {
            return true;
        }
        $file_path = dirname(__FILE__) . '/../../../uploads/' . $file['name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        if (!move_uploaded_file(
            $file['tmp_name'],
            $file_path
        )) {
            $this->error[] = $this->language->get('error_upload_file');
            return false;
        }
        return true;
    }

    public function ajax_button()
    {
        $ajax_form = new AjaxForm($this->registry);
        $ajax_form->ajax_button();
    }
}
