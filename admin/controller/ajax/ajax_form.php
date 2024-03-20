<?php

namespace Unlimit;

use Opencart\System\Library\Response;
use ULRefunds;
use UlRefundsForm;

require_once __DIR__ . "/../../../system/library/ul_util.php";
require_once __DIR__ . "/../../../system/library/unlimit.php";
require_once __DIR__ . "/../../../system/library/unlimit_order_info.php";

class AjaxForm extends \Opencart\System\Engine\Controller
{
    public const CAPTURE_PAYMENT_ACTION = 'capture';
    public const CANCEL_PAYMENT_ACTION = 'cancel';
    public const REFUND_PAYMENT_ACTION = 'refund';
    public const COMPLETE_STATUS_TO = 'COMPLETE';
    public const REVERSE_STATUS_TO = 'REVERSE';
    public const PAYMENT_DATA = 'payment_data';

    protected Unlimit $ul;

    protected UnlimitOrderInfo $get_prefix;

    protected ULRefunds $refund;

    /**
     * @throws JsonException
     */
    public function ajax_response($result, $message = '')
    {
        if (empty($this->response)) {
            $this->response = new Response();
        }

        $this->response->addHeader('Content-type: application/json');
        $this->response->setOutput(
            json_encode([
                "success" => $result,
                "data" => [
                    "error_message" => $message,
                ]
            ], JSON_THROW_ON_ERROR)
        );
        $this->response->output();

        return $result;
    }

    public function ajax_refund_form(): void
    {
        $this->load->model('sale/order');

        $order_id = (int)$this->request->get['order_id'];

        $model = $this->model_sale_order;
        $order = $model->getOrder($order_id);

        if (empty($order)) {
            return;
        }

        $ul_refunds_form = (new UlRefundsForm())
            ->set_language($this->language)
            ->set_currency($this->currency)
            ->set_model_sale_order($model)
            ->set_instance_unlimit($this->get_instance_unlimit($order))
            ->set_instance_refund($this->get_instance_refund())
            ->set_order($order)
            ->set_loader($this->load);

        $this->response->setOutput($ul_refunds_form->draw_refund_form($this->db));
    }

    protected function get_instance_refund(): ULRefunds
    {
        return $this->refund ?? $this->refund = (new ULRefunds())
                ->set_db($this->db)
                ->set_currency($this->currency)
                ->set_config($this->config)
                ->set_ul($this->ul);
    }

    protected function get_instance_unlimit($order_data): Unlimit
    {
        $this->get_prefix = new UnlimitOrderInfo();
        $paymentMethodCode = $order_data['payment_method']['code'];
        $parts = explode('.', $paymentMethodCode);
        $prefix = $this->get_prefix->get_prefix($parts[0]);

        return $this->ul ?? $this->ul = Unlimit::get_instance($prefix, $this->config)
                ->set_log($this->log)
                ->set_db($this->db);
    }

    public function ajax_button()
    {
        return $this->do_payment_action($_POST['action']);
    }

    /**
     * @throws JsonException
     */
    public function do_payment_action($payment_action)
    {
        $this->load->model('sale/order');
        $this->language->load('extension/unlimit/payment/ul_card', '', $this->config->get('config_language'));

        $order_id = (int)$_POST['order_id'];
        $order = $this->model_sale_order->getOrder($order_id);
        if (empty($order)) {
            return $this->ajax_response(false, 'Order not found');
        }
        $this->get_instance_unlimit($order);

        $data = $_POST['data'] ?? [];
        $order_info = $this->ul->get_order_info($order_id);

        switch ($payment_action) {
            case self::CAPTURE_PAYMENT_ACTION:
                $result = $this->capture_payment($order, $order_info);
                break;
            case self::CANCEL_PAYMENT_ACTION:
                $result = $this->cancel_payment($order_info);
                break;
            case self::REFUND_PAYMENT_ACTION:
                $result = $this->refund_payment($order, $order_info, $data);
                break;
            default:
                $result = $this->ajax_response(false, 'Invalid request');
                break;
        }

        return $result;
    }

    public function refund_payment($order, $order_info, $data)
    {
        $this->get_instance_refund();

        $order_total = $order_info['initial_amount'];
        $refunds = $this->refund->get_total_order_refunds($order['order_id']);

        $requested_amount = $data['refund'];
        if ($requested_amount <= 0 || ($requested_amount > $order_total - $refunds)) {
            return $this->ajax_response(false, $this->language->get('invalid_refund_amount'));
        }

        $api_request = $this->get_api_request_for_refund($order_info, $order, $requested_amount, $data['reason'] ?? '');
        $response = ULRestClient::post($this->ul->get_api_url(), $api_request);

        if ($this->is_response_successful($response)) {
            $this->refund->record_refunded_items($order, $data);
            $this->refund->save_refund($order, $response['response']);
        }

        return $this->parse_response_code($response);
    }

    protected function get_api_request_for_refund($order_info, $order, $amount, $reason)
    {
        $get_access_token = $this->ul->get_access_token();

        return [
            "uri" => '/refunds/',
            "params" => [
                "access_token" => $get_access_token,
            ],
            'data' => [
                'request' => [
                    'id' => uniqid('', true),
                    'time' => date("Y-m-d\TH:i:s\Z")
                ],
                'merchant_order' => [
                    'description' => (!empty($reason)) ? $reason : "Refund for order #" . $order_info['order_id'],
                    'id' => $order_info['order_id']
                ],
                self::PAYMENT_DATA => [
                    'id' => $order_info['transaction_id']
                ],
                'refund_data' => [
                    'amount' => $amount,
                    'currency' => $order['currency_code']
                ]

            ],
        ];
    }

    public function capture_payment($order, $order_info)
    {
        $order_total = $order['total'];

        if ($order_total <= 0) {
            return $this->ajax_response(false, $this->language->get('ajax_form_e1'));
        }

        $initial_order_amount = $order_info['initial_amount'];
        if ($order_total > (float)$initial_order_amount) {
            return $this->ajax_response(false, $this->language->get('ajax_form_e2'));
        }

        $api_request = $this->get_api_request_for_update(
            $order_info,
            self::PAYMENT_DATA,
            self::COMPLETE_STATUS_TO,
            $order_total
        );
        $response = ULRestClient::patch($this->ul->get_api_url(), $api_request);

        return $this->parse_response_code($response);
    }

    public function cancel_payment($order_info)
    {
        $api_request = $this->get_api_request_for_update($order_info, self::PAYMENT_DATA, self::REVERSE_STATUS_TO, 0);
        $response = ULRestClient::patch($this->ul->get_api_url(), $api_request);

        return $this->parse_response_code($response);
    }

    protected function is_response_successful($response): bool
    {
        $completeArray = ['AUTHORIZED', 'COMPLETED', 'REFUNDED'];
        if (isset($response['response']['refund_data']['status']) &&
            !in_array($response['response']['refund_data']['status'], $completeArray)) {
            return false;
        }

        return isset($response['status']) && in_array($response['status'], [200, 201]);
    }

    public function parse_response_code($response)
    {
        $success = $this->is_response_successful($response);
        $message = $response['response']['message'] ?? '';
        $this->ul->write_log(json_encode($response));

        return ($success) ? $this->ajax_response(true) : $this->ajax_response(false, $message);
    }

    protected function get_api_request_for_update($order_info, $api_structure, $status_to, $amount)
    {
        $transaction_id = $order_info['transaction_id'];

        $get_access_token = $this->ul->get_access_token();

        $data = [
            'status_to' => $status_to
        ];

        if ((self::COMPLETE_STATUS_TO === $status_to) && ($order_info['initial_amount'] > $amount)) {
            $data['amount'] = number_format($amount, 2);
        }

        $uri = "/payments/" . $transaction_id;

        return [
            "uri" => $uri,
            "headers" => [
                "Authorization" => "Bearer " . $get_access_token,
            ],
            "data" => [
                "request" => [
                    "id" => uniqid('', true),
                    "time" => date("Y-m-d\TH:i:s\Z")
                ],
                "operation" => 'CHANGE_STATUS',
                $api_structure => $data
            ],
        ];
    }
}
