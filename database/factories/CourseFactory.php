<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'title' => fake()->sentence(3),
    'description' => fake()->paragraph(),
    'thumbnail' => null,
    'price' => fake()->randomFloat(2, 499, 4999),
    'status' => 'published',
    'user_id' => User::where('role', 'teacher')->inRandomOrder()->first()->id,
        ];
    }
}
