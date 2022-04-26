<?php

require_once '../catalog/controller/extension/payment/lib/unlimint.php';
require_once '../catalog/controller/extension/payment/lib/ul_util.php';
require_once '../admin/controller/extension/payment/ul_payment.php';

class ControllerExtensionPaymentULTicket extends ControllerExtensionPaymentUl
{
    private const USER_TOKEN = 'user_token=';
    public const CODE = 'payment_ul_ticket_';
    private const EXTENSION_PAYMENT_UL_TICKET = 'extension/payment/ul_ticket';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load_common_header(self::EXTENSION_PAYMENT_UL_TICKET, self::USER_TOKEN);
    }

    public function index()
    {
        $posts_fields = [
            'status',
            'terminal_code',
            'terminal_password',
            'callback_secret',
            'test_environment',
            'payment_title',
            'log_to_file',
            'new_status',
            'processing',
            'authorized',
            'cancelled',
            'declined',
            'charged_back',
            'completed',
            'chargeback_resolved',
        ];

        $data = $this->load_common_footer($posts_fields);

        $this->response->setOutput($this->load->view(self::EXTENSION_PAYMENT_UL_TICKET, $data));
    }
}
