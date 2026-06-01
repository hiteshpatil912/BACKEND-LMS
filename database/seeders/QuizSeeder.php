<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {

            Quiz::create([
                'lesson_id' => $lesson->id,
                'title' => 'Quiz for '.$lesson->title,
                'description' => 'Quiz Description',
                'total_marks' => 10,
            ]);
        }
    }
}