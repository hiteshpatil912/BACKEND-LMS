<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'chats' => Chat::where('sender_id', $request->user()->id)
                ->orWhere('receiver_id', $request->user()->id)
                ->with('sender', 'receiver')
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreChatRequest $request)
    {
        $chat = Chat::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Message Sent Successfully',
            'chat' => $chat,
        ]);
    }
}
