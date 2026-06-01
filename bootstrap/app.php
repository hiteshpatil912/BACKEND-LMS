<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\StudentMiddleware;
use App\Http\Middleware\TeacherMiddleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'teacher' => TeacherMiddleware::class,
            'student' => StudentMiddleware::class,
        ]);

    })
 ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
        Illuminate\Auth\AuthenticationException $e,
        $request
    ) {

        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);

    });

    })->create();
