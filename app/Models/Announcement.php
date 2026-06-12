<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Announcement extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'body',
        'published_at',
    ];

    protected $dates = [
        'published_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
