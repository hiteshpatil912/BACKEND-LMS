<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function index()
    {
        $reviews = Review::with('user', 'course.teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $reviews,
            ReviewResource::collection($reviews),
            'Reviews fetched successfully'
        );
    }

    public function store(StoreReviewRequest $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        if (!$this->hasPurchasedOrEnrolledCourse($user, $course->id)) {
            return $this->forbiddenResponse();
        }

        $existingReview = Review::where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if ($existingReview) {
            return $this->errorResponse('Review Already Submitted', 400);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'course_id' => $id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return $this->successResponse([
            'review' => new ReviewResource($review->load('user', 'course.teacher')),
        ], 'Review Submitted Successfully');
    }

    public function courseReviews(int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $reviews = Review::where('course_id', $id)
            ->with('user')
            ->latest()
            ->paginate(10);

        $averageRating = Review::where('course_id', $id)->avg('rating');

        return $this->paginatedResponse(
            $reviews,
            ReviewResource::collection($reviews),
            'Reviews fetched successfully',
            200,
            [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->total(),
            ]
        );
    }

    /**
     * Reviews for all courses owned by the authenticated teacher.
     */
    public function teacherReviews(Request $request)
    {
        $user = $request->user();

        $reviews = Review::whereHas('course', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('user', 'course')->latest()->get();

        $data = $reviews->map(function ($r) {
            return [
                'review_id' => $r->id,
                'course_id' => $r->course_id,
                'course_title' => $r->course->title ?? null,
                'student_name' => $r->user->name ?? null,
                'rating' => $r->rating,
                'comment' => $r->review,
                'created_at' => $r->created_at,
            ];
        })->values();

        return $this->successResponse($data, 'Teacher reviews fetched successfully');
    }
}
