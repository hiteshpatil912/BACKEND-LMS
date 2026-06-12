<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;

class StudentController extends Controller
{
    use ApiResponse, AuthorizesLmsContent;

    /**
     * Return all unique students enrolled in any course owned by the authenticated teacher.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $students = DB::table('users')
            ->join('course_user', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->where('courses.user_id', $user->id)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT course_user.course_id) as enrolled_courses_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->get();

        return $this->successResponse($students, 'Students fetched successfully');
    }

    /**
     * Show student detail for teacher.
     */
    public function show(Request $request, int $id)
    {
        $teacher = $request->user();

        $student = User::find($id);

        if (! $student) {
            return $this->errorResponse('Student not found', 404);
        }

        $enrolledCourses = Course::where('user_id', $teacher->id)
            ->whereHas('students', function ($q) use ($id) {
                $q->where('users.id', $id);
            })->with('teacher')->get();

        if (! $this->isAdmin($teacher) && $enrolledCourses->isEmpty()) {
            return $this->forbiddenResponse();
        }

        $courseIds = $enrolledCourses->pluck('id')->toArray();

        $completedLessonsCount = $student->completedLessons()
            ->whereIn('lessons.course_id', $courseIds)
            ->count();

        return $this->successResponse([
            'student' => new UserResource($student),
            'enrolled_courses' => CourseResource::collection($enrolledCourses),
            'completed_lessons_count' => $completedLessonsCount,
            'total_courses' => $enrolledCourses->count(),
        ], 'Student fetched successfully');
    }
}
