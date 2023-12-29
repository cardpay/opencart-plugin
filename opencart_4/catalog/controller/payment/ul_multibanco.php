<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

require_once __DIR__ . "/ul_alt_gateway.php";
use Unlimit\ULUtil;

class ULMultibanco extends ULAltGateway
{
	public const EXTENSION_PAYMENT_UL_MULTIBANCO = 'extension/unlimit/payment/ul_multibanco';
	public const UL_PREFIX = 'multibanco';

	public function index(): string
	{
		$data                  = $this->getData(self::EXTENSION_PAYMENT_UL_MULTIBANCO);
		$data['payment_title'] = $this->config->get('payment_ul_multibanco_payment_title');
		$data['is_multibanco_payment_page_required'] =
            $this->config->get('payment_ul_multibanco_payment_page') === ULUtil::ACCESS_MODE_PP;

		if (file_exists(
			DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_MULTIBANCO
		)) {
			return $this->load->view(
                $this->config->get('config_template') . '/template/' . self::EXTENSION_PAYMENT_UL_MULTIBANCO,
				$data
            );
		}

		return $this->load->view(self::EXTENSION_PAYMENT_UL_MULTIBANCO, $data);
	}

	/**
	 * @throws JsonException
	 */
	public function payment(): void
	{
		$this->load->language(self::EXTENSION_PAYMENT_UL_MULTIBANCO);

        if (isset($_REQUEST['unlimit_custom']['ulMultibancoNumber'])) {
            $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulMultibancoNumber'];
        }

        $cleanedPhone                 = $this->validatePhone($this->orderInfo['telephone']);
        $this->orderInfo['telephone'] = $cleanedPhone;
        $url = $this->getPaymentData('MULTIBANCO');
        $json = $this->getResponse($url);
		$this->response->setOutput(json_encode($json));
	}
}
