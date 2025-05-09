<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'log.activity' => \App\Http\Middleware\LogApiActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (AuthenticationException $exception, Request $request) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        });
    })->create();
