<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscussionRequest;
use App\Models\Discussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function store(StoreDiscussionRequest $request)
    {
        $discussion = Discussion::create([
            'user_id' => $request->user()->id,
            'course_id' => $request->course_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Discussion Added Successfully',
            'discussion' => $discussion,
        ]);
    }

    public function chatDiscussion(Request $request)
    {
        return response()->json([
            'message' => 'Discussion Created Successfully',
            'discussion' => [
                'id' => rand(1, 9999),
                'title' => $request->title,
                'message' => $request->message,
                'created_by' => $request->user()->name,
            ],
        ]);
    }
}
