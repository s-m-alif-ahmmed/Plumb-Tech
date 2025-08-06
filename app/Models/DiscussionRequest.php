<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'engineer_id',
        'service_id',
        'service_title',
        'price',
        'status',
        'question_answer',
        'images',
        'description'
    ];

    protected $casts = [
        'images' => 'array',
        'question_answer' => 'array',
    ];

    public function getImagesAttribute($value): array | null
    {
        if (empty($value)) {
            return null;
        }

        $images = json_decode($value, true);

        if (!is_array($images)) {
            return [$value];
        }

        return array_map(fn($image) => url($image), $images);
    }

    // Request sender (User)
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Request receiver (Engineer)
    public function engineer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id')->where('role', 'engineer');
    }

    // Service
    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // RequestAcceptDenies
    public function requestAcceptDenies(): DiscussionRequest|\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RequestAcceptDenied::class,'request_id');
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne|DiscussionRequest
    {
        return $this->hasone(Payment::class, 'discussion_request_id');
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\HasOne|DiscussionRequest
    {
        return $this->hasOne(Conversation::class, 'service_request_id');
    }
    
}
