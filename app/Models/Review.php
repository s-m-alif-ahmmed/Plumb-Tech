<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['reviewer_id', 'user_id', 'rating', 'review'];

    // The user giving the rating
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // The user receiving the rating
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
