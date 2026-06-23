<?php

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class AnnouncementCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Announcement $announcement)
    {
    }

   public function broadcastOn(): array
{
    return [
        new PrivateChannel('announcement'),
    ];
}

    public function broadcastAs(): string
    {
        return 'announcement.created';
    }

    public function broadcastWith(): array
    {
        return [
            'announcement' => $this->announcement,
        ];
    }
}