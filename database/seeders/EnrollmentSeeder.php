<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::all();

        foreach ($students as $student) {

            $randomCourses = $courses->random(rand(1, 3));

            foreach ($randomCourses as $course) {
                $student->courses()->syncWithoutDetaching($course->id);
            }
        }
    }
}