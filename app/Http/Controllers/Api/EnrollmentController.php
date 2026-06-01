<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    use ApiResponse;

    public function enroll(Request $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        if ($user->courses()->where('course_id', $id)->exists()) {
            return $this->errorResponse('Already Enrolled', 400);
        }

        $user->courses()->attach($id);

        return $this->successResponse(null, 'Enrollment Successful');
    }

    public function myCourses(Request $request)
    {
        $courses = $request->user()->courses()->with('teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $courses,
            CourseResource::collection($courses),
            'Courses fetched successfully'
        );
    }
}
