<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function index()
    {
        $lessons = Lesson::with('course.teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $lessons,
            LessonResource::collection($lessons),
            'Lessons fetched successfully'
        );
    }

    public function store(StoreLessonRequest $request)
    {
        $course = Course::find($request->course_id);

        if (!$this->ownsCourse($request->user(), $course)) {
            return $this->forbiddenResponse();
        }

        $lesson = Lesson::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'lesson_order' => $request->lesson_order,
            'is_preview' => $request->is_preview,
        ]);

        return $this->successResponse([
            'lesson' => new LessonResource($lesson->load('course.teacher')),
        ], 'Lesson Created Successfully');
    }

    public function courseLessons(Request $request, int $courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        if (
            !$this->ownsCourse($user, $course)
            && !$this->isEnrolledInCourse($user, $course->id)
        ) {
            return $this->forbiddenResponse();
        }

        $lessons = Lesson::where('course_id', $courseId)
            ->orderBy('lesson_order')
            ->paginate(10);

        return $this->paginatedResponse(
            $lessons,
            LessonResource::collection($lessons),
            'Lessons fetched successfully'
        );
    }

    public function update(UpdateLessonRequest $request, int $id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return $this->errorResponse('Lesson Not Found', 404);
        }

        if (!$this->ownsLesson($request->user(), $lesson)) {
            return $this->forbiddenResponse();
        }

        $lesson->update([
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'lesson_order' => $request->lesson_order,
            'is_preview' => $request->is_preview,
        ]);

        return $this->successResponse([
            'lesson' => new LessonResource($lesson->load('course.teacher')),
        ], 'Lesson Updated Successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return $this->errorResponse('Lesson Not Found', 404);
        }

        if (!$this->ownsLesson($request->user(), $lesson)) {
            return $this->forbiddenResponse();
        }

        $lesson->delete();

        return $this->successResponse(null, 'Lesson Deleted Successfully');
    }
}
