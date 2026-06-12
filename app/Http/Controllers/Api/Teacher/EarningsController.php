<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $ordersQuery = Order::whereHas('course', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('payment_status', 'paid');

        $totalRevenue = (float) $ordersQuery->sum('amount');
        $totalSales = $ordersQuery->count();

        $recentTransactions = Order::whereHas('course', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('payment_status', 'paid')
            ->with('course', 'user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($o) {
                return [
                    'course_id' => $o->course_id,
                    'course_title' => $o->course->title ?? null,
                    'student_name' => $o->user->name ?? null,
                    'amount' => (float) $o->amount,
                    'created_at' => $o->created_at,
                ];
            });

        return $this->successResponse([
            'total_revenue' => $totalRevenue,
            'total_sales' => $totalSales,
            'recent_transactions' => $recentTransactions,
        ], 'Teacher earnings fetched successfully');
    }
}
