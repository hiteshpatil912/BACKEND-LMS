<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponse;

class AdminController extends Controller
{
    use ApiResponse;

    public function users()
    {
        $users = User::latest()->paginate(10);

        return $this->paginatedResponse(
            $users,
            UserResource::collection($users),
            'Users fetched successfully'
        );
    }

    public function reports()
    {
        return $this->successResponse([
            'reports' => [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
                'total_lessons' => Lesson::count(),
                'total_quizzes' => Quiz::count(),
                'total_orders' => Order::count(),
                'total_reviews' => Review::count(),
                'total_certificates' => Certificate::count(),
                'total_notifications' => Notification::count(),
                'paid_revenue' => Order::where('payment_status', 'paid')->sum('amount'),
            ],
        ], 'Reports fetched successfully');
    }
}
