<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
      protected $fillable = [

        'user_id',

        'course_id',

        'lesson_id',

        'watch_time'

    ];
}
