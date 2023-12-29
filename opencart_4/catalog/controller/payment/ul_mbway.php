<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

class ULMbway extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_MBWAY = 'extension/unlimit/payment/ul_mbway';
    public const UL_PREFIX = 'mbway';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_MBWAY);
        $data['payment_title'] = $this->config->get('payment_ul_mbway_payment_title');
		$data['is_mbway_payment_page_required'] =
            $this->config->get('payment_ul_mbway_payment_page') === ULUtil::ACCESS_MODE_PP;

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_MBWAY)
        ) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_MBWAY,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_MBWAY, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_MBWAY);

        if (isset($_REQUEST['unlimit_custom']['ulMbwayNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulMbwayNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData('MBWAY');
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));

    }
}
