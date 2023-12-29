<?php

namespace Unlimit;

class UnlimitOrderInfo
{
    public const UL_PREFIX =
        [
            'ul_card'       => 'card',
            'ul_ticket'     => 'ticket',
            'ul_pix'        => 'pix',
            'ul_gpay'       => 'gpay',
            'ul_mbway'      => 'mbway',
            'ul_multibanco' => 'multibanco',
            'ul_paypal'     => 'paypal',
            'ul_sepa'       => 'sepa',
            'ul_spei'       => 'spei',
        ];

    public function getPrefix($payment_code): string
    {
        if (isset(self::UL_PREFIX[$payment_code])) {
            $prefix = self::UL_PREFIX[$payment_code];
        }

        return $prefix ?? '';
    }
}