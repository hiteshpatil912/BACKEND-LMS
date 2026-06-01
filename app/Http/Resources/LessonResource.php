<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url,
            'pdf_notes' => $this->pdf_notes,
            'lesson_order' => $this->lesson_order,
            'is_preview' => $this->is_preview,
            'course' => new CourseResource($this->whenLoaded('course')),
            'quizzes' => QuizResource::collection($this->whenLoaded('quizzes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
