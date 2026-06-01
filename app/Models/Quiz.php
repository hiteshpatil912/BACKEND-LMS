<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lesson;
use App\Models\Question;

class Quiz extends Model
{
    protected $fillable = [
    'lesson_id',
    'title',
    'description',
    'total_marks',
];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
       public function questions()
{
    return $this->hasMany(Question::class);
}
}
