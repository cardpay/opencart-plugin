<?php

use PHPUnit\Framework\TestCase;

class AdminUlCardTest extends TestCase
{
    public function testAdminCardEnglishPhrases(): void
    {
        include_once __DIR__ . '/../../../../../../../admin/language/en-gb/extension/payment/ul_card.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['heading_title']);
        $this->assertNotEmpty($_['text_ul_card']);
        $this->assertNotEmpty($_['entry_payments_not_accept']);
        $this->assertNotEmpty($_['entry_url']);
        $this->assertNotEmpty($_['entry_capture_payment']);
        $this->assertNotEmpty($_['entry_installment_enabled']);
        $this->assertNotEmpty($_['entry_ask_cpf']);
        $this->assertNotEmpty($_['entry_dynamic_descriptor']);
        $this->assertNotEmpty($_['error_access_token']);
        $this->assertNotEmpty($_['error_public_key']);
        $this->assertNotEmpty($_['help_capture_payment']);
        $this->assertNotEmpty($_['help_installment_enabled']);
        $this->assertNotEmpty($_['18']);
        $this->assertNotEmpty($_['15']);
        $this->assertNotEmpty($_['12']);
        $this->assertNotEmpty($_['9']);
        $this->assertNotEmpty($_['6']);
        $this->assertNotEmpty($_['3']);
        $this->assertNotEmpty($_['1']);
        $this->assertNotEmpty($_['ul_button_cancel']);
        $this->assertNotEmpty($_['ul_button_capture']);
        $this->assertNotEmpty($_['ul_button_refund']);
        $this->assertNotEmpty($_['ul_q01']);
        $this->assertNotEmpty($_['ul_q02']);
        $this->assertNotEmpty($_['ul_q03']);
        $this->assertNotEmpty($_['ul_q04']);
        $this->assertNotEmpty($_['ul_q05']);
        $this->assertNotEmpty($_['ul_q06']);
        $this->assertNotEmpty($_['ul_q07']);
        $this->assertNotEmpty($_['ul_q08']);
        $this->assertNotEmpty($_['ul_q09']);
        $this->assertNotEmpty($_['ul_q10']);
        $this->assertNotEmpty($_['ul_q11']);
        $this->assertNotEmpty($_['ul_product']);
        $this->assertNotEmpty($_['ul_model']);
        $this->assertNotEmpty($_['ul_price']);
        $this->assertNotEmpty($_['ul_quantity']);
        $this->assertNotEmpty($_['ul_amount']);
        $this->assertNotEmpty($_['ul_refund']);
        $this->assertNotEmpty($_['ul_restock']);
        $this->assertNotEmpty($_['ul_already']);
        $this->assertNotEmpty($_['ul_available']);
        $this->assertNotEmpty($_['ul_re_amount']);
        $this->assertNotEmpty($_['ul_re_reason']);
        $this->assertNotEmpty($_['ul_totals']);
        $this->assertNotEmpty($_['ul_cancel']);
        $this->assertNotEmpty($_['ajax_form_e1']);
        $this->assertNotEmpty($_['ajax_form_e2']);
    }
}
