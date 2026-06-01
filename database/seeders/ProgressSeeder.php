<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::with(['students', 'lessons'])->get();

        foreach ($courses as $course) {
            if ($course->lessons->isEmpty()) {
                continue;
            }

            foreach ($course->students as $student) {
                $completedLessons = $course->lessons
                    ->shuffle()
                    ->take(min(rand(1, 3), $course->lessons->count()));

                foreach ($completedLessons as $lesson) {
                    // syncWithoutDetaching is safe to run again and respects the
                    // unique lesson_id/user_id database constraint.
                    $student->lessons()->syncWithoutDetaching([
                        $lesson->id => [
                            'is_completed' => true,
                            'completed_at' => now()->subDays(rand(0, 10)),
                            'progress' => 100,
                        ],
                    ]);
                }
            }
        }
    }
}
