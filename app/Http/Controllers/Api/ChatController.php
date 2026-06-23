<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Events\TypingStarted;
use App\Events\TypingStopped;


class ChatController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        return response()->json([
            'chats' => Chat::where('sender_id', $request->user()->id)
                ->orWhere('receiver_id', $request->user()->id)
                ->with('sender', 'receiver')
                ->latest()
                ->get(),
        ]);
        $chats = Chat::where('sender_id', $request->user()->id)
            ->orWhere('receiver_id', $request->user()->id)
            ->with('sender', 'receiver')
            ->latest()
            ->get();

        return $this->successResponse(['chats' => $chats], 'Chats fetched successfully');
    }

    public function store(StoreChatRequest $request)
    {
        $chat = Chat::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);
        // broadcast(new MessageSent($chat))->toOthers();
        broadcast(new MessageSent($chat));

        return $this->successResponse([
            'chat' => $chat,
        ], 'Message Sent Successfully');
    }

    public function markAsSeen(Request $request, int $userId)
    {
        $messages = Chat::where('sender_id', $userId)
            ->where('receiver_id', $request->user()->id)
            ->whereNull('seen_at')
            ->get();

        foreach ($messages as $message) {

            $message->seen_at = now();
            $message->save();

            broadcast(
                new \App\Events\MessageSeen(
                    $message->id,
                    $message->seen_at,
                    $message->sender_id
                )
            )->toOthers();
        }

        return $this->successResponse(null, 'Messages marked as seen.');
    }
    /**
     * Teacher: list chats for authenticated teacher
     */
    public function teacherIndex(Request $request)
    {
        
        $chats = Chat::where('sender_id', $request->user()->id)
            ->orWhere('receiver_id', $request->user()->id)
            ->with('sender', 'receiver')
            ->latest('created_at')
            ->get();

        return $this->successResponse([
            'chats' => $chats,
        ], 'Chats fetched successfully');
    }

    /**
     * Teacher: send chat message
     */
    public function teacherStore(StoreChatRequest $request)
    {
        $chat = Chat::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($chat))->toOthers();

        return $this->successResponse([
            'chat' => $chat,
        ], 'Message sent successfully');
    }
    public function typingStarted(Request $request)
    {
        broadcast(
            new TypingStarted(
                $request->user()->id,
                $request->receiver_id
            )
        )->toOthers();

        return response()->json([
            'success' => true,
        ]);
    }

    public function typingStopped(Request $request)
    {
        broadcast(
            new TypingStopped(
                $request->user()->id,
                $request->receiver_id
            )
        )->toOthers();

        return response()->json([
            'success' => true,
        ]);
    }
}
