<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender');

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender' => ['id' => $this->message->sender->id, 'name' => $this->message->sender->name],
            'body' => $this->message->body,
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
