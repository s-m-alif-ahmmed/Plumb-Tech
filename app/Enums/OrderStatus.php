<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
use function Laravel\Prompts\select;

class OrderStatus extends Enum
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const SHIPPED = 'shipped';
    const DELIVERED = 'delivered';
    const CANCELED = 'canceled';
    public static function map() : array
    {
        return [
            self::PENDING    => 'yellow',
            self::PROCESSING => 'blue',
            self::SHIPPED    => 'orange',
            self::DELIVERED  => 'green',
            self::CANCELED   => 'red',
        ];
    }
}
