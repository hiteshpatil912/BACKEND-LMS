<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::inRandomOrder()->first()->id,

            'question' => fake()->sentence(),

            'option_1' => 'Option A',
            'option_2' => 'Option B',
            'option_3' => 'Option C',
            'option_4' => 'Option D',

            'correct_answer' => 'Option A',
        ];
    }
}