<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function index()
    {
        $courses = Course::with('teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $courses,
            CourseResource::collection($courses),
            'Courses fetched successfully'
        );
    }

    public function store(StoreCourseRequest $request)
    {
        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
            'user_id' => $request->user()->id,
        ]);

        return $this->successResponse([
            'course' => new CourseResource($course->load('teacher')),
        ], 'Course Created Successfully');
    }

    public function show(int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        return $this->successResponse([
            'course' => new CourseResource($course->load('teacher')),
        ], 'Course fetched successfully');
    }

    public function enrolledStudents(Request $request, int $id)
    {
        $course = Course::with('students')->find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->ownsCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'students' => UserResource::collection($course->students),
        ], 'Students fetched successfully');
    }

    public function update(UpdateCourseRequest $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->ownsCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        return $this->successResponse([
            'course' => new CourseResource($course->load('teacher')),
        ], 'Course Updated Successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->ownsCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        $course->delete();

        return $this->successResponse(null, 'Course Deleted Successfully');
    }
}
