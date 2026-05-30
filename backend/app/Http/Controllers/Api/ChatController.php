<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json($msg->load('sender:id,name'), 201);
    }
}
