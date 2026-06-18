<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Events\MessageSent;


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
        broadcast(new MessageSent($chat))->toOthers();

        return $this->successResponse([
            'chat' => $chat,
        ], 'Message Sent Successfully');
    }

    public function markAsSeen(Request $request, int $userId)
    {
        Chat::where('sender_id', $userId)
            ->where('receiver_id', $request->user()->id)
            ->whereNull('seen_at')
            ->update([
                'seen_at' => now()
            ]);

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
}
