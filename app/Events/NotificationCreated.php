<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   public function __construct(
    public Notification $notification
) {
    \Log::info('🔥 NotificationCreated Constructor', [
        'user_id' => $notification->user_id,
        'title' => $notification->title,
    ]);
}
  public function broadcastOn(): array
{
    \Log::info('🔥 Notification broadcastOn', [
        'user_id' => $this->notification->user_id,
    ]);

    return [
        new PrivateChannel(
            'notification.' . $this->notification->user_id
        ),
    ];
}

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

   public function broadcastWith(): array
{
    \Log::info('🔥 Notification broadcastWith', [
        'user_id' => $this->notification->user_id,
    ]);

    return [
        'notification' => $this->notification,
    ];
}
}