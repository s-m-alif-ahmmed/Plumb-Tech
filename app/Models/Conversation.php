<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['service_session_id', 'service_request_id', 'title', 'type'];

    protected $hidden = ['created_at', 'updated_at','created_by'];

    protected $appends = ['status'];


    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany|Conversation
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany|Conversation
    {
        return $this->hasMany(Message::class);
    }

    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceSession::class, 'service_session_id');
    }

    public function getStatusAttribute(): bool
    {
        return !$this->session->expire_at->isPast();
    }
}
