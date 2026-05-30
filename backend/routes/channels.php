<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

// Laravel broadcasts database notifications on this private channel by default.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conv = Conversation::find($id);
    return $conv && $conv->involves($user->id);
});

/**
 * Presence channel for a conversation. Returning an array places the user on
 * the channel's member list so both sides can render online/offline state.
 */
Broadcast::channel('presence-conversation.{id}', function ($user, $id) {
    $conv = Conversation::find($id);

    if (! $conv || ! $conv->involves($user->id)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar_url' => $user->avatar_url,
    ];
});
