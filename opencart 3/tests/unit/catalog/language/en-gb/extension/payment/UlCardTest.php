<?php

use PHPUnit\Framework\TestCase;

class CatalogUlCardTest extends TestCase
{
    public function testCatalogCardEnglishPhrases(): void
    {
        include_once __DIR__ . '/../../../../../../../catalog/language/en-gb/extension/payment/ul_card.php';

        $this->assertNotEmpty($_);

        $this->assertNotEmpty($_['text_title']);
        $this->assertNotEmpty($_['cue205']);
        $this->assertNotEmpty($_['cueE301']);
        $this->assertNotEmpty($_['cue208']);
        $this->assertNotEmpty($_['cue209']);
        $this->assertNotEmpty($_['cue221']);
        $this->assertNotEmpty($_['cue316']);
        $this->assertNotEmpty($_['cue224']);
        $this->assertNotEmpty($_['cueE302']);
        $this->assertNotEmpty($_['cue322']);
        $this->assertNotEmpty($_['cue324']);
        $this->assertNotEmpty($_['cueE324']);
        $this->assertNotEmpty($_['cue220']);
    }
}
