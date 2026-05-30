<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conv = Conversation::find($id);
    return $conv && $conv->involves($user->id);
});
