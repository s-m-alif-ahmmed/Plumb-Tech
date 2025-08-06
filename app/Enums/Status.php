<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The Status enum.
 *
 * @method static self IN_PROGRESS()
 * @method static self COMPLETED()
 * @method static self FAILED()
 * @method static self ACTIVE()
 * @method static self INACTIVE()
 * @method static self APPROVED()
 * @method static self DECLINED()
 */
class Status extends Enum
{
    const IN_PROGRESS = 'in progress';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const APPROVED = 'approved';
    const DECLINED = 'declined';
    const DENINED = 'denied';
    const REJECTED = 'rejected';
    const PUBLISHED = 'published';
    const CANCELLED = 'cancelled';
    const ACCEPTED = 'accepted';
    const PENDING = 'pending';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::IN_PROGRESS => 'In Progress',
            static::COMPLETED => 'Completed',
            static::FAILED => 'Failed',
            static::ACTIVE => 'Active',
            static::INACTIVE => 'Inactive',
            static::APPROVED => 'Approved',
            static::DECLINED => 'Declined',
        ];
    }
}
