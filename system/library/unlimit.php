<?php

namespace Unlimit;

$GLOBALS["LIB_LOCATION"] = __DIR__;

require_once __DIR__ . '/ul_rest_client.php';
require_once __DIR__ . '/unlimit_exception.php';

use Opencart\System\Engine\Config;

class Unlimit
{
    public const VERSION = "3.0";
    private $client_id;
    private $client_secret;
    private $sandbox = false;
    private $api_url;
    private $log;
    private $db;
    public $log_to_file;

    /**
     * @param string $prefix
     * @param Config $config
     *
     * @return static
     * @throws UnlimitException
     */
    public static function get_instance(string $prefix, Config $config): self
    {
        return new self(
            $config->get('payment_ul_' . $prefix . '_terminal_code'),
            $config->get('payment_ul_' . $prefix . '_terminal_password'),
            $config->get('payment_ul_' . $prefix . '_production'),
            !$prefix || $config->get('payment_ul_' . $prefix . '_log_to_file'),
        );
    }

    public function set_order_data(
        $order_info,
        $payment_info,
        $form_data,
        $access_mode = ULUtil::ACCESS_MODE_GATEWAY
    ): void {
        $sql = "SELECT transaction_id FROM " . DB_PREFIX . "ul_orders WHERE order_id = " . $order_info['order_id'] . " limit 1";

        $query = $this->db->query($sql);
        if (!empty($query->row)) {
            return;
        }

        $recurring = 0;
        $payment_id = "";
        if ($access_mode == ULUtil::ACCESS_MODE_GATEWAY) {
            $type = isset($payment_info['response']['payment_data']) ? 'payment_data' : 'recurring_data';
            $recurring = ($type === 'recurring_data') ? (int)$form_data['recurring_data']["payments"] : 0;
            $payment_id = $payment_info['response'][$type]['id'];
        }
        $total = $order_info['total'];

        $query = 'INSERT INTO ' . DB_PREFIX . 'ul_orders
        SET
        order_id=' . ((int)$order_info['order_id']) . ',
        transaction_id="' . $payment_id . '",
        telephone_number="' . $order_info['telephone'] . '",
        initial_amount=' . $total . ',
        payment_recurring=' . $recurring . ',';

        if (isset($form_data["payment_data"]["installment_type"])) {
            $query .= 'installment_type="' . $form_data["payment_data"]["installment_type"] . '",';
        }

        if (isset($form_data["payment_data"]["installments"])) {
            $query .= 'count_installment_type="' . $form_data["payment_data"]["installments"] . '"';
        }

        $this->db->query(rtrim($query, ','));
    }

    public function complete_order_data($order_id, $new_payment_id): void
    {
        $this->db->query(
            'UPDATE ' . DB_PREFIX . 'ul_orders
        SET
        transaction_id=' . $new_payment_id . ',
        is_complete=1
        WHERE
        is_complete=0 AND order_id=' . $order_id
        );
    }

    public function get_order_info($order_id): array
    {
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'ul_orders WHERE order_id = ' . $order_id . ' limit 1';

        $query = $this->db->query($sql);

        return (!empty($query->row) && !empty($query->row['transaction_id'])) ? $query->row : [];
    }

    /**
     * @throws UnlimitException
     */
    public function __construct()
    {
        $i = func_num_args();

        if ($i !== 4) {
            throw new UnlimitException(
                "Invalid arguments. Use TERMINAL_CODE, TERMINAL_PASSWORD, PRODUCTION_MODE AND LOG_TO_FILE"
            );
        }

        $this->client_id = func_get_arg(0);
        $this->client_secret = func_get_arg(1);

        $this->sandbox_mode(!func_get_arg(2));
        $this->api_url = $this->get_api_url();
        $this->log_to_file = func_get_arg(3);
    }

    public function set_log($log): self
    {
        $this->log = $log;

        return $this;
    }

    public function set_db($db): self
    {
        $this->db = $db;

        return $this;
    }

    public function write_log($text): void
    {
        if (
            isset($this->log) &&
            $this->log_to_file
        ) {
            $this->log->write($text);
        }
    }

    public function sandbox_mode($enable = null): bool
    {
        if (!is_null($enable)) {
            $this->sandbox = $enable === true;
        }

        return $this->sandbox;
    }

    public function get_api_url(): string
    {
        $url = ($this->sandbox_mode()) ? 'https://sandbox.cardpay.com/api' : 'https://www.cardpay.com/api';

        return ($url);
    }

    /**
     * Get Terminal Password for API use
     * @throws UnlimitException|JsonException
     */
    public function get_access_token()
    {
        $app_client_values = [
            'terminal_code' => $this->client_id,
            'password' => $this->client_secret,
            'grant_type' => 'password',
        ];

        $access_data = ULRestClient::post($this->api_url, [
            "uri" => "/auth/token",
            "data" => $app_client_values,
            "headers" => [
                "content-type" => "application/x-www-form-urlencoded",
            ],
        ]);

        if ((int)$access_data["status"] !== 200) {
            $this->write_log(__FUNCTION__ . ' - Request Data: ' . print_r($app_client_values, true));
            $this->write_log(__FUNCTION__ . ' - Response: ' . print_r($access_data, true));

            throw new UnlimitException(print_r($access_data['response'], true), $access_data['status']);
        }

        $access_data_response = $access_data['response'];

        return $access_data_response['access_token'];
    }

    /**
     * Get information for specific authorized payment
     *
     * @param string id
     *
     * @return array(json)
     * @throws UnlimitException|JsonException
     */
    public function get_authorized_payment($id): array
    {
        $request = [
            "uri" => "/authorized_payments/$id",
            "params" => [
                "access_token" => $this->get_access_token(),
            ],
        ];

        return ULRestClient::get($this->api_url, $request);
    }

    /**
     * Create a payment
     *
     * @param array $payment
     *
     * @return array(json)
     * @throws UnlimitException|JsonException
     */
    public function create_payment(array $payment): array
    {
        $get_access_token = $this->get_access_token();

        $request = [
            "uri" => "/payments",
            "headers" => [
                "Authorization" => "Bearer " . $get_access_token,
            ],
            "data" => $payment,
        ];

        if ($payment['payment_method'] === 'BANKCARD' &&
            (!empty($payment['card_account']['card']['pan']) ||
                !empty($payment['card_account']['card']['holder']) ||
                !empty($payment['card_account']['card']['expiration']) ||
                !empty($payment['card_account']['card']['security_code']))) {
            $paymentMasked = $payment;
            $paymentMasked['card_account']['card']['pan'] = substr_replace(
                $paymentMasked['card_account']['card']['pan'],
                '...',
                6,
                -4
            );
            $paymentMasked['card_account']['card']['security_code'] = '...';

            $this->write_log(__FUNCTION__ . ' - Payment masked: ' . print_r($paymentMasked, true));
        } else {
            $this->write_log(__FUNCTION__ . ' - Payment: ' . print_r($payment, true));
        }

        $result = ULRestClient::post($this->api_url, $request);

        $this->write_log(__FUNCTION__ . ' - Result: ' . print_r($result, true));

        return $result;
    }

    /**
     * @throws UnlimitException|JsonException
     */
    public function get_payment($payment_id): ?array
    {
        $get_access_token = $this->get_access_token();

        $request = [
            "uri" => "/payments/" . $payment_id,
            "params" => [
                "access_token" => $get_access_token,
            ]
        ];

        return ULRestClient::get($this->api_url, $request);
    }

    /* Generic resource call methods */
    /**
     * Generic resource get
     *
     * @param request
     * @param null $params
     * @param bool $authenticate
     *
     * @return array|null
     * @throws UnlimitException|JsonException
     */
    public function get($request_uri = '', $params = null, $authenticate = true): ?array
    {
        $request = (!empty($request_uri)) ? [
            "uri" => $request_uri,
            "params" => $params,
            "authenticate" => $authenticate,
        ] : [];

        $request["params"] = isset($request["params"]) && is_array($request["params"]) ? $request["params"] : [];
        if (isset($authenticate) && $authenticate) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        return ULRestClient::get($this->api_url, $request);
    }

    /**
     * Generic resource post
     *
     * @param request
     * @param null $data
     * @param null $params
     *
     * @return array|null
     * @throws UnlimitException|JsonException
     */
    public function post($request, $data = null, $params = null): ?array
    {
        $request = $this->get_request($request, $data, $params);

        return ULRestClient::post($this->api_url, $request);
    }

    /**
     * Generic resource put
     *
     * @param request
     * @param null $data
     * @param null $params
     *
     * @return array|null
     * @throws UnlimitException|JsonException
     */
    public function put($request, $data = null, $params = null): ?array
    {
        $request = $this->get_request($request, $data, $params);

        return ULRestClient::put($this->api_url, $request);
    }

    /**
     * Generic resource delete
     *
     * @param request
     * @param null $params
     *
     * @return array|null
     * @throws UnlimitException|JsonException
     */
    public function delete($request, $params = null): ?array
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params,
            );
        }

        return ULRestClient::delete($this->api_url, $this->get_request_access_token($request));
    }

    /**
     * @param $request
     * @param $data
     * @param $params
     *
     * @return mixed
     * @throws JsonException
     * @throws UnlimitException
     */
    private function get_request($request, $data, $params)
    {
        if (is_string($request)) {
            $request = [
                "uri" => $request,
                "data" => $data,
                "params" => $params,
            ];
        }

        return $this->get_request_access_token($request);
    }

    /**
     * @param $request
     *
     * @return mixed
     * @throws JsonException
     * @throws UnlimitException
     */
    protected function get_request_access_token($request)
    {
        $request["params"] = isset($request["params"]) && is_array($request["params"]) ? $request["params"] : [];
        if (!isset($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->get_access_token();
        }

        return $request;
    }
}
