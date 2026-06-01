<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Http\Resources\ProgressResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function completeLesson(Request $request, int $id)
    {
        $lesson = Lesson::with('course')->find($id);

        if (!$lesson) {
            return $this->errorResponse('Lesson Not Found', 404);
        }

        $user = $request->user();

        if (!$this->isEnrolledInCourse($user, $lesson->course_id)) {
            return $this->forbiddenResponse();
        }

        if ($user->completedLessons()->where('lessons.id', $lesson->id)->exists()) {
            return $this->successResponse(null, 'Lesson Already Completed');
        }

        $user->lessons()->syncWithoutDetaching([
            $lesson->id => [
                'is_completed' => true,
                'completed_at' => now(),
                'progress' => 100,
            ],
        ]);

        return $this->successResponse([
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
        ], 'Lesson Completed Successfully');
    }

    public function courseProgress(Request $request, int $id)
    {
        $course = Course::withCount('lessons')->find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        if (!$this->isEnrolledInCourse($user, $course->id)) {
            return $this->forbiddenResponse();
        }

        $totalLessons = $course->lessons_count;
        $completedLessons = $user->completedLessons()
            ->where('lessons.course_id', $course->id)
            ->count();

        return $this->successResponse((new ProgressResource([
            'course_id' => $course->id,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $this->calculatePercentage($completedLessons, $totalLessons),
        ]))->resolve($request), 'Course progress fetched successfully');
    }

    public function continueLearning(Request $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        if (!$this->isEnrolledInCourse($request->user(), $course->id)) {
            return $this->forbiddenResponse();
        }

        $completedLessonIds = $request->user()
            ->completedLessons()
            ->where('lessons.course_id', $id)
            ->pluck('lessons.id');

        $nextLesson = Lesson::where('course_id', $id)
            ->whereNotIn('id', $completedLessonIds)
            ->orderBy('lesson_order')
            ->first();

        if (!$nextLesson) {
            return $this->successResponse(null, 'Course Completed');
        }

        return $this->successResponse([
            'next_lesson' => new LessonResource($nextLesson),
        ], 'Continue learning fetched successfully');
    }

    private function calculatePercentage(int $completedLessons, int $totalLessons): float|int
    {
        return $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100, 2)
            : 0;
    }
}
