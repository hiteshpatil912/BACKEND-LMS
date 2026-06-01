<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LessonCollection extends ResourceCollection
{
    public $collects = LessonResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
