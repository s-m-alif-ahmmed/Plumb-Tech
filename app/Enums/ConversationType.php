<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
class ConversationType extends Enum
{
    const SELF = 'self';
    const PRIVATE = 'private';
    const GROUP = 'group';

    /**
     * Get an array of all constants.
     *
     * @return array
     */
    public static function map(): array
    {
        return [
            self::SELF,
            self::PRIVATE,
            self::GROUP,
        ];
    }
}
