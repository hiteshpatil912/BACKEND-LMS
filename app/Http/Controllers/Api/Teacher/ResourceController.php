<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Course;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceController extends Controller
{
    use ApiResponse, AuthorizesLmsContent;

    public function index(Request $request)
    {
        $user = $request->user();

        $resources = Resource::whereHas('course', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('course')->latest()->paginate(10);

        return $this->paginatedResponse($resources, JsonResource::collection($resources), 'Resources fetched successfully');
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
            'description' => 'required|string|max:255',
            'file_url' => 'required|string',
        ]);

        $resource = Resource::create(array_merge($validated, ['course_id' => $courseId]));

        return $this->successResponse(['resource' => $resource], 'Resource created successfully', 201);
    }

    public function show(Request $request, int $id)
    {
        $resource = Resource::find($id);

        if (! $resource) {
            return $this->errorResponse('Resource not found', 404);
        }

        $user = $request->user();
        $resource->loadMissing('course');

        if (! $this->ownsCourse($user, $resource->course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(['resource' => $resource], 'Resource fetched successfully');
    }

    public function update(Request $request, int $id)
    {
        $resource = Resource::find($id);

        if (! $resource) {
            return $this->errorResponse('Resource not found', 404);
        }

        $user = $request->user();
        $resource->loadMissing('course');

        if (! $this->ownsCourse($user, $resource->course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'file_url' => 'sometimes|required|string',
        ]);

        $resource->update($validated);

        return $this->successResponse(['resource' => $resource], 'Resource updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $resource = Resource::find($id);

        if (! $resource) {
            return $this->errorResponse('Resource not found', 404);
        }

        $user = $request->user();
        $resource->loadMissing('course');

        if (! $this->ownsCourse($user, $resource->course)) {
            return $this->forbiddenResponse();
        }

        $resource->delete();

        return $this->successResponse(null, 'Resource deleted successfully');
    }
}
