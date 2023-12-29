<?php

use PHPUnit\Framework\TestCase;

class AdminUlBoletoTest extends TestCase
{
    public function testAdminBoletoEnglishPhrases(): void
    {
        include_once __DIR__ . '/../../../../../../../admin/language/en-gb/extension/payment/ul_ticket.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['text_payment']);
        $this->assertNotEmpty($_['text_success']);
        $this->assertNotEmpty($_['text_argentina']);
        $this->assertNotEmpty($_['text_brasil']);
        $this->assertNotEmpty($_['text_colombia']);
        $this->assertNotEmpty($_['text_chile']);
        $this->assertNotEmpty($_['tab_setting']);
        $this->assertNotEmpty($_['tab_order_status']);
        $this->assertNotEmpty($_['entry_installments']);
        $this->assertNotEmpty($_['entry_status']);
        $this->assertNotEmpty($_['entry_production']);
        $this->assertNotEmpty($_['entry_country']);
        $this->assertNotEmpty($_['entry_type_checkout']);
        $this->assertNotEmpty($_['entry_category']);
        $this->assertNotEmpty($_['entry_order_status']);
        $this->assertNotEmpty($_['entry_order_status_general']);
        $this->assertNotEmpty($_['entry_order_status_completed']);
        $this->assertNotEmpty($_['entry_order_status_pending']);
        $this->assertNotEmpty($_['entry_order_status_canceled']);
        $this->assertNotEmpty($_['entry_order_status_in_process']);
        $this->assertNotEmpty($_['entry_order_status_rejected']);
        $this->assertNotEmpty($_['entry_order_status_refunded']);
        $this->assertNotEmpty($_['entry_order_status_in_mediation']);
        $this->assertNotEmpty($_['entry_order_status_chargeback']);
        $this->assertNotEmpty($_['entry_terminal_code']);
        $this->assertNotEmpty($_['entry_terminal_password']);
        $this->assertNotEmpty($_['entry_test_environment']);
        $this->assertNotEmpty($_['entry_callback_secret']);
        $this->assertNotEmpty($_['entry_payment_title']);
        $this->assertNotEmpty($_['entry_log_to_file']);
        $this->assertNotEmpty($_['entry_new_payment_status']);
        $this->assertNotEmpty($_['entry_payment_processing']);
        $this->assertNotEmpty($_['entry_payment_declined']);
        $this->assertNotEmpty($_['entry_payment_authorized']);
        $this->assertNotEmpty($_['entry_payment_completed']);
        $this->assertNotEmpty($_['entry_payment_cancelled']);
        $this->assertNotEmpty($_['entry_payment_charged_back']);
        $this->assertNotEmpty($_['entry_payment_chargeback_resolved']);
        $this->assertNotEmpty($_['entry_payment_refunded']);
        $this->assertNotEmpty($_['entry_payment_voided']);
        $this->assertNotEmpty($_['entry_payment_terminated']);
        $this->assertNotEmpty($_['entry_payment_status']);
        $this->assertNotEmpty($_['error_permission']);
        $this->assertNotEmpty($_['error_client_id']);
        $this->assertNotEmpty($_['error_client_secret']);
        $this->assertNotEmpty($_['error_sponsor_span']);
        $this->assertNotEmpty($_['help_terminal_password']);
        $this->assertNotEmpty($_['help_test_environment']);
        $this->assertNotEmpty($_['help_status']);
        $this->assertNotEmpty($_['entry_notification_url']);
        $this->assertNotEmpty($_['entry_autoreturn']);
        $this->assertNotEmpty($_['entry_client_id']);
        $this->assertNotEmpty($_['entry_client_secret']);
        $this->assertNotEmpty($_['entry_sandbox']);
        $this->assertNotEmpty($_['entry_access_token']);
        $this->assertNotEmpty($_['error_public_key']);
        $this->assertNotEmpty($_['error_access_token']);

        $this->assertNotEmpty($_['heading_title']);
        $this->assertNotEmpty($_['text_ul_ticket']);
    }
}
