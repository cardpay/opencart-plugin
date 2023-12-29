<?php

require_once __DIR__ . "/lib/unlimint.php";
require_once __DIR__ . "/lib/ul_util.php";
require_once __DIR__ . "/ul_general.php";
require_once __DIR__ . "/ul_alt_gateway.php";

class ControllerExtensionPaymentULPix extends ControllerExtensionPaymentULAltGateway
{
    public const EXTENSION_PAYMENT_UL_PIX = 'extension/payment/ul_pix';
    public const UL_PREFIX = 'pix';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_PIX);
        $data['payment_title'] = $this->config->get('payment_ul_pix_payment_title');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX)) {
            return $this->load->view($this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_PIX, $data);
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_PIX, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_PIX);

        $this->getPaymentData('PIX');
    }
}
