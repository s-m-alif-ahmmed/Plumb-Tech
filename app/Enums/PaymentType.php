<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
class PaymentType extends Enum
{
    const DONATION = 'donation';
    const PRODUCT = 'product';
    public static function map() : array
    {
        return [
            self::DONATION,
            self::PRODUCT,
        ];
    }
}
