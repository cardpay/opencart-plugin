<?php

require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/ul_util.php";
require_once __DIR__ . "/../../../catalog/controller/extension/payment/lib/unlimint.php";

class ControllerAjaxAjaxForm extends Controller
{
    public const CAPTURE_PAYMENT_ACTION = 'capture';
    public const CANCEL_PAYMENT_ACTION = 'cancel';
    public const COMPLETE_STATUS_TO = 'COMPLETE';
    public const REVERSE_STATUS_TO = 'REVERSE';

    /**
     * @var UL $ul
     */
    protected $ul;

    protected function get_instance_ul($order_data)
    {
        switch ($order_data['payment_code']) {
            case 'ul_card':
                $prefix = 'card';
                break;
            case 'ul_ticket':
                $prefix = 'ticket';
                break;
            default:
                $prefix = '';
        }
        return $this->ul ?? $this->ul = UL::getInstance($prefix, $this->config)
                ->setDb($this->db);
    }

    public function ajaxResponse($result, $message = '')
    {
        header('Content-type: application/json', true);
        return json_encode(
            [
                "success" => $result,
                "data" => [
                    "error_message" => $message,
                ]
            ]
        );
    }

    public function ajax_button()
    {
        echo $this->do_payment_action($_POST['action']);
    }

    public function do_payment_action($payment_action)
    {
        $order_id = (int)$_POST['order_id'];
        $order = $this->db->query("SELECT * FROM " . DB_PREFIX . "order WHERE " . DB_PREFIX . "order.order_id = '" . $order_id . "' limit 1");
        if (empty($order->row) || empty($order->row['order_id'])) {
            return $this->ajaxResponse(false, 'Order not found');
        }
        $order = $order->row;
        $this->get_instance_ul($order);

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

    public function get_api_structure($order_info)
    {
        return (isset($order_info['payment_recurring']) && $order_info['payment_recurring'] > 0) ? 'recurring_data' : 'payment_data';
    }

    public function capture_payment($order, $order_info)
    {
        $api_structure = $this->get_api_structure($order_info);
        $order_total = $order['total'];

        if ($order_total <= 0) {
            return $this->ajaxResponse(false, 'Order total amount must be more than 0 to capture the payment');
        }

        $initial_order_amount = $order_info['initial_amount'];
        if ($order_total > (float)$initial_order_amount) {
            return $this->ajaxResponse(false, 'Order total amount must not exceed the blocked amount to capture the payment');
        }

        $api_request = $this->get_api_request_for_update($order_info, $api_structure, self::COMPLETE_STATUS_TO, $order_total);
        $response = ULRestClient::patch($this->ul->getApiUrl(), $api_request);
        return $this->parse_response_code($response);
    }

    public function cancel_payment($order_info)
    {
        $api_structure = $this->get_api_structure($order_info);
        $api_request = $this->get_api_request_for_update($order_info, $api_structure, self::REVERSE_STATUS_TO, 0);
        $response = ULRestClient::patch($this->ul->getApiUrl(), $api_request);
        return $this->parse_response_code($response);
    }

    public function parse_response_code($response)
    {
        $success = (isset($response['status']) && $response['status'] == 200);
        $message = isset($response['response']['message']) ? $response['response']['message'] : '';
        return ($success) ? $this->ajaxResponse(true) : $this->ajaxResponse(false, $message);
    }

    private function get_api_request_for_update($order_info, $api_structure, $status_to, $amount)
    {
        $transaction_id = $order_info['transaction_id'];
        $is_recurring = $order_info['payment_recurring'] > 0;

        $get_access_token = $this->ul->get_access_token();

        $data = [
            'status_to' => $status_to
        ];
        if (
            (self::COMPLETE_STATUS_TO === $status_to) && ($order_info['initial_amount'] > $amount)) {
            $data['amount'] = $amount;
        }
        $uri = ($is_recurring) ? '/installments/' : '/payments/';
        return [
            "uri" => $uri . $transaction_id,
            "params" => [
                "access_token" => $get_access_token,
            ],
            "headers" => [
                "x-tracking-id" => "platform:v1-whitelabel,type:OpenCart3",
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
