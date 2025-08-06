<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'sender_id', 'message', 'attachment', 'is_read', 'type'];

    protected $hidden = ['sender_id', 'updated_at','is_read'];

    protected  $casts = [
        'created_at' => 'datetime'
    ];
    public function conversation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads(): \Illuminate\Database\Eloquent\Relations\HasMany|Message
    {
        return $this->hasMany(MessageRead::class);
    }

    public function getAttachmentAttribute($value): \Illuminate\Foundation\Application|string|\Illuminate\Contracts\Routing\UrlGenerator
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }
        return $value ?? '';
    }
}
