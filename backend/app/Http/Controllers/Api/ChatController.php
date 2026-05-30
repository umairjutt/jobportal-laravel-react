<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Chat
 *
 * Recruiter <-> candidate realtime messaging over Laravel Reverb (WebSockets),
 * including typing indicators and read receipts.
 * @authenticated
 */
class ChatController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $convs = Conversation::query()
            ->where(fn ($q) => $q->where('recruiter_id', $userId)->orWhere('candidate_id', $userId))
            ->with(['recruiter:id,name,avatar_url', 'candidate:id,name,avatar_url'])
            ->withCount('messages')
            ->latest('updated_at')
            ->get();

        return response()->json($convs);
    }

    public function startWith(Request $request, User $user): JsonResponse
    {
        $me = $request->user();

        $isRecruiter = $me->hasRole('recruiter');
        $recruiter = $isRecruiter ? $me : $user;
        $candidate = $isRecruiter ? $user : $me;

        $conv = Conversation::firstOrCreate([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);

        return response()->json($conv);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->involves($request->user()->id), 403);
        return response()->json($conversation->messages()->with('sender:id,name')->limit(100)->get());
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->involves($request->user()->id), 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $conversation->touch();

        broadcast(new MessageSent($msg))->toOthers();

        // Notify the other participant (unread badge + toast).
        $recipientId = $conversation->recruiter_id === $request->user()->id
            ? $conversation->candidate_id
            : $conversation->recruiter_id;
        User::find($recipientId)?->notify(new NewMessageNotification($msg));

        return response()->json($msg->load('sender:id,name'), 201);
    }

    /**
     * Typing indicator
     *
     * Broadcast an ephemeral "is typing" signal to the other participant.
     *
     * @urlParam conversation integer required Example: 1
     * @bodyParam is_typing boolean Whether the user started or stopped typing. Example: true
     */
    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->involves($request->user()->id), 403);

        $isTyping = $request->boolean('is_typing', true);

        broadcast(new UserTyping(
            $conversation->id,
            $request->user()->id,
            $request->user()->name,
            $isTyping,
        ))->toOthers();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark messages read
     *
     * Mark all of the other participant's messages in this conversation as read
     * and broadcast a read receipt.
     *
     * @urlParam conversation integer required Example: 1
     */
    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->involves($request->user()->id), 403);

        $readAt = now();

        $messageIds = $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->pluck('id')
            ->all();

        if (! empty($messageIds)) {
            ChatMessage::whereIn('id', $messageIds)->update(['read_at' => $readAt]);

            broadcast(new MessagesRead(
                $conversation->id,
                $request->user()->id,
                $messageIds,
                $readAt->toIso8601String(),
            ))->toOthers();
        }

        return response()->json(['read' => count($messageIds), 'read_at' => $readAt->toIso8601String()]);
    }
}
