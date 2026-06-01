<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Course;

class Certificate extends Model
{
    protected $fillable = [
    'user_id',
    'course_id',
    'certificate_no',
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
