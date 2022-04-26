<?php

require_once "lib/unlimint.php";
require_once "lib/ul_util.php";
require_once "lib/ul_checker.php";
require_once "ul_general.php";

class ControllerExtensionPaymentULCard extends ControllerExtensionPaymentULGeneral
{
    public const CHECKOUT_ORDER = 'checkout/order';
    public const CHECKOUT_CHECKOUT = 'checkout/checkout';
    public const UL_PREFIX = 'card';
    private const INSTALLMENTS_MIN = 1;
    private const INSTALLMENTS_MAX = 12;

    public function index()
    {
        $data['customer_email'] = $this->customer->getEmail();
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');
        $data['analytics'] = $this->setPreModuleAnalytics();
        $data['terms'] = '';
        $data['public_key'] = $this->config->get('payment_ul_card_public_key');
        $data['site_id'] = $this->config->get('payment_ul_card_country');
        $data['max_installments'] = 12;
        $data['payment_title'] = $this->config->get('payment_ul_card_payment_title');
        $data['ask_cpf'] = $this->config->get('payment_ul_card_ask_cpf');
        $data['installment_enabled'] = $this->config->get('payment_ul_card_installment_enabled');
        $data['installments'] = $this->build_installment_options($this->orderInfo['total']);

        $this->load->model(self::CHECKOUT_ORDER);
        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['payment_postcode'] = $this->getZip($orderInfo);

        $transaction_amount = (float)$orderInfo['total'] * (float)$orderInfo['currency_value'];
        $data['amount'] = $transaction_amount;
        $data['actionForm'] = $orderInfo['store_url'] . 'index.php?route=extension/payment/ul_card/payment';

        if ($this->config->get('payment_ul_card_country')) {
            $data['action'] = $this->config->get('payment_ul_card_country');
        }

        $this->load->model(self::CHECKOUT_ORDER);
        $this->language->load('extension/payment/ul_card');

        //populate labels
        $labels = array(
            'cucredit_card_number', 'cucard_holder_name', 'cuexpiration_date',
            'cusecurity_code', 'cuinstallments', 'cudocument_number', "cunumofinstallments",
            'cuyour_card', 'cuother_cards', 'cuother_card', 'cuended_in',
            'cubtn_pay', 'cue205', 'cueE301', 'cue208', 'cue209', 'cue325',
            'cue326', 'cue221', 'cue316', 'cue224', 'cueE302', 'cueE203', 'cue212',
            'cue322', 'cue214', 'cue324', 'cueE324', 'cue213', 'cue323', 'cue220', 'cueEULTY'
        );

        foreach ($labels as $label) {
            $data[$label] = $this->language->get($label);
        }

        $data['server'] = $_SERVER;
        $data['debug'] = $this->config->get('payment_ul_card_debug');
        $data['user_logged'] = $this->customer->isLogged();
        $view = floatval(VERSION) < 2.2 ? 'default/template/payment/' : 'extension/payment/';

        $view_uri = $view . 'ul_card';

        return $this->load->view($view_uri, $data);
    }

    private function build_installment_options($total_amount)
    {
        $options = [];

        for ($installments = self::INSTALLMENTS_MIN; $installments <= self::INSTALLMENTS_MAX; $installments++) {
            $options[] = [
                'installments' => $installments,
                'amount' => $installments . ' x ' . $this->format_amount($total_amount / $installments)
            ];
        }

        return $options;
    }

    private function format_amount($amount)
    {
        if (empty($amount)) {
            return $amount;
        }

        return number_format($amount, 2);
    }

    public function payment()
    {
        $this->language->load('extension/payment/ul_card');

        try {
            $this->load->model(self::CHECKOUT_ORDER);

            $params_unlimint = $_REQUEST['unlimint_custom'];

            $checker = new ULFormChecker($this->language);
            $errors = $checker->check($params_unlimint);
            if (!empty($errors)) {
                $this->session->data['error'] = join('<br>', $errors);
                $this->response->redirect($this->url->link(self::CHECKOUT_CHECKOUT, '', true));
            }

            $payment_method = 'BANKCARD';
            $capture = ($this->config->get('payment_ul_card_capture_payment') === '1');

            $data = $this->get_instance_ul_util()->createApiRequest($this->orderId, $this->orderInfo, $capture);

            $data['payment_method'] = $payment_method;

            $installments = isset($params_unlimint['installments']) ? (int)$params_unlimint['installments'] : 0;
            if ($installments > 1) {
                $data['recurring_data'] = array(
                    'installment_type' => 'MF_HOLD',
                    'initiator' => 'cit',
                    'interval' => '30',
                    'period' => 'day',
                    'currency' => $this->orderInfo['currency_code'],
                    'amount' => round($this->orderInfo['total'] * $this->orderInfo['currency_value'], 2),
                    'payments' => $installments,
                    'generate_token' => 'true'
                );
                if (!$capture) {
                    $data['recurring_data']['preauth'] = true;
                }
                unset($data['payment_data']);
            }
            $exp = explode('/', $params_unlimint['cardExpirationDate']);
            $expiration_date = $exp[0] . '/20' . $exp[1];
            $data['card_account'] = array(
                'card' => array(
                    'pan' => $params_unlimint['cardNumber'],
                    'holder' => $params_unlimint['cardholderName'],
                    'expiration' => $expiration_date,
                    'security_code' => $params_unlimint['securityCode']
                )
            );
            $docNumber = $params_unlimint['docnumber'] ?? '';
            if (!empty($docNumber)) {
                $data['customer']['identity'] = $docNumber;
            }
            $url = $this->get_instance_ul_util()->processPayment($data, $this->orderInfo, $this->statusId, $this->model_order, $this->instance_ul);
            if ($url !== false) {
                $this->response->redirect($url);
            } else {
                $this->response->redirect($this->url->link(self::CHECKOUT_CHECKOUT, '', true));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => $e->getCode(), "message" => $e->getMessage()));
        }
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    public function _getClientId($at)
    {
        $t = explode("-", $at);
        if (!empty($t)) {
            return $t[1];
        }

        return "";
    }
}
