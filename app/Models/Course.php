<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lesson;
use App\Models\User;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'price',
        'status',
        'user_id',
    ];
    /**
     * Teacher who owns this course.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Students enrolled in this course through course_user.
     */
    public function students()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Lessons that belong to this course.
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_order');
    }
}
