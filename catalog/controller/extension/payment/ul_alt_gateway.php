<?php

require_once __DIR__ . "/lib/unlimint.php";
require_once __DIR__ . "/lib/ul_util.php";
require_once __DIR__ . "/ul_general.php";
require_once __DIR__ . "/ul_pix.php";
require_once __DIR__ . "/ul_ticket.php";

class ControllerExtensionPaymentULAltGateway extends ControllerExtensionPaymentULGeneral
{
    public function getData($extension_payment_ul)
    {
        $this->load->language($extension_payment_ul);

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data = $this->getPostCode($orderInfo);

        $data['actionForm'] = $orderInfo['store_url'] . 'index.php?route=' . $extension_payment_ul . '/payment';
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
            $data['payment_zone'] = $this->language->get('select_one');
        }

        return $data;
    }

    public function getPaymentData($ul_prefix)
    {
        try {
            $this->load->model(self::CHECKOUT_ORDER);

            $this->orderInfo['post_code'] = $_REQUEST['postcode'];

            $data = $this->get_instance_ul_util()->createApiRequest($this->orderId, $this->orderInfo);
            $data['payment_method'] = $ul_prefix;
            $docNumber = $_REQUEST['unlimint_custom']['docnumber'];
            $data['customer']['identity'] = $docNumber;
            $data['customer']['full_name'] = $this->orderInfo['firstname'] . ' ' . $this->orderInfo['lastname'];

            $this->createUrl($data);
        } catch (Exception $e) {
            $this->exceptionCatch($e);
        }
    }

    /**
     * @param $orderInfo
     * @return array
     */
    protected function getPostCode($orderInfo)
    {
        if (static::UL_PREFIX != 'ticket') {
            return [];
        }

        $data = [];

        $zip = $this->getZip($orderInfo);
        $data['payment_postcode'] = (preg_match('/^[0-9]{8}$/', $zip)) ? $zip : '';

        $data['labels'] = $data['errors'] = [];

        foreach (['payment_button', 'label_cpf', 'label_post_code'] as $label) {
            $data['labels'][$label] = $this->language->get($label);
        }

        foreach (['error_invalid_cpf', 'error_empty_cpf', 'error_invalid_post_code'] as $label) {
            $data['errors'][$label] = $this->language->get($label);
        }

        return $data;
    }
}
