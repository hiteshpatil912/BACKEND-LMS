<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::inRandomOrder()->first()->id,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'video_url' => 'https://youtube.com/watch?v=' . fake()->lexify('????????'),
            'pdf_notes' => null,
            'lesson_order' => fake()->numberBetween(1, 5),
            'is_preview' => false,
        ];
    }
}