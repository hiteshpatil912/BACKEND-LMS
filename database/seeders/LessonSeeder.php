<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {

            for ($i = 1; $i <= 5; $i++) {

                Lesson::create([
                    'course_id' => $course->id,
                    'title' => "Lesson {$i}",
                    'description' => "Description for Lesson {$i}",
                    'video_url' => "https://youtube.com/watch?v=lesson{$i}",
                    'pdf_notes' => null,
                    'lesson_order' => $i,
                    'is_preview' => $i === 1,
                ]);
            }
        }
    }
}