<?php

class UnlimintOrderInfo
{
    public const UL_PREFIX =
        [
            'ul_card' => 'card',
            'ul_ticket' => 'ticket',
            'ul_pix' => 'pix'
        ];

    public function getPrefix($payment_code)
    {
        if (isset(UnlimintOrderInfo::UL_PREFIX[$payment_code])) {
            $prefix = UnlimintOrderInfo::UL_PREFIX[$payment_code];
        }

        return $prefix ?? '';
    }
}