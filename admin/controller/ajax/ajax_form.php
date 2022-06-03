<?php

require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/ul_util.php";
require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/ul_refunds.php";
require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/unlimint.php";
require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/unlimint_order_info.php";
require_once __DIR__ . "/../../controller/extension/payment/lib/ul_refunds_form.php";

class ControllerAjaxAjaxForm extends Controller
{
    public const CAPTURE_PAYMENT_ACTION = 'capture';
    public const CANCEL_PAYMENT_ACTION = 'cancel';
    public const REFUND_PAYMENT_ACTION = 'refund';
    public const COMPLETE_STATUS_TO = 'COMPLETE';
    public const REVERSE_STATUS_TO = 'REVERSE';
    public const PAYMENT_DATA = 'payment_data';

    protected Unlimint $ul;

    protected ULRefunds $refund;

    protected UnlimintOrderInfo $get_prefix;

    protected function getInstanceRefund(): ULRefunds
    {
        return $this->refund ?? $this->refund = (new ULRefunds())
                ->setDb($this->db)
                ->setConfig($this->config)
                ->setUl($this->ul);
    }

    protected function getInstanceUnlimint($order_data): Unlimint
    {
        $this->get_prefix = new UnlimintOrderInfo();
        $prefix = $this->get_prefix->getPrefix($order_data['payment_code']);

        return $this->ul ?? $this->ul = Unlimint::getInstance($prefix, $this->config)
                ->setLog($this->log)
                ->setDb($this->db);
    }

    public function ajaxResponse($result, $message = '')
    {
        if (empty($this->response)) {
            $this->response = new Response();
        }

        $this->response->addHeader('Content-type: application/json');
        $this->response->setOutput(json_encode([
            "success" => $result,
            "data" => [
                "error_message" => $message,
            ]
        ], JSON_THROW_ON_ERROR));
        $this->response->output();

        return $result;
    }

    public function ajaxRefundForm(): void
    {
        $this->load->model('sale/order');

        $order_id = (int)$this->request->get['order_id'];

        $model = $this->model_sale_order;
        $order = $model->getOrder($order_id);

        if (empty($order)) {
            return;
        }

        $ul_refunds_form = (new UlRefundsForm())
            ->setLanguage($this->language)
            ->setCurrency($this->currency)
            ->setModelSaleOrder($model)
            ->setInstanceUnlimint($this->getInstanceUnlimint($order))
            ->setInstanceRefund($this->getInstanceRefund())
            ->setOrder($order)
            ->setLoader($this->load);

        $this->response->setOutput($ul_refunds_form->drawRefundForm());
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
        $this->language->load('extension/payment/ul_card');

        $order_id = (int)$_POST['order_id'];
        $data = $_POST['data'] ?? [];
        $order = $this->model_sale_order->getOrder($order_id);
        if (empty($order)) {
            return $this->ajaxResponse(false, 'Order not found');
        }
        $this->getInstanceUnlimint($order);

        $order_info = $this->ul->getorderInfo($order_id);

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
                $result = $this->ajaxResponse(false, 'Invalid request');
                break;
        }

        return $result;
    }

    public function refund_payment($order, $order_info, $data)
    {
        $this->getInstanceRefund();

        $order_total = $order_info['initial_amount'];
        $refunds = $this->refund->getTotalOrderRefunds($order['order_id']);

        $requested_amount = $data['refund'];
        if (($requested_amount <= 0) || ($requested_amount > $order_total - $refunds)) {
            return $this->ajaxResponse(false, $this->language->get('invalid_refund_amount'));
        }

        $api_request = $this->getApiRequestForRefund($order_info, $order, $requested_amount, $data['reason'] ?? '');
        $response = ULRestClient::post($this->ul->getApiUrl(), $api_request);

        if ($this->is_response_successful($response)) {
            $this->refund->logRefundedItems($order['order_id'], $data);
            $this->refund->saveRefund($order['order_id'], $response['response']);
        }

        return $this->parse_response_code($response);
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

        $api_request = $this->getApiRequestForUpdate($order_info, self::PAYMENT_DATA, self::COMPLETE_STATUS_TO, $order_total);
        $response = ULRestClient::patch($this->ul->getApiUrl(), $api_request);

        return $this->parse_response_code($response);
    }

    public function cancel_payment($order_info)
    {
        $api_request = $this->getApiRequestForUpdate($order_info, self::PAYMENT_DATA, self::REVERSE_STATUS_TO, 0);
        $response = ULRestClient::patch($this->ul->getApiUrl(), $api_request);

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

    protected function getApiRequestForRefund($order_info, $order, $amount, $reason)
    {
        $get_access_token = $this->ul->getAccessToken();

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

    protected function getApiRequestForUpdate($order_info, $api_structure, $status_to, $amount)
    {
        $transaction_id = $order_info['transaction_id'];

        $get_access_token = $this->ul->getAccessToken();

        $data = [
            'status_to' => $status_to
        ];

        if ((self::COMPLETE_STATUS_TO === $status_to) && ($order_info['initial_amount'] > $amount)) {
            $data['amount'] = $amount;
        }

        $uri = "/payments/" . $transaction_id;

        return [
            "uri" => $uri,
            "params" => [
                "access_token" => $get_access_token,
            ],
            'data' => [
                'request' => [
                    'id' => uniqid('', true),
                    'time' => date("Y-m-d\TH:i:s\Z")
                ],
                'operation' => 'CHANGE_STATUS',
                $api_structure => $data
            ],
        ];
    }
}
