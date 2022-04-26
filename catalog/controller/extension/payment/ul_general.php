<?php

require_once "lib/unlimint.php";
require_once "lib/ul_util.php";
require_once "ul_ticket.php";
require_once "ul_card.php";

/**
 * @property Request $request
 * @property UL $ul
 * @property Config $config
 * @property Response $response
 */
class ControllerExtensionPaymentULGeneral extends Controller
{
    public const CHECKOUT_ORDER = 'checkout/order';
    public const CHECKOUT_CHECKOUT = 'checkout/checkout';
    public const CHECKOUT_SUCCESS = 'checkout/success';
    public const UL_PREFIX = '';

    private $ul_util;
    private $ul;
    protected $orderId;
    protected UL $instance_ul;
    protected $model_order;
    protected $orderInfo;
    protected $data;
    protected $statusId;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('extension/payment/ul_general.php');
        $this->load->model(self::CHECKOUT_ORDER);
        $this->orderId = $this->session->data['order_id'] ?? '';
        $this->instance_ul = $this->get_instance_ul();
        $this->model_order = $this->model_checkout_order;
        $this->orderInfo = $this->model_checkout_order->getOrder($this->orderId);
        $this->statusId = 1;
    }

    public function get_instance_ul_util()
    {
        if ($this->ul_util == null) {
            $this->ul_util = new ULOpencartUtil();
            $this->ul_util->setLog($this->log);
            $this->ul_util->setConfig($this->config);
            $this->ul_util->setUl($this->get_instance_ul());
        }

        return $this->ul_util;
    }

    public function get_instance_ul()
    {
        return $this->ul ?? $this->ul = UL::getInstance(static::UL_PREFIX, $this->config)
                ->setLog($this->log)
                ->setDb($this->db);
    }

    public function getPaymentStatus()
    {
        $this->load->language('payment/ul_' . static::UL_PREFIX);
        $request_type = isset($this->request->get['request_type']) ? (string)$this->request->get['request_type'] : "";
        $status = (string)$this->request->get['status'];
        if ($request_type) {
            $status = $request_type === "token" ? 'T' . $status : 'S' . $status;
        }

        $message = $this->language->get($status);
        echo json_encode(['message' => $message]);
    }

    //CallBack IPN : eg: https://<domain>/index.php?route=extension/payment/ul_general/callback
    public function callback()
    {
        $this->get_instance_ul_util();
        $action = '';
        if (isset($this->request->get['action'])) {
            $action = $this->request->get['action'];
        }

        switch ($action) {
            case 'success' :
            case 'inproccess' :
                $this->response->redirect($this->url->link(self::CHECKOUT_SUCCESS));
                break;
            case 'decline' :
            case 'cancel' :
                $this->response->redirect($this->url->link(self::CHECKOUT_CHECKOUT));
                break;
            case '' :
                $this->notifications();
                break;
            default:
                break;
        }
    }

    public function isValidSignature($prefix)
    {
        $callback_secret = $this->config->get('payment_ul_' . $prefix . '_callback_secret');
        $callback = file_get_contents('php://input');
        $headers = getallheaders();
        $callback_signature = $headers['signature'] ?? '';
        $generated_signature = hash('sha512', $callback . $callback_secret);
        return ($generated_signature === $callback_signature);
    }


    protected function parse_notification_body(): bool
    {
        $err = true;
        $input = file_get_contents("php://input");

        if (!empty($input)) {
            $request_data = json_decode($input, true);

            if (isset($request_data['callback_time']) &&
                (isset($request_data['payment_data']) || isset($request_data['recurring_data']))) {

                //it calls successful_request
                $err = !$this->successfulRequest($request_data);
                if ($err) {
                    $this->ul_util->writeLog(__FUNCTION__ . ' - Data: ' . json_encode($request_data));
                }
            }
        }
        if ($err) {
            $this->ul_util->writeLog(__FUNCTION__ . ' - Wrong params in Request IPN.');
        }
        return $err;
    }

    /**
     *  IPN
     */
    public function notifications()
    {
        if (!$this->parse_notification_body()) {
            echo json_encode(422);
        } else {
            echo json_encode(200);
        }
    }

    protected function getPaymentCode($orderID = null)
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
            $dbCode = $dbOrderInfo['payment_code'];
            switch ($dbCode) {
                case 'ul_card':
                    $result = 'card';
                    break;
                case 'ul_ticket':
                    $result = 'ticket';
                    break;
                default:
                    $result = '';
            }
        }
        return $result;
    }

    /**
     * Process successful request
     */
    public function successfulRequest($data)
    {
        $prefix = '';
        if (isset($data['merchant_order']) && isset($data['merchant_order']['id'])) {
            $prefix = $this->getPaymentCode((int)($data['merchant_order']['id']));
        }

        if (!$this->isValidSignature($prefix)) {
            $this->log->write(__FUNCTION__ . ' Invalid signature ' . json_encode($this->request->request));
            return false;
        }
        $this->log->write(__FUNCTION__ . ' - updating metadata and status with data: ' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($prefix) {
            $this->updateOrder($data, $prefix);
            return true;
        }

        $this->log->write(__FUNCTION__ . ' - External Reference not found');
        return (false);
    }

    public function updateOrder($payment, $prefix)
    {
        $this->load->model(self::CHECKOUT_ORDER);
        $this->get_instance_ul_util()->updateOrder($payment, $this->model_checkout_order, $this->db, $prefix);
    }

    public function setPreModuleAnalytics()
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

    public function getZip($orderInfo)
    {
        if (!empty($orderInfo['shipping_postcode'])) {
            return $orderInfo['shipping_postcode'];
        } elseif (!empty($orderInfo['payment_postcode'])) {
            return $orderInfo['payment_postcode'];
        }

        return '';
    }
}
