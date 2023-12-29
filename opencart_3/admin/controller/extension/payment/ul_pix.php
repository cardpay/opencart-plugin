<?php

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/lib/unlimint.php';
require_once __DIR__ . '/../../../../catalog/controller/extension/payment/lib/ul_util.php';
require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_payment.php';

class ControllerExtensionPaymentUlPix extends ControllerExtensionPaymentUl
{
    public const CODE = 'payment_ul_pix_';
    private const EXTENSION_PAYMENT_UL_PIX = 'extension/payment/ul_pix';

    /**
     * @throws UnlimintException
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->loadCommonHeader(self::EXTENSION_PAYMENT_UL_PIX);
    }

    public function index(): void
    {
        $this->response->setOutput(
            $this->load->view(
                self::EXTENSION_PAYMENT_UL_PIX,
                $this->loadCommonFooter(self::POST_FIELDS)
            )
        );
    }
}
