<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $notifications = Notification::with('user')->latest()->paginate(10);

        return $this->paginatedResponse(
            $notifications,
            NotificationResource::collection($notifications),
            'Notifications fetched successfully'
        );
    }

    public function store(StoreNotificationRequest $request)
    {
        $notification = Notification::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'message' => $request->message,
        ]);

        return $this->successResponse([
            'notification' => new NotificationResource($notification->load('user')),
        ], 'Notification Created Successfully');
    }

    public function myNotifications(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse(
            $notifications,
            NotificationResource::collection($notifications),
            'Notifications fetched successfully'
        );
    }

    public function markAsRead(Request $request, int $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification Not Found', 404);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return $this->successResponse([
            'notification' => new NotificationResource($notification),
        ], 'Notification Marked As Read');
    }
}
