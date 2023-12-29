<?php

namespace Unlimit;

require_once __DIR__ . '/unlimit_order_info.php';

use Opencart\System\Engine\Config;
use Opencart\System\Engine\Proxy;
use Opencart\System\Library\DB;

class ULUtil
{
    public const TRANSACTION_ID_PREFIX = '- Payment ID:';
    public const ACCESS_MODE_PP = '1';
    public const ACCESS_MODE_GATEWAY = '0';

    private const DATE_FORMAT = 'd/m/Y h:i';

    private $platformVersion = "3.0";
    private $moduleVersion = "1.0.3";
    private $log;
    private $config;
    /**
     * @var Unlimit $ul
     */
    private $ul;

    private $ul_order_status_id = [
        "pending"             => 1,
        "new"                 => 1,
        "in_process"          => 1,
        "completed"           => 5,
        "authorized"          => 2,
        "chargeback_resolved" => 5,
        "cancelled"           => 7,
        "voided"              => 7,
        "charged_back"        => 13,
        "rejected"            => 10,
        "declined"            => 10,
        "terminated"          => 10,
        "refunded"            => 11
    ];

    private const FAILED_STATUSES = [
        "CANCELLED",
        "REJECTED",
        "DECLINED",
        "TERMINATED"
    ];

    protected $get_prefix;

    public $error = '';

    public function setLog($log): self
    {
        $this->log = $log;

        return $this;
    }

    public function setUl(Unlimit $ul): self
    {
        $this->ul = $ul;

        return $this;
    }

    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function writeLog($text): void
    {
        if (isset($this->log)) {
            $this->log->write($text);
        }
    }

    public function getStatusId($status)
    {
        $status = strtolower($status);
        $status = preg_replace('#[^a-z_]+#', '', $status);
        if ( ! isset($this->ul_order_status_id[$status])) {
            return $this->ul_order_status_id['pending'];
        }

        $status_id = $this->ul_order_status_id[$status];
        if ( ! empty($status_id)) {
            return $status_id;
        }

        return ($this->ul_order_status_id['pending']);
    }

    public function createAnalytics($resultModules, $token, $customerEmail, $userLogged): array
    {
        return [
            'publicKey'        => "",
            'token'            => $token,
            'platform'         => "Opencart",
            'platformVersion'  => $this->platformVersion,
            'moduleVersion'    => $this->moduleVersion,
            'payerEmail'       => $customerEmail,
            'userLogged'       => $userLogged,
            'installedModules' => implode(', ', $resultModules),
            'additionalInfo'   => ""
        ];
    }

    /**
     * @param  string  $type
     * @param  array  $payment
     * @param  DB  $db
     */
    public function completeOrderData(string $type, array $payment, DB $db): void
    {
        $order_id                = (int)$payment['merchant_order']['id'];
        $res                     = $db->query('SELECT * FROM ' . DB_PREFIX . 'ul_orders
                    WHERE order_id=' . ($order_id) . '
                    LIMIT 1');
        $order_info              = $res->row ?? [];
        $received_transaction_id = $payment[$type]['id'];
        $received_amount         = $payment[$type]['amount'];

        if (
            empty($order_info) ||
            $order_info['is_complete'] ||
            (
                (int)$received_transaction_id === (int)$order_info['transaction_id'] &&
                (float)$received_amount === (float)$order_info['initial_amount']
            )
        ) {
            return;
        }

        $this->ul->completeOrderData($order_id, $received_amount, $received_transaction_id);
    }

    /**
     * @param  string  $result_order_status
     * @param  string  $transaction_id
     * @param ?array  $order_info
     *
     * @return bool
     */
    protected function isDoubledPaymentFailed(
        string $result_order_status,
        string $transaction_id,
        ?array $order_info
    ): bool {
        return
            in_array($result_order_status, self::FAILED_STATUSES) &&
            ! empty($transaction_id) &&
            ! empty($order_info) &&
            isset($order_info['transaction_id']) &&
            $order_info['transaction_id'] !== $transaction_id;
    }

    /**
     * @param  array  $callback_data
     * @param  Proxy|ModelCheckoutOrder  $model
     * @param  DB  $db
     *
     * @return bool
     * @throws JsonException
     */
    public function updateOrder(
        array $callback_data,
        Proxy $model,
        DB $db,
    ): bool {
        $this->ul->writeLog('Callback: ' . json_encode($callback_data, JSON_THROW_ON_ERROR));
        $order_id            = $callback_data['merchant_order']['id'] ?? 0;
        $order               = ($order_id) ? $model->getOrder((int)$order_id) : [];
        $type                = 'payment_data';
        $result_order_status = $callback_data[$type]['status'] ?? '';
        if ( ! $order_id || empty($order)) {
            return false;
        }

        if (in_array($result_order_status, ['COMPLETED', 'AUTHORIZED'])) {
            $this->completeOrderData($type, $callback_data, $db);
        }

        $result = true;

        try {
            $status_id  = $this->getStatusId($result_order_status);
            $order_info = $this->ul->getOrderInfo($order_id);

            if ($this->isDoubledPaymentFailed(
                $result_order_status,
                $callback_data[$type]['id'] ?? '',
                $order_info)
            ) {
                $status_id = $order['order_status_id'];
            }

            $query = $db->query('SELECT max(order_history_id) AS order_history
            FROM ' . DB_PREFIX . 'order_history
            WHERE order_id = ' . $order_id . '
            AND order_status_id = ' . $status_id);

            if ( ! isset($query->rows) || empty($query->rows[0]['order_history']) || 'CHARGEBACK_RESOLVED' === $result_order_status) {
                $model->addHistory(
                    (int)$order_id,
                    $status_id,
                    sprintf('%s - Status: %s - ID: %s',
                        date(self::DATE_FORMAT), $result_order_status, $callback_data[$type]['id'] ?? ''
                    )
                );
            }
        } catch (Exception $e) {
            $this->writeLog(__FUNCTION__ . ' - ' . print_r($e, true));
            error_log("error for updateOrder - " . $e);
            $result = false;
        }

        return $result;
    }

    public function updateOrderPayment($order_info, $model, $statusId, $payment_id): void
    {
        try {
            $model->addHistory(
                $order_info['order_id'],
                $statusId,
                date(self::DATE_FORMAT) . ' '
                . ($payment_id ? self::TRANSACTION_ID_PREFIX . $payment_id : "")
            );
        } catch (Exception $e) {
            error_log("error for updateOrder - " . $e);
        }
    }

    public function getModuleVersion(): string
    {
        return $this->moduleVersion;
    }

    public function createApiRequest($orderId, $order_info): array
    {
        if ( ! $orderId) {
            throw  new \Exception('Invalid Request');
        }
        $this->get_prefix = new UnlimitOrderInfo();
        $id               = uniqid('', true);
        $customerId       = uniqid('', true);

        $total_price      = round($order_info['total'] * $order_info['currency_value'], 2);
        $notification_url = $order_info['store_url'] .
                            'extension/unlimit/callback.php?route=extension/unlimit/payment/ul_general.callback';

        $data = [
            'request'        => [
                'id'   => $id,
                'time' => date("Y-m-d\TH:i:s\Z"),
            ],
            'merchant_order' => [
                'id'          => $orderId,
                "description" => $order_info['store_name'] . ' - ' . $order_info['order_id'],
            ],
            'customer'       => [
                'id'    => $customerId,
                'email' => $order_info['email'],
                'phone' => $order_info['telephone'] ?
                    preg_replace('/[^\d]/', '', $order_info['telephone']) :
                    "",
            ],
            'return_urls'    => [
                "cancel_url"    => $notification_url . '&action=cancel&orderId=' . $orderId,
                "decline_url"   => $notification_url . '&action=decline&orderId=' . $orderId,
                "inprocess_url" => $notification_url . '&action=inprocess&orderId=' . $orderId,
                "success_url"   => $notification_url . '&action=success&orderId=' . $orderId
            ],
            'payment_data'   => [
                "amount"   => $total_price,
                "currency" => $order_info['currency_code'],
            ],
        ];

        $shipping_address = [
            'country'     => $order_info['shipping_iso_code_2'],
            'state'       => $order_info['shipping_zone'],
            'city'        => $order_info['shipping_city'],
            'phone'       => $order_info['telephone'] ?
                preg_replace('/[^\d]/', '', $order_info['telephone']) :
                "",
            'addr_line_1' => $order_info['shipping_address_1'],
            'addr_line_2' => $order_info['shipping_address_2'],
        ];

        $items = [];
        foreach ($order_info['orderProducts'] as $orderProduct) {
            $items[] = [
                'name'        => $orderProduct['name'],
                'description' => 'Item #' . $orderProduct['product_id'],
                'count'       => $orderProduct['quantity'],
                'price'       => number_format($orderProduct['price'], 2, '.', '')
            ];
        }
        $data['merchant_order']['items'] = $items;

        if ( ! empty($order_info['shipping_postcode'])) {
            $shipping_address['zip'] = $order_info['shipping_postcode'];
        } elseif ( ! empty($order_info['post_code'])) {
            $shipping_address['zip'] = $order_info['post_code'];
        } else {
            $shipping_address['zip'] = '';
        }

        if (isset($order_info['shipping_method']['code']) && ! empty($order_info['shipping_method']['code'])) {
            $data['merchant_order']['shipping_address'] = $shipping_address;
        } elseif ( ! empty($order_info['post_code'])) {
            $data['merchant_order']['shipping_address']['zip'] = $order_info['post_code'];
        }

        return $data;
    }

    public function processPayment(
        $data,
        $order_info,
        $status_id,
        $model_order,
        $instance_ul,
        $access_mode = self::ACCESS_MODE_GATEWAY
    ) {
        $result = $instance_ul->create_payment($data);

        if (isset($result["status"], $result['response']) && in_array((int)$result["status"], [200, 201], true)) {
            $redirectUrl = $result['response']['redirect_url'];
            $paymentId   = null;
            if ($access_mode == self::ACCESS_MODE_GATEWAY) {
                $paymentId = $result['response']['payment_data']['id'];
            }
            $this->updateOrderPayment($order_info, $model_order, $status_id, $paymentId);

            $this->ul->setOrderData($order_info, $result, $data, $access_mode);

            return $redirectUrl;
        }

        if (isset($result['response']['message'])) {
            $this->error = $result['response']['message'];
        }

        return false;
    }
}
