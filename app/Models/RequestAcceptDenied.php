<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAcceptDenied extends Model
{
    protected $fillable = [
        'request_id', 'engineer_id', 'status'
    ];

    // DiscussionRequest relation
    public function discussionRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DiscussionRequest::class, 'request_id');
    }

    // Engineer relation
    public function engineer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    public function scopeByEngineer($query)
    {
        return $query->where('engineer_id',auth()->id());
    }
}
