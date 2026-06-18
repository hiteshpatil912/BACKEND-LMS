<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $chat)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'chat.' . $this->chat->receiver_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

   public function broadcastWith(): array
{
    return [
        'chat' => $this->chat->load('sender', 'receiver'),
    ];
}
}