<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

class ULPaypal extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_PAYPAL = 'extension/unlimit/payment/ul_paypal';
    public const UL_PREFIX = 'paypal';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_PAYPAL);
        $data['payment_title'] = $this->config->get('payment_ul_paypal_payment_title');
		$data['is_paypal_payment_page_required'] =
            $this->config->get('payment_ul_paypal_payment_page') === ULUtil::ACCESS_MODE_PP;

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PAYPAL
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PAYPAL,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_PAYPAL, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_PAYPAL);

        if (isset($_REQUEST['unlimit_custom']['ulPaypalNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulPaypalNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData(self::UL_CODES['PAYPAL']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));

    }
}
