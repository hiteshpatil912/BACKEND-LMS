<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Assignment extends Model
{
     protected $fillable = [

        'course_id',

        'title',

        'description',

        'due_date'

    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
