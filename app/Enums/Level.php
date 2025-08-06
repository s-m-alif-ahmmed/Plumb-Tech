<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
class Level extends Enum
{
    const BEGINNER = 'beginner';
    const INTERMEDIATE = 'intermediate';
    const ADVANCE = 'advance';

    /**
     * Get an array of all constants.
     *
     * @return array
     */
    public static function map(): array
    {
        return [
            self::BEGINNER,
            self::INTERMEDIATE,
            self::ADVANCE,
        ];
    }
}
