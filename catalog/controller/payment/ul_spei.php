<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";

use Unlimit\ULUtil;

class ULSpei extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_SPEI = 'extension/unlimit/payment/ul_spei';
    public const UL_PREFIX = 'spei';

    public function index(): string
    {
        $data                                  = $this->getData(self::EXTENSION_PAYMENT_UL_SPEI);
        $data['payment_title']                 = $this->config->get('payment_ul_spei_payment_title');
        $data['is_spei_payment_page_required'] =
            $this->config->get('payment_ul_spei_payment_page') === ULUtil::ACCESS_MODE_PP;

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_SPEI
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_SPEI,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_SPEI, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_SPEI);

        if (isset($_REQUEST['unlimit_custom']['ulSpeiNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulSpeiNumber'];
        }

        $cleanedPhone                 = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url  = $this->getPaymentData(self::UL_CODES['SPEI']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));
    }
}
