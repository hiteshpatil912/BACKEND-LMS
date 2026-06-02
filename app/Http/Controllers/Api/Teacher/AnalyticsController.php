<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $totalCourses = Course::where('user_id', $user->id)->count();

        // count distinct students across teacher's courses
        $totalStudents = DB::table('course_user')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->where('courses.user_id', $user->id)
            ->distinct()
            ->count('course_user.user_id');

        $totalLessons = Lesson::whereHas('course', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $totalQuizzes = Quiz::whereHas('lesson', function ($q) use ($user) {
            $q->whereHas('course', function ($q2) use ($user) {
                $q2->where('user_id', $user->id);
            });
        })->count();

        return $this->successResponse([
            'total_courses' => $totalCourses,
            'total_students' => $totalStudents,
            'total_lessons' => $totalLessons,
            'total_quizzes' => $totalQuizzes,
        ], 'Teacher analytics fetched successfully');
    }
}
