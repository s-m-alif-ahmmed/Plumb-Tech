<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSession extends Model
{
   protected $fillable = ['start_at', 'expire_at'];

   protected $casts = [
       'start_at' => 'datetime',
       'expire_at' => 'datetime',
   ];

   public function conversation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
   {
       return $this->belongsTo(Conversation::class);
   }
}
