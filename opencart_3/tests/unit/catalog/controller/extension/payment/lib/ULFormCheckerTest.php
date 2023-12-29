<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../../catalog/controller/extension/payment/lib/ul_checker.php';

class ULFormCheckerTest extends TestCase
{
    private ULFormChecker $uLFormChecker;

    private array $dataToTest = [
        'cardNumber' => '4000000000000002',
        'cardholderName' => 'John Smith',
        'cardExpirationMonth' => 10,
        'cardExpirationYear' => 2200,
        'securityCode' => 123,
        'docnumber' => '12345678901',
        'installments' => 100,
    ];

    protected function setUp(): void
    {
        $this->uLFormChecker = new ULFormChecker('en');
    }

    public function testWithUsingTheTestingData(): void
    {
        foreach ($this->dataToTest as $field => $fieldValue) {
            $errors = $this->uLFormChecker->check([
                $field => $fieldValue
            ]);

            $this->assertEmpty($errors);
        }
    }
}
