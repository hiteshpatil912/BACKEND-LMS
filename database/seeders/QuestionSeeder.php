<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = Quiz::all();

        foreach ($quizzes as $quiz) {

            for ($i = 1; $i <= 5; $i++) {

                Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => "Question {$i} for Quiz {$quiz->id}",

                    'option_1' => 'Option A',
                    'option_2' => 'Option B',
                    'option_3' => 'Option C',
                    'option_4' => 'Option D',

                    'correct_answer' => 'Option A',
                ]);
            }
        }
    }
}