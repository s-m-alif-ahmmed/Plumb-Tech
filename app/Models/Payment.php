<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['payment_id', 'application_fee','transaction_id', 'amount', 'currency_code', 'discussion_request_id', 'user_id', 'engineer_id', 'status'];

    public function paymentBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function paymentFor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id', 'id');
    }

    public function discussionRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DiscussionRequest::class, 'discussion_request_id', 'id');
    }
}
