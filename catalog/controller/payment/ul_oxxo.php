<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";

use Unlimit\ULUtil;
use Unlimit\UnlimitException;

class ULOxxo extends ULAltGateway
{
    public const EXTENSION_PAYMENT_UL_OXXO = 'extension/unlimit/payment/ul_oxxo';
    public const UL_PREFIX = 'oxxo';

    public function index(): string
    {
        $data = $this->getData(self::EXTENSION_PAYMENT_UL_OXXO);
        $data['payment_title'] = $this->config->get('payment_ul_oxxo_payment_title');
        $data['is_oxxo_payment_page_required'] =
            $this->config->get('payment_ul_oxxo_payment_page') === ULUtil::ACCESS_MODE_PP;

        if (file_exists(
            DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_OXXO
        )) {
            return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_OXXO,
                $data
            );
        }

        return $this->load->view(self::EXTENSION_PAYMENT_UL_OXXO, $data);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->load->language(self::EXTENSION_PAYMENT_UL_OXXO);

        if (isset($_REQUEST['unlimit_custom']['ulOxxoNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulOxxoNumber'];
        }
        try {
            $total = $this->orderInfo['total'] ?? $this->amount;
            $formattedTotal = (float)$this->currency->format(
                $total,
                $this->session->data['currency'],
                0,
                false
            );
            if ($formattedTotal > 99999.99) {
                throw new UnlimitException($this->language->get('error_invalid_oxxo_amount'));
            }
        } catch (UnlimitException $e) {
            $this->exceptionCatch($e, self::UL_PREFIX, ULUtil::ACCESS_MODE_PP);
            if ($this->config->get('payment_ul_oxxo_payment_page') == ULUtil::ACCESS_MODE_PP) {
                $this->response->setOutput(json_encode($this->getResponse(false)));
            }

            return;
        }

        $cleanedPhone = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

        $url = $this->getPaymentData(self::UL_CODES['OXXO']);
        $json = $this->getResponse($url);
        $this->response->setOutput(json_encode($json));
    }
}
