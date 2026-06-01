<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;   
use App\Models\Course;

class Discussion extends Model
{
      protected $fillable = [

        'user_id',

        'course_id',

        'message'

    ];
public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
