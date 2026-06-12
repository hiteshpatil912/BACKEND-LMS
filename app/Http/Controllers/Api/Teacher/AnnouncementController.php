<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Models\Announcement;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementController extends Controller
{
    use ApiResponse, AuthorizesLmsContent;

    public function index(Request $request, int $courseId)
    {
        $user = $request->user();
        $course = Course::find($courseId);

        if (! $this->ownsCourse($user, $course)) {
            return $this->forbiddenResponse();
        }

        $announcements = Announcement::where('course_id', $courseId)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($announcements, JsonResource::collection($announcements), 'Announcements fetched successfully');
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
            'body' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $announcement = Announcement::create(array_merge($validated, ['course_id' => $courseId]));

        return $this->successResponse(['announcement' => $announcement], 'Announcement created successfully', 201);
    }

    public function show(Request $request, int $id)
    {
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return $this->errorResponse('Announcement not found', 404);
        }

        $user = $request->user();
        $announcement->loadMissing('course');

        if (! $this->ownsCourse($user, $announcement->course)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(['announcement' => $announcement], 'Announcement fetched successfully');
    }

    public function update(Request $request, int $id)
    {
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return $this->errorResponse('Announcement not found', 404);
        }

        $user = $request->user();
        $announcement->loadMissing('course');

        if (! $this->ownsCourse($user, $announcement->course)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'published_at' => 'nullable|date',
        ]);

        $announcement->update($validated);

        return $this->successResponse(['announcement' => $announcement], 'Announcement updated successfully');
    }

    public function destroy(Request $request, int $id)
    {
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return $this->errorResponse('Announcement not found', 404);
        }

        $user = $request->user();
        $announcement->loadMissing('course');

        if (! $this->ownsCourse($user, $announcement->course)) {
            return $this->forbiddenResponse();
        }

        $announcement->delete();

        return $this->successResponse(null, 'Announcement deleted successfully');
    }
}
