<?php

use App\Models\DiscussionRequest;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('accept.{id}', function ($user, $id) {
    $request = DiscussionRequest::find($id);
    return (int) $request?->user_id === (int) $user?->id;
});
