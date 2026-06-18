<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('chat.{userId}', function ($user, $userId) {

    Log::info('CHANNEL AUTH', [
        'auth_user' => $user?->id,
        'channel_user' => $userId,
    ]);

    return (int) $user->id === (int) $userId;
});