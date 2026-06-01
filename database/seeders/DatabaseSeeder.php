<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
     $this->call([
    UserSeeder::class,
    CourseSeeder::class,
    LessonSeeder::class,
    EnrollmentSeeder::class,
    ProgressSeeder::class,
    QuizSeeder::class,
    QuestionSeeder::class,
]);
    }
}
