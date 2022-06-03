<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../../catalog/controller/extension/payment/lib/unlimint_exception.php';

class UnlimintExceptionTest extends TestCase
{
    public function testException()
    {
        $exceptionMessage = 'test';
        $unlimintException = new UnlimintException($exceptionMessage);

        $this->assertEquals($exceptionMessage, $unlimintException->getMessage());
    }
}
