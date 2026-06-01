<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'total_lessons' => $this->resource['total_lessons'] ?? 0,
            'completed_lessons' => $this->resource['completed_lessons'] ?? 0,
            'progress_percentage' => $this->resource['progress_percentage'] ?? 0,
        ];

        if (array_key_exists('course_id', $this->resource)) {
            $data = ['course_id' => $this->resource['course_id']] + $data;
        }

        if (array_key_exists('course', $this->resource)) {
            $data = ['course' => new CourseResource($this->resource['course'])] + $data;
        }

        return $data;
    }
}
