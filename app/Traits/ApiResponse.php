<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(
        string $message = 'Error',
        int $status = 400,
        mixed $errors = null
    ) {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        ResourceCollection $resource,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ) {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $resource,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], $meta), $status);
    }
}
