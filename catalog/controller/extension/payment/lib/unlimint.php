<?php

$GLOBALS["LIB_LOCATION"] = __DIR__;

require_once __DIR__ . '/ul_rest_client.php';
require_once __DIR__ . '/unlimint_exception.php';

class Unlimint
{
    public const VERSION = "3.0";
    private $client_id;
    private $client_secret;
    private bool $sandbox = false;
    private string $api_url;
    private Log $log;
    private DB $db;

    /**
     * @param string $prefix
     * @param Config $config
     * @return static
     * @throws UnlimintException
     */
    public static function getInstance(string $prefix, Config $config): self
    {
        return new self(
            $config->get('payment_ul_' . $prefix . '_terminal_code'),
            $config->get('payment_ul_' . $prefix . '_terminal_password'),
            $config->get('payment_ul_' . $prefix . '_production')
        );
    }

    public function setOrderData($order_info, $payment_info, $form_data): void
    {
        $sql = "SELECT transaction_id FROM " . DB_PREFIX . "ul_orders WHERE order_id = " . $order_info['order_id'] . " limit 1";

        $query = $this->db->query($sql);
        if (!empty($query->row)) {
            return;
        }

        $type = isset($payment_info['response']['payment_data']) ? 'payment_data' : 'recurring_data';
        $recurring = ($type === 'recurring_data') ? (int)$form_data['recurring_data']["payments"] : 0;
        $payment_id = $payment_info['response'][$type]['id'];
        $total = $order_info['total'];

        $query = 'INSERT INTO ' . DB_PREFIX . 'ul_orders 
        SET 
        order_id=' . ((int)$order_info['order_id']) . ', 
        transaction_id=' . $payment_id . ', 
        initial_amount=' . $total . ', 
        payment_recurring=' . $recurring;

        $this->db->query($query);
    }

    public function completeOrderData($order_id, $new_amount, $new_payment_id): void
    {
        $this->db->query('UPDATE ' . DB_PREFIX . 'ul_orders 
        SET 
        initial_amount=' . $new_amount . ',
        transaction_id=' . $new_payment_id . ',
        is_complete=1
        WHERE 
        is_complete=0 AND order_id=' . $order_id);
    }

    public function getOrderInfo($order_id): array
    {
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'ul_orders WHERE order_id = ' . $order_id . ' limit 1';

        $query = $this->db->query($sql);

        return (!empty($query->row) && !empty($query->row['transaction_id'])) ? $query->row : [];
    }

    /**
     * @throws UnlimintException
     */
    public function __construct()
    {
        $i = func_num_args();

        if ($i !== 3) {
            throw new UnlimintException("Invalid arguments. Use TERMINAL_CODE, TERMINAL_PASSWORD and PRODUCTION_MODE");
        }

        $this->client_id = func_get_arg(0);
        $this->client_secret = func_get_arg(1);

        $this->sandbox_mode(!func_get_arg(2));
        $this->api_url = $this->getApiUrl();
    }

    public function setLog($log): self
    {
        $this->log = $log;

        return $this;
    }

    public function setDb($db): self
    {
        $this->db = $db;

        return $this;
    }

    public function writeLog($text): void
    {
        if (isset($this->log)) {
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

    public function getApiUrl(): string
    {
        $url = ($this->sandbox_mode()) ? 'https://sandbox.cardpay.com/api' : 'https://www.cardpay.com/api';

        return ($url);
    }

    /**
     * Get Terminal Password for API use
     * @throws UnlimintException|JsonException
     */
    public function getAccessToken()
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
            $this->writeLog(__FUNCTION__ . ' - Request Data: ' . print_r($app_client_values, true));
            $this->writeLog(__FUNCTION__ . ' - Response: ' . print_r($access_data, true));

            throw new UnlimintException(print_r($access_data['response'], true), $access_data['status']);
        }

        $access_data_response = $access_data['response'];

        return $access_data_response['access_token'];
    }

    /**
     * Get information for specific authorized payment
     * @param string id
     * @return array(json)
     * @throws UnlimintException|JsonException
     */
    public function getAuthorizedPayment($id): array
    {
        $request = [
            "uri" => "/authorized_payments/$id",
            "params" => [
                "access_token" => $this->getAccessToken(),
            ],
        ];

        return ULRestClient::get($this->api_url, $request);
    }

    /**
     * Create a payment
     * @param array $payment
     * @return array(json)
     * @throws UnlimintException|JsonException
     */
    public function create_payment(array $payment): array
    {
        $get_access_token = $this->getAccessToken();

        $request = [
            "uri" => "/payments",
            "params" => [
                "access_token" => $get_access_token,
            ],
            "data" => $payment,
        ];

        if (!in_array($payment['payment_method'], ['BOLETO', 'PIX'])) {
            $paymentMasked = $payment;
            $paymentMasked['card_account'] = [
                'card' => [
                    'pan' => substr($paymentMasked['card_account']['card']['pan'], 0, 6) . '...' . substr($paymentMasked['card_account']['card']['pan'], -4),
                    'security_code' => '...'
                ]
            ];
            $this->writeLog(__FUNCTION__ . ' - Request: ' . print_r($paymentMasked, true));
        }

        $result = ULRestClient::post($this->api_url, $request);

        $this->writeLog(__FUNCTION__ . ' - Result: ' . print_r($result, true));

        return $result;
    }

    /**
     * @throws UnlimintException|JsonException
     */
    public function getPayment($payment_id): ?array
    {
        $get_access_token = $this->getAccessToken();

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
     * @param request
     * @param null $params
     * @param bool $authenticate
     * @return array|null
     * @throws UnlimintException|JsonException
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
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        return ULRestClient::get($this->api_url, $request);
    }

    /**
     * Generic resource post
     * @param request
     * @param null $data
     * @param null $params
     * @return array|null
     * @throws UnlimintException|JsonException
     */
    public function post($request, $data = null, $params = null): ?array
    {
        $request = $this->getRequest($request, $data, $params);

        return ULRestClient::post($this->api_url, $request);
    }

    /**
     * Generic resource put
     * @param request
     * @param null $data
     * @param null $params
     * @return array|null
     * @throws UnlimintException|JsonException
     */
    public function put($request, $data = null, $params = null): ?array
    {
        $request = $this->getRequest($request, $data, $params);

        return ULRestClient::put($this->api_url, $request);
    }

    /**
     * Generic resource delete
     * @param request
     * @param null $params
     * @return array|null
     * @throws UnlimintException|JsonException
     */
    public function delete($request, $params = null): ?array
    {
        if (is_string($request)) {
            $request = array(
                "uri" => $request,
                "params" => $params,
            );
        }

        $request["params"] = isset($request["params"]) && is_array($request["params"]) ? $request["params"] : [];
        if (!isset($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        return ULRestClient::delete($this->api_url, $request);
    }

    /**
     * @param $request
     * @param $data
     * @param $params
     * @return mixed
     * @throws JsonException
     * @throws UnlimintException
     */
    private function getRequest($request, $data, $params): mixed
    {
        if (is_string($request)) {
            $request = [
                "uri" => $request,
                "data" => $data,
                "params" => $params,
            ];
        }

        $request["params"] = isset($request["params"]) && is_array($request["params"]) ? $request["params"] : [];
        if (!isset($request["authenticate"]) || $request["authenticate"] !== false) {
            $request["params"]["access_token"] = $this->getAccessToken();
        }

        return $request;
    }
}
