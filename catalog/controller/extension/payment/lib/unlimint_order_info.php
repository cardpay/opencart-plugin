<?php

class UnlimintOrderInfo
{
    public const UL_PREFIX =
        [
            'ul_card' => 'card',
            'ul_ticket' => 'ticket',
            'ul_pix' => 'pix'
        ];

    public function getPrefix($payment_code): string
    {
        if (isset(self::UL_PREFIX[$payment_code])) {
            $prefix = self::UL_PREFIX[$payment_code];
        }

        return $prefix ?? '';
    }
}