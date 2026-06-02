<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $coursesQuery = Course::where('user_id', $user->id);

        $coursesCount = $coursesQuery->count();

        $studentsCount = Course::where('user_id', $user->id)
            ->withCount('students')
            ->get()
            ->sum('students_count');

        $recentCourses = $coursesQuery->with('teacher')->latest()->take(5)->get();

        return $this->successResponse([
            'dashboard' => [
                'courses_count' => $coursesCount,
                'students_count' => $studentsCount,
                'recent_courses' => CourseResource::collection($recentCourses),
            ],
        ], 'Teacher dashboard fetched successfully');
    }
}
