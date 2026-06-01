<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Models\Review;
use App\Traits\ApiResponse;

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
}
