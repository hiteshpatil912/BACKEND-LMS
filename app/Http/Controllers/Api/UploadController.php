<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPdfRequest;
use App\Http\Requests\UploadThumbnailRequest;
use App\Http\Requests\UploadVideoRequest;

class UploadController extends Controller
{
    public function thumbnail(UploadThumbnailRequest $request)
    {
        $path = $request->file('thumbnail')->store('thumbnails', 'public');

        return response()->json([
            'message' => 'Thumbnail Uploaded Successfully',
            'thumbnail_url' => asset('storage/' . $path),
        ]);
    }

    public function pdf(UploadPdfRequest $request)
    {
        $path = $request->file('pdf')->store('pdfs', 'public');

        return response()->json([
            'message' => 'PDF Uploaded Successfully',
            'pdf_url' => asset('storage/' . $path),
        ]);
    }

    public function video(UploadVideoRequest $request)
    {
        $path = $request->file('video')->store('videos', 'public');

        return response()->json([
            'message' => 'Video Uploaded Successfully',
            'video_url' => asset('storage/' . $path),
        ]);
    }
}
