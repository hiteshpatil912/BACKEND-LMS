<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Course;
use App\Models\Lesson;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // use HasFactory, Notifiable;
     use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Courses where this user is enrolled as a student.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    /**
     * All lessons that have a progress row for this user.
     */
    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')
            ->withPivot(['is_completed', 'completed_at', 'progress'])
            ->withTimestamps();
    }

    /**
     * Lessons completed by this user.
     */
    public function completedLessons()
    {
        return $this->lessons()->wherePivot('is_completed', true);
    }

}
