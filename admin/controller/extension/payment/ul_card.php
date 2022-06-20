<?php

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/lib/unlimint.php';
require_once __DIR__ . '/../../../../catalog/controller/extension/payment/lib/ul_util.php';
require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_payment.php';

/**
 * @property Response $response
 * @property Loader $load
 */
class ControllerExtensionPaymentULCard extends ControllerExtensionPaymentUl
{
    public const CODE = 'payment_ul_card_';
    private const EXTENSION_PAYMENT_UL_CARD = 'extension/payment/ul_card';

    /**
     * @throws UnlimintException
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->loadCommonHeader(self::EXTENSION_PAYMENT_UL_CARD);
    }

    public function index(): void
    {
        $posts_fields = [
            'capture_payment',
            'installment_enabled',
            'installments',
            'ask_cpf',
            'dynamic_descriptor',
            'voided',
            'refunded',
            'terminated',
        ];

        $data = $this->loadCommonFooter($posts_fields);

        $this->response->setOutput($this->load->view(self::EXTENSION_PAYMENT_UL_CARD, $data));
    }
}
