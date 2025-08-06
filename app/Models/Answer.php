<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    protected $fillable = ['question_id', 'answer_text'];

    protected $hidden = ['created_at', 'updated_at','question_id'];

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class,'question_id');
    }
}
