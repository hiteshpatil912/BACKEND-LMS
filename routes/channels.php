<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Course;

Broadcast::channel('chat.{userId}', function ($user, $userId) {

    Log::info('CHANNEL AUTH', [
        'auth_user' => $user?->id,
        'channel_user' => $userId,
    ]);

    return (int) $user->id === (int) $userId;
});
Broadcast::channel('online', function ($user) {

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];

});
Broadcast::channel('announcement.{courseId}', function ($user, $courseId) {

    $course = Course::find($courseId);

    if (! $course) {
        return false;
    }

    // ✅ Teacher who owns this course
    if ((int) $course->user_id === (int) $user->id) {
        return true;
    }

    // ✅ Enrolled student
    return $course->students()
        ->where('users.id', $user->id)
        ->exists();
});
Broadcast::channel('announcement', function ($user) {
    return $user !== null;
});
Broadcast::channel('notification.{userId}', function ($user, $userId) {

    return (int) $user->id === (int) $userId;

});