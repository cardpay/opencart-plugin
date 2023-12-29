<?php

use PHPUnit\Framework\TestCase;

class CatalogUlBoletoTest extends TestCase
{
    public function testCatalogBoletoEnglishPhrases(): void
    {
        include_once __DIR__ . '/../../../../../../../catalog/language/en-gb/extension/payment/ul_ticket.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['currency_no_support']);
        $this->assertNotEmpty($_['expiration_date_placeholder']);
        $this->assertNotEmpty($_['name_placeholder']);
        $this->assertNotEmpty($_['doctype_placeholder']);
        $this->assertNotEmpty($_['docnumber_placeholder']);
        $this->assertNotEmpty($_['expiration_month_placeholder']);
        $this->assertNotEmpty($_['expiration_year_placeholder']);
        $this->assertNotEmpty($_['error_invalid_payment_type']);
        $this->assertNotEmpty($_['installments_placeholder']);
        $this->assertNotEmpty($_['issuer_placeholder']);
        $this->assertNotEmpty($_['cardType_placeholder']);
        $this->assertNotEmpty($_['payment_processing']);
        $this->assertNotEmpty($_['payment_title']);
        $this->assertNotEmpty($_['payment_button']);
        $this->assertNotEmpty($_['S200']);
        $this->assertNotEmpty($_['S2000']);
        $this->assertNotEmpty($_['S400']);
        $this->assertNotEmpty($_['label_cpf']);
        $this->assertNotEmpty($_['error_invalid_cpf']);
        $this->assertNotEmpty($_['error_empty_cpf']);
        $this->assertNotEmpty($_['label_post_code']);
        $this->assertNotEmpty($_['error_invalid_post_code']);
        $this->assertNotEmpty($_['select_one']);

        $this->assertNotEmpty($_['text_title']);
    }
}
