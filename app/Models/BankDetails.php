<?php

namespace App\Models;

use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'branch_name',
        'swift_code',
        'ifsc_code',
        'is_default'
    ];

    protected $hidden = [
        'user_id',
        "is_default",
        "created_at",
        "updated_at"
    ];

    /**
     * Relationship: A bank detail belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Relationship with WithdrawalRequest
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }
}
