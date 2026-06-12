<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // DEBUG: log user and bearer token to troubleshoot 403 issues
        try {
            logger()->info('TeacherMiddleware debug', [
                'user' => $request->user(),
                'bearer' => $request->bearerToken(),
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures in middleware
        }

        if (!$request->user() || !in_array($request->user()->role, ['admin', 'teacher'], true)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return $next($request);
    }
}
