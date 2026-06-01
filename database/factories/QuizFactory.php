<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::inRandomOrder()->first()->id,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'total_marks' => 10,
        ];
    }
}