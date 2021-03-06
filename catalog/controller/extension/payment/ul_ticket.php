<?php

require_once __DIR__ . "/lib/unlimint.php";
require_once __DIR__ . "/lib/ul_util.php";
require_once __DIR__ . "/ul_general.php";
require_once __DIR__ . "/ul_alt_gateway.php";

class ControllerExtensionPaymentULTicket extends ControllerExtensionPaymentULAltGateway
{
    public const EXTENSION_PAYMENT_UL_TICKET = 'extension/payment/ul_ticket';
    public const UL_PREFIX = 'ticket';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_TICKET);
        $data['payment_title'] = $this->config->get('payment_ul_ticket_payment_title');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_TICKET)) {
            return $this->load->view($this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_TICKET, $data);
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_TICKET, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_TICKET);

        $this->getPaymentData('BOLETO');
    }
}
