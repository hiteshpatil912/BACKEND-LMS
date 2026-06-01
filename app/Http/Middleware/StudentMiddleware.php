<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !in_array($request->user()->role, ['admin', 'student'], true)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return $next($request);
    }
}
