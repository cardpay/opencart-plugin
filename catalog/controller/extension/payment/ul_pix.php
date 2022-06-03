<?php

require_once __DIR__ . "/lib/unlimint.php";
require_once __DIR__ . "/lib/ul_util.php";
require_once __DIR__ . "/ul_general.php";
require_once __DIR__ . "/ul_alt_gateway.php";

class ControllerExtensionPaymentULPix extends ControllerExtensionPaymentULAltGateway
{
    public const EXTENSION_PAYMENT_UL_PIX = 'extension/payment/ul_pix';
    public const UL_PREFIX = 'pix';

    public function index()
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_PIX);

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX)) {
            return $this->load->view($this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX, $data);
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_PIX, $data);
    }

    public function payment()
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_PIX);

        $this->getPaymentData('PIX');
    }
}
