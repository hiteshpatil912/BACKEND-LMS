<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Models\Course;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    public function store(StoreWishlistRequest $request, int $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return $this->errorResponse('Course Not Found', 404);
        }

        $user = $request->user();

        $existingWishlist = Wishlist::where('user_id', $user->id)
            ->where('course_id', $id)
            ->first();

        if ($existingWishlist) {
            return $this->errorResponse('Course Already In Wishlist', 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'course_id' => $id,
        ]);

        return $this->successResponse([
            'wishlist' => new WishlistResource($wishlist->load('course.teacher', 'user')),
        ], 'Course Added To Wishlist');
    }

    public function myWishlist(Request $request)
    {
        $wishlists = Wishlist::where('user_id', $request->user()->id)
            ->with('course.teacher')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse(
            $wishlists,
            WishlistResource::collection($wishlists),
            'Wishlists fetched successfully'
        );
    }
}
