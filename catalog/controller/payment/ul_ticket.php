<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

use Opencart\Catalog\Controller\Extension\Unlimit\Payment\ULAltGateway;

class ULTicket extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_TICKET = 'extension/unlimit/payment/ul_ticket';
    public const UL_PREFIX = 'ticket';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_TICKET);
        $data['payment_title'] = $this->config->get('payment_ul_ticket_payment_title');

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_TICKET
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_TICKET,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_TICKET, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_TICKET);

        if (isset($_REQUEST['unlimit_custom']['ulBoletoNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulBoletoNumber'];
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData(self::UL_CODES['TICKET']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));
    }
}
