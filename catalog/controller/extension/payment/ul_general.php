<?php

use Cart\Customer;

require_once __DIR__ . "/lib/unlimint.php";
require_once __DIR__ . "/lib/ul_util.php";
require_once __DIR__ . "/lib/ul_cart.php";
require_once __DIR__ . "/lib/unlimint_order_info.php";
require_once __DIR__ . "/ul_alt_gateway.php";
require_once __DIR__ . "/ul_card.php";
require_once __DIR__ . "/ul_ticket.php";
require_once __DIR__ . "/ul_pix.php";

/**
 * @property Request $request
 * @property Unlimint $ul
 * @property Config $config
 * @property Response $response
 * @property Session $session
 * @property DB $db
 * @property Cart\Cart $cart
 * @property Log $log
 * @property Loader $load
 * @property Customer $customer
 * @property Url $url
 * @property Language $language
 * @property ModelCheckoutOrder $model_order
 * @property ModelCheckoutOrder $model_checkout_order
 * @property ULOpencartUtil $ul_util
 */
class ControllerExtensionPaymentULGeneral extends Controller
{
    public const CHECKOUT_ORDER = 'checkout/order';
    public const CHECKOUT_CHECKOUT = 'checkout/checkout';
    public const CHECKOUT_SUCCESS = 'checkout/success';
    public const UL_PREFIX = '';

    public UnlimintOrderInfo $get_prefix;
    private Unlimint $ul;
    protected string $orderId;
    protected Unlimint $instance_ul;
    protected array $orderInfo;
    protected array $data;
    protected int $statusId;
    protected float $amount;
    protected ULCart $ul_cart;

    /**
     * @param $registry
     * @throws UnlimintException|Exception
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('extension/payment/ul_general.php');
        $this->load->model(self::CHECKOUT_ORDER);
        $this->orderId = $this->session->data['order_id'] ?? '';
        $this->instance_ul = $this->get_instance_ul();
        $this->model_order = $this->model_checkout_order;
        $orderInfo = $this->model_checkout_order->getOrder($this->orderId);
        $this->orderInfo = (!$orderInfo) ? [] : $orderInfo;
        $this->statusId = 1;
        $this->amount = ($this->orderInfo) ?
            round($this->orderInfo['total'] * $this->orderInfo['currency_value'], 2) : 0;

        $this->ul_cart = (new ULCart())
            ->setDb($this->db)
            ->setCart($this->cart)
            ->setCustomer($this->customer)
            ->setSession($this->session);
    }

    /**
     * @return ULOpencartUtil
     * @throws UnlimintException
     */
    public function get_instance_ul_util(): ULOpencartUtil
    {
        if (is_null($this->ul_util)) {
            $this->ul_util = (new ULOpencartUtil())
                ->setConfig($this->config)
                ->setUl($this->get_instance_ul())
                ->setLog($this->log);
        }

        return $this->ul_util;
    }

    /**
     * @return Unlimint
     * @throws UnlimintException
     */
    public function get_instance_ul(): Unlimint
    {
        return $this->ul ?? $this->ul = (Unlimint::getInstance(static::UL_PREFIX, $this->config))
                ->setLog($this->log)
                ->setDb($this->db);
    }

    /**
     * @throws JsonException
     */
    public function getPaymentStatus(): void
    {
        $this->load->language('payment/ul_' . static::UL_PREFIX);
        $request_type = isset($this->request->get['request_type']) ? (string)$this->request->get['request_type'] : "";
        $status = (string)$this->request->get['status'];
        if ($request_type) {
            $status = $request_type === "token" ? 'T' . $status : 'S' . $status;
        }

        $message = $this->language->get($status);

        echo json_encode(['message' => $message], JSON_THROW_ON_ERROR);
    }

    /**
     * CallBack IPN : eg: https://<domain>/index.php?route=extension/payment/ul_general/callback
     * @throws JsonException|UnlimintException
     */
    public function callback(): void
    {
        $this->get_instance_ul_util();

        $action = $this->request->get['action'] ?? '';

        switch ($action) {
            case 'success' :
            case 'inproccess' :
                $this->ul_cart->clearCurrentBackup();
                $this->response->redirect($this->url->link(self::CHECKOUT_SUCCESS));
                break;
            case 'decline' :
            case 'cancel' :
                $this->ul_cart->restoreOrderedProducts();
                $this->response->redirect($this->url->link(self::CHECKOUT_CHECKOUT));
                break;
            case '' :
                $this->notifications();
                break;
            default:
                break;
        }
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public function isValidSignature(string $prefix): bool
    {
        $callback_secret = $this->config->get('payment_ul_' . $prefix . '_callback_secret');
        $callback = file_get_contents('php://input');
        $headers = getallheaders();
        $callback_signature = $headers['signature'] ?? '';
        $generated_signature = hash('sha512', $callback . $callback_secret);

        return ($generated_signature === $callback_signature);
    }

    /**
     * @return bool
     * @throws JsonException|UnlimintException
     */
    protected function parse_notification_body(): bool
    {
        $err = true;
        $input = file_get_contents("php://input");

        if (!empty($input)) {
            $request_data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

            if (isset($request_data['callback_time'], $request_data['payment_data'])) {
                //it calls successful_request
                $err = !$this->successfulRequest($request_data);
                if ($err) {
                    $this->ul_util->writeLog(
                        __FUNCTION__ .
                        ' - Data: ' .
                        json_encode($request_data, JSON_THROW_ON_ERROR)
                    );
                }
            }
        }

        if ($err) {
            $this->ul_util->writeLog(__FUNCTION__ . ' - Wrong params in Request IPN.');
        }

        return $err;
    }

    /**
     * IPN
     * @throws JsonException
     * @throws UnlimintException
     */
    public function notifications(): void
    {
        if (!$this->parse_notification_body()) {
            echo json_encode(422, JSON_THROW_ON_ERROR);
        } else {
            echo json_encode(200, JSON_THROW_ON_ERROR);
        }
    }

    /**
     * @param ?string $orderID
     * @return string
     * @throws Exception
     */
    protected function getPaymentCode(?string $orderID = null): string
    {
        $result = '';
        if (empty($orderID)) {
            if (!empty(static::UL_PREFIX)) {
                $result = static::UL_PREFIX;
            }

            return $result;
        }

        if (empty($result)) {
            $this->load->model(self::CHECKOUT_ORDER);
            $dbOrderInfo = $this->model_checkout_order->getOrder($orderID);

            $this->get_prefix = new UnlimintOrderInfo();
            $result = $this->get_prefix->getPrefix($dbOrderInfo['payment_code']);
        }

        return $result;
    }

    /**
     * Process successful request
     * @param array $data
     * @return bool
     * @throws JsonException|UnlimintException|Exception
     */
    public function successfulRequest(array $data): bool
    {
        $prefix = '';
        if (isset($data['merchant_order']['id'])) {
            $prefix = $this->getPaymentCode((int)($data['merchant_order']['id']));
        }

        if (!$this->isValidSignature($prefix)) {
            $this->log->write(
                __FUNCTION__ .
                ' Invalid signature ' .
                json_encode($this->request->request, JSON_THROW_ON_ERROR)
            );
            return false;
        }

        $this->log->write(
            __FUNCTION__ .
            ' - updating metadata and status with data: ' .
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        if ($prefix) {
            $this->updateOrder($data, $prefix);
            return true;
        }

        $this->log->write(__FUNCTION__ . ' - External Reference not found');

        return (false);
    }

    /**
     * @param $payment
     * @param $prefix
     * @throws JsonException|Exception|UnlimintException
     */
    public function updateOrder($payment, $prefix): void
    {
        $this->load->model(self::CHECKOUT_ORDER);
        $this->get_instance_ul_util()->updateOrder(
            $payment,
            $this->model_checkout_order,
            $this->db,
            $prefix
        );
    }

    /**
     * @throws UnlimintException
     */
    public function setPreModuleAnalytics(): array
    {
        $query = $this->db->query("SELECT code FROM " . DB_PREFIX . "extension WHERE type = 'payment'");

        $resultModules = array();
        $token = $this->config->get('payment_ul_' . static::UL_PREFIX . '_access_token');
        $customerEmail = $this->customer->getEmail();
        $userLogged = $this->customer->isLogged() ? 1 : 0;

        foreach ($query->rows as $result) {
            $resultModules[] = $result['code'];
        }

        return $this->get_instance_ul_util()->createAnalytics($resultModules, $token, $customerEmail, $userLogged);
    }

    /**
     * @param array $orderInfo
     * @return string
     */
    public function getZip(array $orderInfo): string
    {
        $result = '';
        if (!empty($orderInfo['shipping_postcode'])) {
            $result = $orderInfo['shipping_postcode'];
        } elseif (!empty($orderInfo['payment_postcode'])) {
            $result = $orderInfo['payment_postcode'];
        }

        return $result;
    }

    /**
     * @param $data
     * @throws JsonException|UnlimintException
     */
    public function createUrl($data): void
    {
        $url = $this->get_instance_ul_util()->processPayment(
            $data,
            $this->orderInfo,
            $this->statusId,
            $this->model_order,
            $this->instance_ul
        );

        if ($url !== false) {
            $this->ul_cart->clearOrderedProducts();
            $this->ul_cart->clearOldCartBackups();
            $this->response->redirect($url);
        } else {
            $this->session->data['error'] = $this->get_instance_ul_util()->error;
            $this->response->redirect($this->url->link(self::CHECKOUT_CHECKOUT, '', true));
        }
    }

    /**
     * @param $e
     * @throws JsonException
     */
    public function exceptionCatch($e): void
    {
        echo json_encode(array("status" => $e->getCode(), "message" => $e->getMessage()), JSON_THROW_ON_ERROR);
    }
}
