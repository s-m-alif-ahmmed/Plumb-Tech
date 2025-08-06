<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'bank_details_id',
        'amount',
        'status',
        'rejection_reason'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with BankDetails
    public function bankDetails()
    {
        return $this->belongsTo(BankDetails::class, 'bank_details_id'); 
    }
}
