<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

use Opencart\Catalog\Controller\Extension\Unlimit\Payment\ULAltGateway;

class ULPix extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_PIX = 'extension/unlimit/payment/ul_pix';
    public const UL_PREFIX = 'pix';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_PIX);
        $data['payment_title'] = $this->config->get('payment_ul_pix_payment_title');

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_PIX, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_PIX);

        if (isset($_REQUEST['unlimit_custom']['ulPixNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulPixNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData(self::UL_CODES['PIX']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));

    }
}
