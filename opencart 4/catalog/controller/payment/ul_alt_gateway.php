<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_general.php";

require_once DIR_EXTENSION . 'unlimit/system/library/unlimit_exception.php';

use Unlimit\UnlimitException;

class ULAltGateway extends ULGeneral
{
    /**
     * @param  string  $extension_payment_ul
     *
     * @return array
     * @throws UnlimitException
     */
    public function getData($extension_payment_ul): array
    {
        $this->load->language($extension_payment_ul);

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data = $this->getPostCode($orderInfo);

        $data['actionForm']    = $this->url->link($extension_payment_ul . '.payment');
        $data['analytics']     = $this->setPreModuleAnalytics();
        $data['firstname']     = $orderInfo['firstname'];
        $data['lastname']      = $orderInfo['lastname'];
        $data['address']       = $orderInfo['shipping_address_1'];
        $data['zipcode']       = $orderInfo['shipping_postcode'];
        $data['shipping_city'] = $orderInfo['shipping_city'];
        $data['ask_cpf']       = $this->config->get('payment_ul_ticket_ask_cpf');
        $data['countryType']   = $orderInfo['payment_country'];

        if ( ! is_null($orderInfo['payment_zone_code']) && ! is_null($orderInfo['payment_zone'])) {
            $data['payment_zone_code'] = $orderInfo['payment_zone_code'];
            $data['payment_zone']      = $orderInfo['payment_zone'];
        } else {
            $data['payment_zone_code'] = "";
            $data['payment_zone']      = $this->language->get('select_one');
        }
        $data['telephone_display']  = $this->config->get('config_telephone_display');
        $data['telephone_required'] = $this->config->get('config_telephone_required');

        return $data;
    }

    /**
     * @param  string  $ul_prefix
     *
     * @return string|null
     * @throws JsonException
     */
    public function getPaymentData($ul_prefix): string|null
    {
        try {
            $this->load->model(self::CHECKOUT_ORDER);

            $this->orderInfo['post_code'] = $_REQUEST['postcode'] ?? '';

            $data                   = $this->get_instance_ul_util()->createApiRequest($this->orderId, $this->orderInfo);
            $data['payment_method'] = $ul_prefix;

            if (in_array($ul_prefix, ['PIX', 'BOLETO'])) {
                $docNumber                    = $_REQUEST['unlimit_custom']['docnumber'];
                $data['customer']['identity'] = $docNumber;
                if ( ! empty($_REQUEST['unlimit_custom']['postcode'])) {
                    $data['merchant_order']['shipping_address']['zip'] = $_REQUEST['unlimit_custom']['postcode'];
                }
            }

            if ($ul_prefix == 'MBWAY') {
                $data['ewallet_account']['id'] = $this->orderInfo['telephone'];
            }

            if ($ul_prefix == 'GOOGLEPAY') {
                $data['payment_data']['encrypted_data'] =
                    base64_encode($_REQUEST["cardpay_custom_gpay"]["signature"]);
            }

            $data['customer']['full_name'] = $this->orderInfo['firstname'] . ' ' . $this->orderInfo['lastname'];

            return $this->createUrl($data);
        } catch (\Exception $e) {
            $this->exceptionCatch($e);

            return null;
        }
    }

    /**
     * @param  array  $orderInfo
     *
     * @return array
     */
    protected function getPostCode(array $orderInfo): array
    {
        if (static::UL_PREFIX !== 'ticket') {
            return [];
        }

        $data = [];

        $zip                      = $this->getZip($orderInfo);
        $data['payment_postcode'] = (preg_match('/^\d{8}$/', $zip)) ? $zip : '';

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
