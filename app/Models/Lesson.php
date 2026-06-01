<?php

namespace App\Models;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'video_url',
        'pdf_notes',
        'lesson_order',
        'is_preview',
    ];

    /**
     * Course that owns this lesson.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Users who have a progress row for this lesson.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'lesson_user')
            ->withPivot(['is_completed', 'completed_at', 'progress'])
            ->withTimestamps();
    }

    /**
     * Users who completed this lesson.
     */
    public function completedByUsers()
    {
        return $this->users()->wherePivot('is_completed', true);
    }

}
