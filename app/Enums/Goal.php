<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;
class Goal extends Enum
{
    const LOSE_WEIGHT = 'lose weight';
    const GAIN_WEIGHT = 'gain weight';
    const MUSCLE_MASS_GAIN = 'muscle mass gain';
    const SHAPE_BODY = 'shape body';
    const OTHERS = 'others';

    /**
     * Get an array of all constants.
     *
     * @return array
     */
    public static function map(): array
    {
        return [
            self::LOSE_WEIGHT,
            self::GAIN_WEIGHT,
            self::MUSCLE_MASS_GAIN,
            self::SHAPE_BODY,
            self::OTHERS,
        ];
    }
}
