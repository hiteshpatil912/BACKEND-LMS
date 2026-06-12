<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Models\Assignment;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentController extends Controller
{
    use ApiResponse, AuthorizesLmsContent;

    public function index(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::find($courseId);

        if (! $this->ownsCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $assignments = Assignment::where('course_id', $courseId)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($assignments, JsonResource::collection($assignments), 'Assignments fetched successfully');
    }

    public function store(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::find($courseId);

        if (! $this->ownsCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $assignment = Assignment::create(array_merge($validated, ['course_id' => $courseId]));

        return $this->successResponse(['assignment' => $assignment], 'Assignment created successfully', 201);
    }

    public function show(Request $request, int $id)
    {
        $assignment = Assignment::find($id);

        if (! $assignment) {
            return $this->errorResponse('Assignment not found', 404);
        }

        $user = $request->user();
        $assignment->loadMissing('course');

        if (! $this->ownsCourse($user, $assignment->course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(['assignment' => $assignment], 'Assignment fetched successfully');
    }

    public function update(Request $request, int $id)
    {
        $assignment = Assignment::find($id);

        if (! $assignment) {
            return $this->errorResponse('Assignment not found', 404);
        }

        $user = $request->user();
        $assignment->loadMissing('course');

        if (! $this->ownsCourse($user, $assignment->course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $assignment->update($validated);

        return $this->successResponse(['assignment' => $assignment], 'Assignment updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $assignment = Assignment::find($id);

        if (! $assignment) {
            return $this->errorResponse('Assignment not found', 404);
        }

        $user = $request->user();
        $assignment->loadMissing('course');

        if (! $this->ownsCourse($user, $assignment->course)) {
            return $this->forbiddenResponse();
        }

        $assignment->delete();

        return $this->successResponse(null, 'Assignment deleted successfully');
    }
}
