<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";

class ULGpay extends ULAltGateway
{
	public const EXTENSION_PAYMENT_UL_GPAY = 'extension/unlimit/payment/ul_gpay';
	public const UL_PREFIX = 'gpay';

	public function index(): string
	{
		$data                  = $this->getData(self::EXTENSION_PAYMENT_UL_GPAY);
		$data['payment_title'] = $this->config->get('payment_ul_gpay_payment_title');
		$data['merchant_id']   = $this->config->get('payment_ul_gpay_merchant_id') . ' ' . $this->session->data['currency'];

		if (file_exists(
			DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_GPAY
		)) {
			return $this->load->view($this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_GPAY,
				$data);
		}

		return $this->load->view(self::EXTENSION_PAYMENT_UL_GPAY, $data);
	}

	/**
	 * @throws JsonException
	 */
	public function payment(): void
	{
		$this->load->language(self::EXTENSION_PAYMENT_UL_GPAY);

        if (isset($_REQUEST['unlimit_custom']['ulGpayNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulGpayNumber'];
        }

        $cleanedPhone                 = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;

		$this->getPaymentData('GOOGLEPAY');
	}
}
