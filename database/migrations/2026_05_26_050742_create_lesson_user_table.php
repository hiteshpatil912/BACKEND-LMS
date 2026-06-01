<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_user', function (Blueprint $table) {
             $table->id();

            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Whether the lesson was completed by the user
            $table->boolean('is_completed')->default(false);

            // When the lesson was completed (nullable when not completed)
            $table->timestamp('completed_at')->nullable();

            // Optional granular progress per lesson (0-100)
            $table->unsignedSmallInteger('progress')->default(0);

            $table->timestamps();

            // Prevent duplicate entries for the same user and lesson
            $table->unique(['lesson_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_user');
    }
};
