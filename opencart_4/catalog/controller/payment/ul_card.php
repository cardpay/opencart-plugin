<?php

namespace Opencart\Catalog\Controller\Extension\Unlimit\Payment;

use Cart\Customer;
use Unlimit\ULFormChecker;
use Unlimit\ULUtil;

require_once __DIR__ . "/ul_general.php";
require_once DIR_EXTENSION . 'unlimit/system/library/ul_checker.php';

/**
 * @property Customer $customer
 * @property Language $language
 * @property Loader $load
 * @property Url $url
 */
class ULCard extends ULGeneral
{
    public const CHECKOUT_ORDER = 'checkout/order';
    public const CHECKOUT_CART = 'checkout/checkout';
    public const UL_PREFIX = 'card';
    private const INSTALLMENTS_MAX = 12;

    public const UL_CARD_LANGUAGE_KEY = 'extension/unlimit/payment/ul_card';

    /**
     * index
     *
     * @return mix
     */
    public function index(): string
    {
        $data['customer_email']      = $this->customer->getEmail();
        $data['button_confirm']      = $this->language->get('button_confirm');
        $data['button_back']         = $this->language->get('button_back');
        $data['analytics']           = $this->setPreModuleAnalytics();
        $data['terms']               = '';
        $data['public_key']          = $this->config->get('payment_ul_card_public_key');
        $data['site_id']             = $this->config->get('payment_ul_card_country');
        $data['max_installments']    = self::INSTALLMENTS_MAX;
        $data['payment_title']       = $this->config->get('payment_ul_card_payment_title');
        $data['ask_cpf']             = $this->config->get('payment_ul_card_ask_cpf');
        $data['installment_enabled'] = $this->config->get('payment_ul_card_installment_enabled');
        $data['installments']        = $this->build_installment_options($this->orderInfo['total']);

        $this->load->model(self::CHECKOUT_ORDER);
        $orderInfo                = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['payment_postcode'] = $this->getZip($orderInfo);

        $transaction_amount = (float)$orderInfo['total'] * (float)$orderInfo['currency_value'];
        $data['amount']     = $transaction_amount;
        $data['actionForm'] = $this->url->link(self::UL_CARD_LANGUAGE_KEY . '.payment');

        if ($this->config->get('payment_ul_card_country')) {
            $data['action'] = $this->config->get('payment_ul_card_country');
        }
        $data['telephone_display']  = $this->config->get('config_telephone_display');
        $data['telephone_required'] = $this->config->get('config_telephone_required');

        $this->load->model(self::CHECKOUT_ORDER);
        $this->load->language(self::UL_CARD_LANGUAGE_KEY);

        //populate labels
        $labels = [
            'cucredit_card_number',
            'cucard_holder_name',
            'cuexpiration_date',
            'cusecurity_code',
            'cuinstallments',
            'cudocument_number',
            "cunumofinstallments",
            'cuended_in',
            'cubtn_pay',
            'cue205',
            'cueE301',
            'cue208',
            'cue209',
            'cue325',
            'cue326',
            'cue221',
            'cue316',
            'cue224',
            'cueE302',
            'cueE203',
            'cue212',
            'cue322',
            'cue214',
            'cue324',
            'cueE324',
            'cue213',
            'cue323',
            'cue220',
            'cueEULTY'
        ];

        foreach ($labels as $label) {
            $data[$label] = $this->language->get($label);
        }

        $data['server']                        = $_SERVER;
        $data['debug']                         = $this->config->get('payment_ul_card_debug');
        $data['user_logged']                   = $this->customer->isLogged();
        $data['is_card_payment_page_required'] =
            $this->config->get('payment_ul_card_payment_page') === ULUtil::ACCESS_MODE_PP;

        return $this->load->view(self::UL_CARD_LANGUAGE_KEY, $data);
    }

    /**
     * @param  float  $total_amount
     *
     * @return array
     */
    private function build_installment_options(float $total_amount): array
    {
        $options = [];

        $installments_range = $this->get_installments_range(
            $this->config->get('payment_ul_card_maximum_accepted_installments')
        );

        $minimum_installment_amount = (float)$this->config->get('payment_ul_card_minimum_installment_amount');

        foreach ($installments_range as $installments) {
            $amount = $total_amount / $installments;
            if (
                ($amount < $minimum_installment_amount) &&
                ($installments > 1) &&
                ($minimum_installment_amount > 0)) {
                break;
            }
            $options[] = [
                'installments' => $installments,
                'amount'       => $installments . ' x ' . $this->format_amount($total_amount / $installments)
            ];
        }

        return $options;
    }

    public function get_installments_range($settings)
    {
        $result = [];

        $range = $this->get_allowed_installment_range();

        foreach (explode(',', trim($settings)) as $value) {
            if (strpos($value, '-') !== false) {
                $value = explode('-', $value);
                if (count($value) !== 2) {
                    continue;
                }
                for ($i = (int)$value[0]; $i <= ((int)$value[1]); $i++) {
                    $this->append_installment_option($result, $i, $range);
                }
            } else {
                $this->append_installment_option($result, (int)$value, $range);
            }
        }

        return $this->normalize_installment_array($result);
    }

    private function append_installment_option(&$result, $value, $range)
    {
        if (in_array($value, $range)) {
            $result[] = $value;
        }
    }

    private function normalize_installment_array($array)
    {
        $array[] = 1;
        $result  = array_unique($array);
        sort($result);

        return (empty($result)) ? [1] : $result;
    }

    private function get_allowed_installment_range()
    {
        if ($this->config->get('payment_ul_card_installment_type') === 'MF_HOLD') {
            return [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        } else {
            return [3, 6, 9, 12, 18];
        }
    }

    /**
     * @param  float  $amount
     *
     * @return string
     */
    private function format_amount(float $amount): string
    {
        if ($amount === 0.0) {
            return '0';
        }

        return number_format($amount, 2);
    }

    /**
     * @throws JsonException
     */
    public function payment(): void
    {
        $this->language->load(self::UL_CARD_LANGUAGE_KEY);

        try {
            $this->load->model(self::CHECKOUT_ORDER);

            $card_post_fields = $_REQUEST['unlimit_custom'];
            if (isset($_REQUEST['unlimit_custom']['ulCardNumber'])) {
                $this->orderInfo['telephone'] = $_REQUEST['unlimit_custom']['ulCardNumber'];
            }

            $api_request = $this->get_instance_ul_util()->createApiRequest($this->orderId, $this->orderInfo);

            $yes                      = '1';
            $are_installments_enabled = $yes === $this->config->get('payment_ul_card_installment_enabled');
            $installments             = isset($card_post_fields['installments'])
                ? (int)$card_post_fields['installments'] : 1;

            $this->handleInstallments($api_request, $are_installments_enabled, $installments, $card_post_fields);
            $this->handlePaymentData($api_request);
            $this->handlePreauth($api_request);
            $this->handleInstallmentAmount($api_request, $are_installments_enabled, $installments);
            $this->handleDynamicDescriptor($api_request);
            $this->handleCpf($api_request, $yes, $card_post_fields);
            $this->handleApiAccessMode($api_request, $card_post_fields);

            $cleanedPhone                     = $this->validatePhone($this->orderInfo['telephone']);
            $api_request['customer']['phone'] = $cleanedPhone;

            $checker = new ULFormChecker($this->language);
            $errors  = $checker->check($card_post_fields);

            if ( ! empty($errors)) {
                $this->session->data['error'] = implode('<br>', $errors);
                $this->response->redirect($this->url->link(self::CHECKOUT_CART, '', true));
            }

            $url  = $this->createUrl($api_request);
            $json = $this->getResponse($url);
            $this->response->setOutput(json_encode($json));

        } catch (\Exception $e) {
            $this->exceptionCatch($e);
        }
    }

    private function handleInstallments(&$api_request, $are_installments_enabled, $installments, $card_post_fields)
    {
        if ($are_installments_enabled && isset($card_post_fields['installments'])) {
            $additionalData = [
                'installment_type' => $this->config->get('payment_ul_card_installment_type'),
                'installments'     => $installments,
            ];

            $api_request['payment_data'] = array_merge($api_request['payment_data'] ?? [], $additionalData);
        }
    }

    private function handlePaymentData(&$api_request)
    {
        $api_request['payment_method']           = 'BANKCARD';
        $api_request['payment_data']['currency'] = $this->session->data['currency'];
    }

    private function handlePreauth(&$api_request)
    {
        $no         = '0';
        $is_preauth = $no === $this->config->get('payment_ul_card_capture_payment');
        if ($is_preauth) {
            $api_request['payment_data']['preauth'] = true;
        }
    }

    private function handleInstallmentAmount(&$api_request, $are_installments_enabled, $installments)
    {
        if ($are_installments_enabled &&
            $this->config->get('payment_ul_card_installment_type') == 'IF' && $installments > 1) {
            $no         = '0';
            $is_preauth = ($no === $this->config->get('payment_ul_card_capture_payment'));
            if ($is_preauth) {
                unset($api_request['payment_data']['preauth']);
            }
            $amount                                            =
                round($api_request['payment_data']['amount'] / $installments,
                    2);
            $api_request['payment_data']['installment_amount'] = $amount;
        }
    }

    private function handleDynamicDescriptor(&$api_request)
    {
        $dynamic_descriptor = $this->config->get('payment_ul_card_dynamic_descriptor');
        if ( ! empty($dynamic_descriptor)) {
            $api_request['payment_data']['dynamic_descriptor'] = $dynamic_descriptor;
        }
    }

    private function handleCpf(&$api_request, $yes, $card_post_fields)
    {
        $is_cpf_required = $yes === $this->config->get('payment_ul_card_ask_cpf');
        if ($is_cpf_required && ! empty($card_post_fields['cpf'])) {
            $api_request['customer']['identity'] = $card_post_fields['cpf'];
        }
    }

    private function handleApiAccessMode(&$api_request, $card_post_fields)
    {
        $cleanedPhone                     = $this->validatePhone($this->orderInfo['telephone']);
        $api_request['customer']['phone'] = $cleanedPhone;

        $gateway            = '0';
        $is_api_access_mode = ($gateway === $this->config->get('payment_ul_card_payment_page'));

        if ($is_api_access_mode) {
            $api_request['card_account']['card'] = [
                'pan'           => str_replace(' ', '', $card_post_fields['cardNumber']),
                'holder'        => $card_post_fields['cardholderName'],
                'expiration'    => substr_replace($card_post_fields['cardExpirationDate'], '20', 3, 0),
                'security_code' => $card_post_fields['securityCode'],
            ];

            $shippingAddress = $this->session->data['shipping_address'] ?? null;
            if ($shippingAddress !== null) {
                $api_request['card_account'] += [
                    'billing_address' => [
                        'country'     => $shippingAddress['iso_code_2'] ?? null,
                        'state'       => $shippingAddress['zone_code'] ?? null,
                        'zip'         => $shippingAddress['postcode'] ?? null,
                        'city'        => $shippingAddress['city'] ?? null,
                        'phone'       => $cleanedPhone,
                        'addr_line_1' => $shippingAddress['address_1'] ?? null,
                        'addr_line_2' => $shippingAddress['address_2'] ?? null,
                    ],
                ];
            }
        }
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    /**
     * @param  string  $at
     *
     * @return string
     */
    public function _getClientId(string $at): string
    {
        $t = explode("-", $at);
        if ( ! empty($t)) {
            return $t[1];
        }

        return "";
    }
}
