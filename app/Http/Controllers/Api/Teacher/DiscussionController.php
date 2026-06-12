<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Models\Discussion;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscussionController extends Controller
{
    use ApiResponse, AuthorizesLmsContent;

    public function index(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::find($courseId);

        if (! $this->ownsCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $discussions = Discussion::where('course_id', $courseId)
            ->with('user')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($discussions, JsonResource::collection($discussions), 'Discussions fetched successfully');
    }

    public function store(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::find($courseId);

        if (! $this->ownsCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $discussion = Discussion::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'message' => $validated['message'],
        ]);

        return $this->successResponse(['discussion' => $discussion], 'Discussion created successfully', 201);
    }

    public function show(Request $request, int $id)
    {
        $discussion = Discussion::find($id);

        if (! $discussion) {
            return $this->errorResponse('Discussion not found', 404);
        }

        $user = $request->user();
        $discussion->loadMissing('course', 'user');

        if (! $this->ownsCourse($user, $discussion->course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(['discussion' => $discussion], 'Discussion fetched successfully');
    }

    public function update(Request $request, int $id)
    {
        $discussion = Discussion::find($id);

        if (! $discussion) {
            return $this->errorResponse('Discussion not found', 404);
        }

        $user = $request->user();
        $discussion->loadMissing('course');

        if (! $this->ownsCourse($user, $discussion->course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $discussion->update(['message' => $validated['message']]);

        return $this->successResponse(['discussion' => $discussion], 'Discussion updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $discussion = Discussion::find($id);

        if (! $discussion) {
            return $this->errorResponse('Discussion not found', 404);
        }

        $user = $request->user();
        $discussion->loadMissing('course');

        if (! $this->ownsCourse($user, $discussion->course)) {
            return $this->forbiddenResponse();
        }

        $discussion->delete();

        return $this->successResponse(null, 'Discussion deleted successfully');
    }
}
