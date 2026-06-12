<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Lesson;

$result = [];
$courseIds = [2,3,4,5];
foreach ($courseIds as $id) {
    $query = Lesson::where('course_id', $id)->orderBy('lesson_order');
    $result[$id]['sql'] = $query->toSql();
    $result[$id]['bindings'] = $query->getBindings();
    $result[$id]['count'] = $query->count();
    $result[$id]['rows'] = $query->limit(10)->get()->map(function($r){ return ['id'=>$r->id,'course_id'=>$r->course_id,'lesson_order'=>$r->lesson_order,'title'=>$r->title]; })->toArray();
}

echo json_encode($result, JSON_PRETTY_PRINT);
