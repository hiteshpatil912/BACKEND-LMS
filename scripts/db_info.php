<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;

$result = [];
$result['env_db_connection'] = config('database.default');
$conf = config('database.connections.' . $result['env_db_connection']);
$result['env_db_database'] = $conf['database'] ?? null;

// Teachers
$result['teachers'] = User::where('role', 'teacher')->get(['id','name','email'])->toArray();

$courseIds = [2,3,4,5];
foreach ($courseIds as $id) {
    $course = Course::find($id);
    $result['courses'][$id] = $course ? $course->toArray() : null;
    $result['lessons_count'][$id] = Lesson::where('course_id', $id)->count();
}

echo json_encode($result, JSON_PRETTY_PRINT);
