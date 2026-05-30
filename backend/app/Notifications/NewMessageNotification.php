<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent to the recipient of a chat message when they are not actively viewing
 * the conversation, so they see an unread badge / toast.
 */
class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public ChatMessage $message) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->message->loadMissing('sender');

        return [
            'type' => 'new_message',
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender?->name,
            'preview' => Str::limit($this->message->body, 80),
            'message' => sprintf('New message from %s', $this->message->sender?->name ?? 'someone'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
