<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

define('CALLBACK_PATH', 'extension/unlimit/callback.php?route=extension/unlimit/payment/ul_general.callback');
define('ACTION_PATH', '&action=restoreOrderProducts');

use Opencart\System\Library\Cart\Customer;
use Unlimit\ULCart;
use Unlimit\ULUtil;
use Unlimit\Unlimit;
use Unlimit\UnlimitException;
use Unlimit\UnlimitOrderInfo;

require_once __DIR__ . "/ul_card.php";

require_once DIR_EXTENSION . 'unlimit/system/library/unlimit.php';
require_once DIR_EXTENSION . 'unlimit/system/library/unlimit_exception.php';
require_once DIR_EXTENSION . 'unlimit/system/library/ul_util.php';
require_once DIR_EXTENSION . 'unlimit/system/library/ul_cart.php';
require_once DIR_EXTENSION . 'unlimit/system/library/ul_rest_client.php';
require_once DIR_EXTENSION . 'unlimit/system/library/unlimit_order_info.php';

/**
 * @property Request $request
 * @property Unlimit $ul
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
 * @property ULUtil $ul_util
 */
class ULGeneral extends \Opencart\System\Engine\Controller
{
    public const CHECKOUT_ORDER = 'checkout/order';
    public const CHECKOUT_CART = 'checkout/cart';
    public const CHECKOUT_SUCCESS = 'checkout/success';
    public const UL_PREFIX = '';

    public $get_prefix;
    private $ul;
    protected $orderId;
    protected $instance_ul;
    protected $orderInfo;
    protected $data;
    protected $statusId;
    protected $amount;
    protected $ul_cart;
    protected $ul_util;

    /**
     * @param $registry
     *
     * @throws UnlimitException|Exception
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('extension/unlimit/payment/ul_general');
        $this->load->model(self::CHECKOUT_ORDER);
        $this->orderId     = $this->session->data['order_id'] ?? '';
        $this->instance_ul = $this->get_instance_ul();
        $this->model_order = $this->model_checkout_order;
        if ($this->orderId) {
            $orderInfo     = $this->model_checkout_order->getOrder($this->orderId);
            $orderProducts = $this->model_checkout_order->getProducts($this->orderId);
        } else {
            $orderInfo     = [];
            $orderProducts = [];
        }
        $this->orderInfo                  = ( ! $orderInfo) ? [] : $orderInfo;
        $this->orderInfo['orderProducts'] = ( ! $orderProducts) ? [] : $orderProducts;
        $this->statusId                   = 1;
        $this->amount                     = (
            $this->orderInfo &&
            isset($this->orderInfo['total']) &&
            isset($this->orderInfo['currency_value'])
        ) ? round($this->orderInfo['total'] * $this->orderInfo['currency_value'], 2) : 0;

        $this->ul_cart = (new ULCart())
            ->setDb($this->db)
            ->setCart($this->cart)
            ->setCustomer($this->customer)
            ->setSession($this->session);
    }

    /**
     * @return ULUtil
     * @throws UnlimitException
     */
    public function get_instance_ul_util(): ULUtil
    {
        if (is_null($this->ul_util)) {
            $this->ul_util = (new ULUtil())
                ->setConfig($this->config)
                ->setUl($this->get_instance_ul())
                ->setLog($this->log);
        }

        return $this->ul_util;
    }

    /**
     * @return Unlimit
     * @throws UnlimitException
     */
    public function get_instance_ul(): Unlimit
    {
        return $this->ul ?? $this->ul = (Unlimit::getInstance(static::UL_PREFIX, $this->config))
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
        $status       = (string)$this->request->get['status'];
        if ($request_type) {
            $status = $request_type === "token" ? 'T' . $status : 'S' . $status;
        }

        $message = $this->language->get($status);

        echo json_encode(['message' => $message], JSON_THROW_ON_ERROR);
    }

    /**
     * CallBack IPN : eg: https://<domain>/index.php?route=extension/payment/ul_general/callback
     * @throws JsonException|UnlimitException
     */
    public function callback(): void
    {
        $this->get_instance_ul_util();

        $action = $this->request->get['action'] ?? '';

        switch ($action) {
            case 'success' :
            case 'inprocess' :
                $this->ul_cart->clearCurrentBackup();
                $this->response->redirect($this->url->link(self::CHECKOUT_SUCCESS));
                break;

            case 'decline' :
            case 'cancel' :
                $this->session->data['error']    = "Order was unsuccessful";
                $this->session->data['order_id'] = null;
                $actual_link                     = $this->config->get('site_url') . CALLBACK_PATH . ACTION_PATH;
                header('Refresh: 0; url=' . $actual_link);
                break;

            case 'restoreOrderProducts' :
                $this->ul_cart->restoreOrderedProducts();
                $this->response->redirect($this->url->link(self::CHECKOUT_CART));
                break;

            case '' :
                $this->notifications();
                break;

            default:
                break;
        }
    }

    /**
     * @param  string  $prefix
     *
     * @return bool
     */
    public function isValidSignature(string $prefix): bool
    {
        $callback_secret     = $this->config->get('payment_ul_' . $prefix . '_callback_secret');
        $callback            = file_get_contents('php://input');
        $headers             = getallheaders();
        $callback_signature  = $headers['Signature'] ?? '';
        $generated_signature = hash('sha512', $callback . $callback_secret);

        return ($generated_signature === $callback_signature);
    }

    /**
     * @return bool
     * @throws JsonException|UnlimitException
     */
    protected function parse_notification_body()
    {
        $input = file_get_contents("php://input");

        if (empty($input)) {
            return null;
        }

        $request_data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

        if ( ! isset($request_data['callback_time'], $request_data['payment_data'])) {
            return null;
        }

        $result = $this->successfulRequest($request_data);
        if ($result === false) {
            $this->ul_util->writeLog(
                __FUNCTION__ .
                ' - Data: ' .
                json_encode($request_data, JSON_THROW_ON_ERROR)
            );
        }

        return $result;
    }

    /**
     * IPN
     * @throws JsonException
     * @throws UnlimitException
     */
    public function notifications(): void
    {
        $result = $this->parse_notification_body();
        if ($result === null) {
            http_response_code(422);
            exit;
        } elseif ($result === false) {
            http_response_code(400);
            exit;
        } else {
            http_response_code(200);
            echo json_encode(['status' => 'success'], JSON_THROW_ON_ERROR);
        }
    }

    /**
     * @param ?string  $orderID
     *
     * @return string
     * @throws Exception
     */
    protected function getPaymentCode(?int $orderID = null): string
    {
        $result = '';
        if (empty($orderID)) {
            if ( ! empty(static::UL_PREFIX)) {
                $result = static::UL_PREFIX;
            }

            return $result;
        }

        if (empty($result)) {
            $this->load->model(self::CHECKOUT_ORDER);
            $dbOrderInfo = $this->model_checkout_order->getOrder($orderID);

            $this->get_prefix = new UnlimitOrderInfo();
            $payment_method   = $dbOrderInfo['payment_method'];
            $payment_code     = explode('.', $payment_method['code']);
            $result           = $this->get_prefix->getPrefix($payment_code[0]);
        }

        return $result;
    }

    /**
     * Process successful request
     *
     * @param  array  $data
     *
     * @return bool
     * @throws JsonException|UnlimitException|Exception
     */
    public function successfulRequest(array $data): bool
    {
        $prefix    = '';
        $orderId   = '';
        $paymentId = '';

        if (isset($data['merchant_order']['id'])) {
            $prefix  = $this->getPaymentCode((int)($data['merchant_order']['id']));
            $orderId = $data['merchant_order']['id'];
        }

        if (isset($data['payment']['id'])) {
            $paymentId = $data['payment']['id'];
        }

        if ( ! $this->isValidSignature($prefix)) {
            $this->log->write(
                __FUNCTION__ .
                ' Invalid signature ' .
                json_encode($this->request, JSON_THROW_ON_ERROR)
            );

            return false;
        }

        $this->log->write(
            __FUNCTION__ .
            ' - updating metadata and status with data: ' .
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        if ($prefix) {
            $this->updateOrder($data);

            $this->log->write(
                __FUNCTION__ .
                ' - Successfully updated order with Order ID: ' . $orderId . ' and Payment ID: ' . $paymentId
            );

            return true;
        }

        $this->log->write(__FUNCTION__ . ' - External Reference not found');

        return false;
    }

    /**
     * @param $payment
     * @param $prefix
     *
     * @throws JsonException|Exception|UnlimitException
     */
    public function updateOrder($payment): void
    {
        $this->load->model(self::CHECKOUT_ORDER);
        $this->get_instance_ul_util()->updateOrder(
            $payment,
            $this->model_checkout_order,
            $this->db,
        );
    }

    /**
     * @throws UnlimitException
     */
    public function setPreModuleAnalytics(): array
    {
        $query = $this->db->query("SELECT code FROM " . DB_PREFIX . "extension WHERE type = 'payment'");

        $resultModules = array();
        $token         = $this->config->get('payment_ul_' . static::UL_PREFIX . '_access_token');
        $customerEmail = $this->customer->getEmail();
        $userLogged    = $this->customer->isLogged() ? 1 : 0;

        foreach ($query->rows as $result) {
            $resultModules[] = $result['code'];
        }

        return $this->get_instance_ul_util()->createAnalytics($resultModules, $token, $customerEmail, $userLogged);
    }

    /**
     * @param  array  $orderInfo
     *
     * @return string
     */
    public function getZip(array $orderInfo): string
    {
        $result = '';
        if ( ! empty($orderInfo['shipping_postcode'])) {
            $result = $orderInfo['shipping_postcode'];
        } elseif ( ! empty($orderInfo['payment_postcode'])) {
            $result = $orderInfo['payment_postcode'];
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return string|null
     * @throws JsonException|UnlimitException
     */
    public function createUrl($api_request): string|null
    {
        $api_access_mode = ULUtil::ACCESS_MODE_GATEWAY;
        if ( ! in_array(static::UL_PREFIX, ['gpay', 'apay'])) {
            $api_access_mode = $this->config->get('payment_ul_' . static::UL_PREFIX . '_payment_page') ?
                ULUtil::ACCESS_MODE_PP :
                ULUtil::ACCESS_MODE_GATEWAY;
        }
        $url = $this->get_instance_ul_util()->processPayment(
            $api_request,
            $this->orderInfo,
            $this->statusId,
            $this->model_order,
            $this->instance_ul,
            $api_access_mode
        );

        $this->session->data['order_id'] = null;
        if ($url !== false) {
            $this->ul_cart->clearOrderedProducts();
            $this->ul_cart->clearOldCartBackups();
            if ($api_access_mode == ULUtil::ACCESS_MODE_GATEWAY) {
                $this->response->redirect($url);
            }

            return $url;
        } else {
            $this->session->data['error'] = "Some issues occurred on the server, please try again later.";
            $actual_link                  = $this->config->get('site_url') .
                                            CALLBACK_PATH . ACTION_PATH;
            if ($api_access_mode == ULUtil::ACCESS_MODE_GATEWAY) {
                $this->response->redirect($actual_link);
            }

            return null;
        }
    }

    /**
     * @param $e
     *
     * @throws JsonException
     */
    public function exceptionCatch($e): void
    {
        $this->log->write(__FUNCTION__ . $e->getMessage());
    }

    protected function validatePhone(string $phone): ?string
    {
        $cleanedPhone = $this->cleanPhone($phone);
        if (strlen($cleanedPhone) < 8 || strlen($cleanedPhone) > 18) {
            return null;
        }

        return $cleanedPhone;
    }

    protected function cleanPhone(string $phone): string
    {
        return preg_replace("/[^\d]+/", "", $phone);
    }

    public function getResponse($url): array
    {
        $json = [
            'result'   => 'failure',
            'redirect' => $this->config->get('site_url') .
                          CALLBACK_PATH . ACTION_PATH
        ];
        if ($url) {
            $json['redirect'] = $url;
            $json['result']   = 'success';
        }

        return $json;
    }
}
