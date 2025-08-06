<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    protected $fillable = ['user_id', 'image'];

    protected $hidden = ['user_id','created_at','updated_at'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }
        return $value;
    }


}
