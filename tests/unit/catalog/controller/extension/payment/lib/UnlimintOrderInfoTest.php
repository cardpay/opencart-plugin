<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../../catalog/controller/extension/payment/lib/unlimint_order_info.php';

class UnlimintOrderInfoTest extends TestCase
{
    private $prefixesToTest = [
        '' => '',
        'ul_card' => 'card',
        'ul_ticket' => 'ticket',
        'ul_pix' => 'pix'
    ];

    public function testOrderInfo()
    {
        $unlimintOrderInfo = new UnlimintOrderInfo();

        foreach ($this->prefixesToTest as $paymentCode => $expectedPrefix) {
            $prefix = $unlimintOrderInfo->getPrefix($paymentCode);
            $this->assertEquals($expectedPrefix, $prefix);
        }
    }
}
