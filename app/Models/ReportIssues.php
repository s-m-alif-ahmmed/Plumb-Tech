<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportIssues extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'engineer_id',
        'type',
        'service_title',
        'description',
        'is_resolved',
        'discussion_request_id',
    ];

    // Relationship: Report belongs to an Engineer (User)
    public function engineer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function discussionRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DiscussionRequest::class, 'discussion_request_id');
    }
}
