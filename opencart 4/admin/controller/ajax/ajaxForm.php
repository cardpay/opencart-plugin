<?php

namespace Unlimit;

use Opencart\System\Library\Response;

require_once __DIR__ . "/../../../system/library/ul_util.php";
require_once __DIR__ . "/../../../system/library/unlimit.php";
require_once __DIR__ . "/../../../system/library/unlimit_order_info.php";

class AjaxForm extends \Opencart\System\Engine\Controller
{
    public const CAPTURE_PAYMENT_ACTION = 'capture';
    public const CANCEL_PAYMENT_ACTION = 'cancel';
    public const COMPLETE_STATUS_TO = 'COMPLETE';
    public const REVERSE_STATUS_TO = 'REVERSE';
    public const PAYMENT_DATA = 'payment_data';

    protected Unlimit $ul;

    protected UnlimitOrderInfo $get_prefix;

    protected function getInstanceUnlimit($order_data): Unlimit
    {
        $this->get_prefix  = new UnlimitOrderInfo();
        $paymentMethodCode = $order_data['payment_method']['code'];
        $parts             = explode('.', $paymentMethodCode);
        $prefix            = $this->get_prefix->getPrefix($parts[0]);

        return $this->ul ?? $this->ul = Unlimit::getInstance($prefix, $this->config)
                                               ->setLog($this->log)
                                               ->setDb($this->db);
    }

    /**
     * @throws JsonException
     */
    public function ajaxResponse($result, $message = '')
    {
        if (empty($this->response)) {
            $this->response = new Response();
        }

        $this->response->addHeader('Content-type: application/json');
        $this->response->setOutput(json_encode([
            "success" => $result,
            "data"    => [
                "error_message" => $message,
            ]
        ], JSON_THROW_ON_ERROR));
        $this->response->output();

        return $result;
    }

    public function ajaxButton()
    {
        return $this->doPaymentAction($_POST['action']);
    }

    /**
     * @throws JsonException
     */
    public function doPaymentAction($payment_action)
    {
        $this->load->model('sale/order');
        $this->language->load('extension/unlimit/payment/ul_card');

        $order_id = (int)$_POST['order_id'];
        $order    = $this->model_sale_order->getOrder($order_id);
        if (empty($order)) {
            return $this->ajaxResponse(false, 'Order not found');
        }
        $this->getInstanceUnlimit($order);

        $order_info = $this->ul->getorderInfo($order_id);

        switch ($payment_action) {
            case self::CAPTURE_PAYMENT_ACTION:
                $result = $this->capture_payment($order, $order_info);
                break;
            case self::CANCEL_PAYMENT_ACTION:
                $result = $this->cancel_payment($order_info);
                break;
            default:
                $result = $this->ajaxResponse(false, 'Invalid request');
                break;
        }

        return $result;
    }

    public function capture_payment($order, $order_info)
    {
        $order_total = $order['total'];

        if ($order_total <= 0) {
            return $this->ajaxResponse(false, $this->language->get('ajax_form_e1'));
        }

        $initial_order_amount = $order_info['initial_amount'];
        if ($order_total > (float)$initial_order_amount) {
            return $this->ajaxResponse(false, $this->language->get('ajax_form_e2'));
        }

        $api_request = $this->getApiRequestForUpdate($order_info, self::PAYMENT_DATA, self::COMPLETE_STATUS_TO,
            $order_total);
        $response    = ULRestClient::patch($this->ul->getApiUrl(), $api_request);

        return $this->parse_response_code($response);
    }

    public function cancel_payment($order_info)
    {
        $api_request = $this->getApiRequestForUpdate($order_info, self::PAYMENT_DATA, self::REVERSE_STATUS_TO, 0);
        $response    = ULRestClient::patch($this->ul->getApiUrl(), $api_request);

        return $this->parse_response_code($response);
    }

    protected function is_response_successful($response): bool
    {
        return (isset($response['status']) && in_array($response['status'], [200, 201]));
    }

    public function parse_response_code($response)
    {
        $success = $this->is_response_successful($response);
        $message = $response['response']['message'] ?? '';
        $this->ul->writeLog(json_encode($response));

        return ($success) ? $this->ajaxResponse(true) : $this->ajaxResponse(false, $message);
    }

    protected function getApiRequestForUpdate($order_info, $api_structure, $status_to, $amount)
    {
        $transaction_id = $order_info['transaction_id'];

        $get_access_token = $this->ul->getAccessToken();

        $data = [
            'status_to' => $status_to
        ];

        if ((self::COMPLETE_STATUS_TO === $status_to) && ($order_info['initial_amount'] > $amount)) {
            $data['amount'] = number_format($amount, 2);
        }

        $uri = "/payments/" . $transaction_id;

        return [
            "uri"     => $uri,
            "headers" => [
                "Authorization" => "Bearer " . $get_access_token,
            ],
            "data"    => [
                "request"      => [
                    "id"   => uniqid('', true),
                    "time" => date("Y-m-d\TH:i:s\Z")
                ],
                "operation"    => 'CHANGE_STATUS',
                $api_structure => $data
            ],
        ];
    }
}
