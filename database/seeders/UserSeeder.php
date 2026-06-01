<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Teachers
        User::factory(3)->create([
            'role' => 'teacher',
        ]);

        // Students
        User::factory(10)->create([
            'role' => 'student',
        ]);
    }
}