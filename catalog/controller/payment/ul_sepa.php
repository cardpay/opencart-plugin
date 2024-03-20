<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

class ULSepa extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_SEPA = 'extension/unlimit/payment/ul_sepa';
    public const UL_PREFIX = 'sepa';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_SEPA);
        $data['payment_title'] = $this->config->get('payment_ul_sepa_payment_title');
		$data['is_sepa_payment_page_required'] =
            $this->config->get('payment_ul_sepa_payment_page') === ULUtil::ACCESS_MODE_PP;

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_SEPA
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_SEPA,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_SEPA, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_SEPA);

        if (isset($_REQUEST['unlimit_custom']['ulSepaNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulSepaNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData(self::UL_CODES['SEPA']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));

    }
}
