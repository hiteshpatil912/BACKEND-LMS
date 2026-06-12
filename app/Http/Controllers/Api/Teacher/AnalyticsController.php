<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;

class AnalyticsController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

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

    /**
     * Course-specific analytics for a teacher-owned course.
     */
    public function courseStats(Request $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->ownsCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        $totalStudents = DB::table('course_user')
            ->where('course_id', $course->id)
            ->distinct()
            ->count('user_id');

        $totalLessons = $course->lessons()->count();

        $totalQuizzes = Quiz::whereHas('lesson', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->count();

        return $this->successResponse([
            'course_id' => $course->id,
            'total_students' => $totalStudents,
            'total_lessons' => $totalLessons,
            'total_quizzes' => $totalQuizzes,
        ], 'Course analytics fetched successfully');
    }
}
