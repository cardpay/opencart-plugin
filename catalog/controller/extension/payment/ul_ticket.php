<?php

require_once "lib/unlimint.php";
require_once "lib/ul_util.php";
require_once "ul_general.php";

class ControllerExtensionPaymentULTicket extends ControllerExtensionPaymentULGeneral
{
    public const EXTENSION_PAYMENT_UL_TICKET = 'extension/payment/ul_ticket';
    public const UL_PREFIX = 'ticket';

    public function index()
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_TICKET);
        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['payment_postcode'] = $this->getZip($orderInfo);

        $data['payment_button'] = $this->language->get('payment_button');
        $data['actionForm'] = $orderInfo['store_url'] . 'index.php?route=extension/payment/ul_ticket/payment';
        $data['analytics'] = $this->setPreModuleAnalytics();
        $data['firstname'] = $orderInfo['firstname'];
        $data['lastname'] = $orderInfo['lastname'];
        $data['address'] = $orderInfo['shipping_address_1'];
        $data['zipcode'] = $orderInfo['shipping_postcode'];
        $data['shipping_city'] = $orderInfo['shipping_city'];
        $data['ask_cpf'] = $this->config->get('payment_ul_ticket_ask_cpf');
        $data['countryType'] = $orderInfo['payment_country'];

        if ($orderInfo['payment_zone_code'] != null && $orderInfo['payment_zone'] != null) {
            $data['payment_zone_code'] = $orderInfo['payment_zone_code'];
            $data['payment_zone'] = $orderInfo['payment_zone'];
        } else {
            $data['payment_zone_code'] = "";
            $data['payment_zone'] = 'Selecione';
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/ul_ticket')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/ul_ticket', $data);
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_TICKET, $data);
    }

    public function payment()
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_TICKET);

        try {
            $this->load->model(self::CHECKOUT_ORDER);

            $payment_method = 'BOLETO';

            $this->orderInfo['post_code'] = $_REQUEST['postcode'];

            $data = $this->get_instance_ul_util()->createApiRequest($this->orderId, $this->orderInfo);
            $data['payment_method'] = $payment_method;
            $data['payment_data']['amount'] = round($this->orderInfo['total'] * $this->orderInfo['currency_value'], 2);
            $data['payment_data']['currency'] = $this->orderInfo['currency_code'];
            $docNumber = $_REQUEST['unlimint_custom']['docnumber'];
            $data['customer']['identity'] = $docNumber;
            $data['customer']['full_name'] = $this->orderInfo['firstname'] . ' ' . $this->orderInfo['lastname'];

            $url = $this->get_instance_ul_util()->processPayment($data, $this->orderInfo, $this->statusId, $this->model_order, $this->instance_ul);
            if ($url !== false) {
                $this->response->redirect($url);
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => $e->getCode(), "message" => $e->getMessage()));
        }
    }

    private function getMethods()
    {
        try {
            return $this->get_instance_ul()->get("/payment_methods");
        } catch (Exception $e) {
            $this->load->language(self::EXTENSION_PAYMENT_UL_TICKET);
            return array('status' => 400, 'message' => $this->language->get('error_access_token'));
        }
    }

    public function getAcceptedMethods()
    {
        $methods = $this->getMethods();
        $methods_api = $methods['response'];
        $saved_methods = preg_split("/[\s,]+/", $this->config->get('payment_ul_ticket_methods'));
        $accepted_methods = array();
        foreach ($methods_api as $method) {
            if (in_array($method['id'], $saved_methods)) {
                $accepted_methods[] = array('method' => $method['id'], 'secure_thumbnail' => $method['secure_thumbnail']);
            }
        }

        echo json_encode(array('methods' => $accepted_methods));
    }
}
