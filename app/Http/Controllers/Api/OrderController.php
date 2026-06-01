<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseCourseRequest;
use App\Http\Resources\OrderResource;
use App\Models\Course;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $orders = Order::with('user', 'course.teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $orders,
            OrderResource::collection($orders),
            'Orders fetched successfully'
        );
    }

    public function studentPurchaseCourse(PurchaseCourseRequest $request)
    {
        return $this->successResponse([
            'payment' => [
                'id' => rand(1000, 9999),
                'course' => 'Purchased Course',
                'type' => 'one_time',
                'amount' => 129,
                'status' => 'paid',
                'date' => now()->toDateString(),
                'invoiceNo' => 'INV-' . rand(1000, 9999),
            ],
        ], 'Payment fetched successfully');
    }

    public function purchaseCourse(PurchaseCourseRequest $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $id)
            ->where('payment_status', 'paid')
            ->first();

        if ($existingOrder) {
            return $this->errorResponse('Course Already Purchased', 400);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $id,
            'amount' => $course->price,
            'payment_status' => 'paid',
            'payment_id' => 'PAY-' . strtoupper(uniqid()),
        ]);

        if (!$user->courses()->where('course_id', $id)->exists()) {
            $user->courses()->attach($id);
        }

        return $this->successResponse([
            'order' => new OrderResource($order->load('course.teacher', 'user')),
        ], 'Course Purchased Successfully');
    }

    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('course.teacher')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse(
            $orders,
            OrderResource::collection($orders),
            'Orders fetched successfully'
        );
    }
}
