<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\JsonResponse;

trait AuthorizesLmsContent
{
    protected function forbiddenResponse(): JsonResponse
    {
        if (method_exists($this, 'errorResponse')) {
            return $this->errorResponse('Forbidden', 403);
        }

        return response()->json([
            'message' => 'Forbidden',
        ], 403);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    protected function ownsCourse(User $user, ?Course $course): bool
    {
        return $course !== null
            && ($this->isAdmin($user) || (int) $course->user_id === (int) $user->id);
    }

    protected function ownsLesson(User $user, ?Lesson $lesson): bool
    {
        if ($lesson === null) {
            return false;
        }

        $lesson->loadMissing('course');

        return $lesson->course !== null && $this->ownsCourse($user, $lesson->course);
    }

    protected function ownsQuiz(User $user, ?Quiz $quiz): bool
    {
        if ($quiz === null) {
            return false;
        }

        $quiz->loadMissing('lesson.course');

        return $quiz->lesson !== null && $this->ownsLesson($user, $quiz->lesson);
    }

    protected function isEnrolledInCourse(User $user, int $courseId): bool
    {
        return $this->isAdmin($user)
            || $user->courses()->where('courses.id', $courseId)->exists();
    }

    protected function hasPurchasedOrEnrolledCourse(User $user, int $courseId): bool
    {
        return $this->isEnrolledInCourse($user, $courseId)
            || Order::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('payment_status', 'paid')
                ->exists();
    }

    protected function hasCompletedCourse(User $user, Course $course): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $totalLessons = $course->lessons()->count();
        $completedLessons = $user->completedLessons()
            ->where('lessons.course_id', $course->id)
            ->count();

        return $totalLessons > 0 && $completedLessons >= $totalLessons;
    }
}
