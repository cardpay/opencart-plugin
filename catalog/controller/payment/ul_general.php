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
    public const UL_CODES = [
        'CARD' => 'CARD',
        'APAY' => 'APPLEPAY',
        'GPAY' => 'GOOGLEPAY',
        'MBWAY' => 'MBWAY',
        'PIX' => 'PIX',
        'MULTIBANCO' => 'MULTIBANCO',
        'PAYPAL' => 'PAYPAL',
        'SEPA' => 'SEPATRANSFER',
        'SPEI' => 'SPEI',
        'OXXO' => 'OXXO',
        'TICKET' => 'BOLETO',
    ];

    public $get_prefix;
    private $ul;
    protected $orderId;
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
        $this->orderId = $this->session->data['order_id'] ?? '';
        $this->model_order = $this->model_checkout_order;
        if ($this->orderId) {
            $orderInfo = $this->model_checkout_order->getOrder($this->orderId);
            $orderProducts = $this->model_checkout_order->getProducts($this->orderId);
        } else {
            $orderInfo = [];
            $orderProducts = [];
        }
        $this->orderInfo = (!$orderInfo) ? [] : $orderInfo;
        $this->orderInfo['orderProducts'] = (!$orderProducts) ? [] : $orderProducts;
        $this->statusId = 1;
        $this->amount = (
            $this->orderInfo &&
            isset($this->orderInfo['total']) &&
            isset($this->orderInfo['currency_value'])
        ) ? round($this->orderInfo['total'] * $this->orderInfo['currency_value'], 2) : 0;

        $this->ul_cart = (new ULCart())
            ->set_db($this->db)
            ->set_cart($this->cart)
            ->set_customer($this->customer)
            ->set_session($this->session);
    }

    /**
     * @return ULUtil
     * @throws UnlimitException
     */
    public function get_instance_ul_util($prefix = null): ULUtil
    {
        if (is_null($this->ul_util)) {
            $this->ul_util = (new ULUtil())
                ->set_config($this->config)
                ->set_ul($this->get_instance_ul($prefix))
                ->set_Log($this->log);
        }

        return $this->ul_util;
    }

    /**
     * @return Unlimit
     * @throws UnlimitException
     */
    public function get_instance_ul($prefix = null): Unlimit
    {
        return $this->ul ?? $this->ul = (Unlimit::get_instance($prefix ?? static::UL_PREFIX, $this->config))
            ->set_log($this->log)
            ->set_db($this->db);
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
     * @throws JsonException|UnlimitException
     */
    public function callback(): void
    {
        $action = $this->request->get['action'] ?? '';
        $language = $this->getLanguageCode();

        switch ($action) {
            case 'success' :
            case 'inprocess' :
                $this->ul_cart->clear_current_backup();
                $this->response->redirect($this->url->link(self::CHECKOUT_SUCCESS, ['language' => $language]));
                break;

            case 'decline' :
            case 'cancel' :
                $this->session->data['error'] = "Order was unsuccessful";
                $this->session->data['order_id'] = null;
                $actualLink = $this->config->get('site_url') .
                    CALLBACK_PATH . ACTION_PATH . '&language=' . $language;
                header('Refresh: 0; url=' . $actualLink);
                break;

            case 'restoreOrderProducts' :
                $this->ul_cart->restore_ordered_products();
                $this->response->redirect(
                    $this->url->link(self::CHECKOUT_CART, ['language' => $language])
                );
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
     *
     * @return bool
     */
    public function isValidSignature(string $prefix): bool
    {
        $callback_secret = $this->config->get('payment_ul_' . $prefix . '_callback_secret');
        $callback = file_get_contents('php://input');
        $headers = getallheaders();
        $callback_signature = $headers['Signature'] ?? '';
        $generated_signature = hash('sha512', $callback . $callback_secret);

        return ($generated_signature === $callback_signature);
    }

    /**
     * @return bool
     * @throws JsonException|UnlimitException
     */
    protected function parse_notification_body(): bool
    {
        $input = file_get_contents("php://input");

        $request_data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($request_data['callback_time'], $request_data['payment_data'])) {
            return false;
        }

        $prefix = '';
        $orderId = '';
        $paymentId = '';

        if (isset($request_data['merchant_order']['id'])) {
            $prefix = $this->getPaymentCode((int)($request_data['merchant_order']['id']));
            $orderId = $request_data['merchant_order']['id'];
        }

        if (isset($request_data['payment']['id'])) {
            $paymentId = $request_data['payment']['id'];
        }

        if (!$prefix || !$this->isValidSignature($prefix)) {
            if (!$prefix) {
                $this->writeLog(
                    $prefix,
                    __FUNCTION__ . ' - External Reference not found' .
                    ' - Data: ' .
                    json_encode($request_data, JSON_THROW_ON_ERROR)
                );
            } else {
                $this->writeLog(
                    $prefix,
                    __FUNCTION__ . ' Invalid signature ' .
                    ' - Data: ' .
                    json_encode($request_data, JSON_THROW_ON_ERROR)
                );
            }

            return false;
        }
        $this->writeLog(
            $prefix,
            __FUNCTION__ . ' - updating metadata and status with data: ' .
            json_encode($request_data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->updateOrder($request_data, $prefix);

        $this->writeLog(
            $prefix,
            __FUNCTION__ .
            ' - Successfully updated order with Order ID: ' . $orderId . ' and Payment ID: ' . $paymentId
        );

        return true;
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
     * @param ?string $orderID
     *
     * @return string
     * @throws Exception
     */
    protected function getPaymentCode(?int $orderID = null): string
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

            $this->get_prefix = new UnlimitOrderInfo();
            $payment_method = $dbOrderInfo['payment_method'];
            $payment_code = explode('.', $payment_method['code']);
            $result = $this->get_prefix->get_prefix($payment_code[0]);
        }

        return $result;
    }

    /**
     * @param $payment
     * @param $prefix
     *
     * @throws JsonException|Exception|UnlimitException
     */
    public function updateOrder($payment, $prefix): void
    {
        $this->load->model(self::CHECKOUT_ORDER);
        $this->get_instance_ul_util($prefix)->update_order(
            $payment,
            $this->model_checkout_order,
            $this->db,
            $prefix
        );
    }

    /**
     * @throws UnlimitException
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

        return $this->get_instance_ul_util()->create_analytics($resultModules, $token, $customerEmail, $userLogged);
    }

    /**
     * @param array $orderInfo
     *
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
     *
     * @return string|null
     * @throws JsonException|UnlimitException
     */
    public function createUrl($api_request): string|null
    {
        $api_access_mode = ULUtil::ACCESS_MODE_GATEWAY;
        if (!in_array(static::UL_PREFIX, ['gpay', 'apay'])) {
            $api_access_mode = $this->config->get('payment_ul_' . static::UL_PREFIX . '_payment_page') ?
                ULUtil::ACCESS_MODE_PP :
                ULUtil::ACCESS_MODE_GATEWAY;
        }
        $url = $this->get_instance_ul_util()->process_payment(
            $api_request,
            $this->orderInfo,
            $this->statusId,
            $this->model_order,
            $this->get_instance_ul(),
            $api_access_mode
        );

        $this->session->data['order_id'] = null;
        if ($url !== false) {
            $this->ul_cart->clear_ordered_products();
            $this->ul_cart->clear_old_cart_backups();
            if ($api_access_mode == ULUtil::ACCESS_MODE_GATEWAY) {
                $this->response->redirect($url);
            }

            return $url;
        } else {
            $this->session->data['error'] = "Some issues occurred on the server, please try again later.";
            $actualLink = $this->config->get('site_url') .
                CALLBACK_PATH . ACTION_PATH;
            if ($api_access_mode == ULUtil::ACCESS_MODE_GATEWAY) {
                $this->response->redirect($actualLink);
            }

            return null;
        }
    }

    /**
     * @param $e
     *
     * @throws JsonException
     */
    public function exceptionCatch($e, $prefix, $api_access_mode = ULUtil::ACCESS_MODE_GATEWAY): void
    {
        $this->writeLog($prefix, __FUNCTION__ . ': ' . $e->getMessage());
        $this->session->data['error'] = $e->getMessage();
        $this->session->data['order_id'] = null;
        $actualLink = $this->config->get('site_url') . CALLBACK_PATH . ACTION_PATH .
            '&language=' . $this->getLanguageCode();
        header('Refresh: 0; url=' . $actualLink);
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
            'result' => 'failure',
            'redirect' => $this->config->get('site_url') .
                CALLBACK_PATH . ACTION_PATH
        ];
        if ($url) {
            $json['redirect'] = $url;
            $json['result'] = 'success';
        }

        return $json;
    }

    protected function validateZip($ulPrefix, $fieldValue): void
    {
        if ($ulPrefix === self::UL_CODES['TICKET']) {
            $cleanedPhone = $this->cleanPhone($fieldValue);
            if (strlen($fieldValue) > 17 || strlen($cleanedPhone) !== 8) {
                throw new UnlimitException($this->language->get('error_invalid_post_code'));
            }
        }

        if (($ulPrefix === self::UL_CODES['PIX']) &&
            strlen($fieldValue) > 17) {
            throw new UnlimitException($this->language->get('error_invalid_post_code'));
        }

        if (($ulPrefix === self::UL_CODES['CARD']) &&
            strlen($fieldValue) > 12) {
            throw new UnlimitException($this->language->get('error_invalid_post_code'));
        }
    }

    public function getLanguageCode()
    {
        return $_REQUEST['language'] ?? $this->config->get('config_language');
    }

    public function writeLog($prefix, $message): void
    {
        if (!$prefix || $this->config->get('payment_ul_' . $prefix . '_log_to_file')) {
            $this->log->write($message);
        }
    }
}
