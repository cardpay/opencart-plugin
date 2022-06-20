<?php

use PHPUnit\Framework\TestCase;

class AdminUlPixTest extends TestCase
{
    public function testAdminPixEnglishPhrases(): void
    {
        include_once __DIR__ . '/../../../../../../../admin/language/en-gb/extension/payment/ul_pix.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['heading_title']);
        $this->assertNotEmpty($_['text_ul_pix']);
    }
}
