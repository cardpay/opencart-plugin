<?php

use PHPUnit\Framework\TestCase;

class CatalogUlPixTest extends TestCase
{
    public function testCatalogPixEnglishPhrases()
    {
        include_once __DIR__ . '/../../../../../../../catalog/language/en-gb/extension/payment/ul_pix.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['text_title']);
        $this->assertNotEmpty($_['label_post_code']);
        $this->assertNotEmpty($_['error_invalid_post_code']);
    }
}
