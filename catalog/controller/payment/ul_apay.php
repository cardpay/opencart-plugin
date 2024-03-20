<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";

class ULApay extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_APAY = 'extension/unlimit/payment/ul_apay';
    public const UL_PREFIX = 'apay';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_APAY);
        $data['payment_title'] = $this->config->get('payment_ul_apay_payment_title');
        $data['merchant_id'] = $this->config->get('payment_ul_apay_merchant_id');
        $data['currency'] = $this->session->data['currency'];
        $data['store_name'] = $this->config->get('config_name');
        $data['validatemerchant_url'] = $this->config->get('site_url') .
            'index.php?route=extension/unlimit/payment/ul_apay.validatemerchant';

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_APAY
        )) {
            return $this->load->view(
                $this->config->get('config_template') .
                '/template/' . self::EXTENSION_PAYMENT_UL_APAY,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_APAY, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_APAY);

        if (isset($_REQUEST['unlimit_custom']['ulApayNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulApayNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $this->getPaymentData(self::UL_CODES['APAY']);
    }

    public function validatemerchant(): void
    {
        $postData = array(
            'merchantIdentifier' => $_POST['merchantIdentifier'],
            'displayName' => $_POST['displayName'],
            'domainName' => gethostname(),
            'initiative' => 'web',
            'initiativeContext' => gethostname()
        );
        $postDataFields = json_encode($postData);
        $url = $_POST['url'];
        $file_path = dirname(__FILE__) . '/../../../uploads/';
        $merchant_crt = $this->config->get('payment_ul_apay_merchant_certificate');
        $merchant_key = $this->config->get('payment_ul_apay_merchant_key');
        try {
            $curlOptions = array(
                CURLOPT_URL => $url ? $url : 'https://apple-pay-gateway.apple.com/paymentservices/paymentSession',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postDataFields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSLCERT => $file_path . $merchant_crt,
                CURLOPT_SSLKEY => $file_path . $merchant_key,
                CURLOPT_SSLCERTPASSWD => '',
                CURLOPT_SSLKEYTYPE => 'PEM',
                CURLOPT_SSL_VERIFYPEER => true
            );
            $curlConnection = curl_init();
            curl_setopt_array($curlConnection, $curlOptions);
            $response = curl_exec($curlConnection);
            print_r($response);
        } catch (\Exception $e) {
            $this->writeLog(
                'apay',
                __FUNCTION__ .
                $e->getMessage()
            );
        }
        die;
    }
}
