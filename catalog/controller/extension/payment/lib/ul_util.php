<?php

class ULOpencartUtil
{
    public const TRANSACTION_ID_PREFIX = '- Payment ID:';

    private $platformVersion = "3.0";
    private $moduleVersion = "1.0.3";
    private $log;
    private $config;
    /**
     * @var UL $ul
     */
    private $ul;

    private $ul_order_status_id = [
        "pending" => 1,
        "new" => 1,
        "in_process" => 1,
        "completed" => 5,
        "authorized" => 5,
        "chargeback_resolved" => 5,
        "cancelled" => 7,
        "voided" => 7,
        "charged_back" => 7,
        "rejected" => 10,
        "declined" => 10,
        "terminated" => 10,
        "refunded" => 11
    ];

    public function setLog($log)
    {
        $this->log = $log;
    }

    public function setUl($ul)
    {
        $this->ul = $ul;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function writeLog($text)
    {
        if (isset($this->log)) {
            $this->log->write($text);
        }
    }

    public function getStatusId($status, $prefix)
    {
        $status = strtolower($status);
        $status = preg_replace('#[^a-z_]+#s', '', $status);
        if (!isset($this->ul_order_status_id[$status])) {
            return $this->ul_order_status_id['pending'];
        }
        $status_id = $this->config->get('payment_ul_' . $prefix . '_' . $status);

        if (!empty($status_id)) {
            return $status_id;
        }

        return ($this->ul_order_status_id['pending']);
    }

    public function createAnalytics($resultModules, $token, $customerEmail, $userLogged)
    {
        return array(
            'publicKey' => "",
            'token' => $token,
            'platform' => "Opencart",
            'platformVersion' => $this->platformVersion,
            'moduleVersion' => $this->moduleVersion,
            'payerEmail' => $customerEmail,
            'userLogged' => $userLogged,
            'installedModules' => implode(', ', $resultModules),
            'additionalInfo' => ""
        );
    }

    public function updateOrder($payment, $model, $db, $prefix)
    {
        try {
            $type = isset($payment['payment_data']) ? 'payment_data' : 'recurring_data';

            $order_id = $payment['merchant_order']['id'] ?? 0;
            $result_order_status = $payment[$type]['status'] ?? '';

            $actualize = true;

            if (empty($result_order_status)) {
                $actualize = false;
                $db = null;
            }

            if (isset($db) && $db != null) {
                $status_id = $this->getStatusId($result_order_status, $prefix);
                $sql = "SELECT max(order_history_id) as order_history FROM " . DB_PREFIX . "order_history WHERE order_id = " . $order_id . " and order_status_id = " . $status_id;

                $query = $db->query($sql);

                if (isset($query->rows) && $query->rows[0]['order_history'] != null) {
                    $actualize = true;
                }
            }

            if ($actualize) {
                $model->addOrderHistory($order_id, $status_id, date('d/m/Y h:i') . ' - Callback Status: ' . $result_order_status);
            }
        } catch (Exception $e) {
            $this->writeLog(__FUNCTION__ . ' - ' . print_r($e, true));
            error_log("error for updateOrder - " . $e);
        }
    }

    public function updateOrderPayment($order_info, $model, $statusId, $payment_id)
    {
        try {
            $model->addOrderHistory(
                $order_info['order_id'],
                $statusId,
                date('d/m/Y h:i') . ' '
                . self::TRANSACTION_ID_PREFIX
                . $payment_id
            );
        } catch (Exception $e) {
            error_log("error for updateOrder - " . $e);
        }
    }

    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    public function createApiRequest($orderId, $order_info, $capture = true)
    {
        switch ($order_info['payment_code']) {
            case 'ul_card':
                $prefix = 'card';
                break;
            case 'ul_ticket':
                $prefix = 'ticket';
                break;
            default:
                $prefix = '';
        }
        $id = uniqid('', true);
        $customerId = uniqid('', true);

        $total_price = round($order_info['total'] * $order_info['currency_value'], 2);
        $notification_url = $order_info['store_url'] . 'index.php?route=extension/payment/ul_' . $prefix . '/callback';
        $data = [
            'request' => [
                'id' => $id,
                'time' => date("Y-m-d\TH:i:s\Z"),
            ],
            'merchant_order' => [
                'id' => $orderId,
                "description" => $order_info['store_name'] . ' - ' . $order_info['order_id'],
            ],
            'customer' => [
                'id' => $customerId,
                'email' => $order_info['email'],
                'phone' => $order_info['telephone'],
            ],
            'return_urls' => [
                "cancel_url" => $notification_url . '&action=cancel&orderId=' . $orderId,
                "decline_url" => $notification_url . '&action=decline&orderId=' . $orderId,
                "inprocess_url" => $notification_url . '&action=inprocess&orderId=' . $orderId,
                "success_url" => $notification_url . '&action=success&orderId=' . $orderId
            ],
            'payment_data' => [
                "amount" => $total_price,
                "currency" => $order_info['currency_code'],
            ],
        ];

        $shipping_address = [
            'country' => 'BR',
            'state' => $order_info['payment_zone'],
            'zip' => $order_info['shipping_postcode'],
            'city' => $order_info['payment_city'],
            'phone' => $order_info['telephone'],
            'addr_line_1' => $order_info['payment_address_1'],
            'addr_line_2' => $order_info['payment_address_2'],
        ];

        if (empty($shipping_address['zip'])) {
            $shipping_address['zip'] = $order_info['post_code'] ?? '';
        }

        if (!empty($order_info['shipping_code'])) {
            $data['merchant_order']['shipping_address'] = $shipping_address;
        } elseif (!empty($order_info['post_code'])) {
            $data['merchant_order']['shipping_address']['zip'] = $order_info['post_code'];
        }

        if (!$capture) {
            $data['payment_data']['preauth'] = true;
        }

        return $data;
    }

    public function processPayment($data, $order_info, $statusId, $model_order, $instance_ul)
    {
        $ret = $instance_ul->create_payment($data);

        if (($ret["status"] === 200 || $ret["status"] === 201) && isset($ret['response'])) {
            $type = isset($ret['response']['payment_data']) ? 'payment_data' : 'recurring_data';

            $redirectUrl = $ret['response']['redirect_url'];
            $paymentId = $ret['response'][$type]['id'];

            $this->updateOrderPayment($order_info, $model_order, $statusId, $paymentId);
            $this->ul->setOrderData($order_info, $ret, $data);


            return ($redirectUrl);
        }

        return (false);
    }
}
