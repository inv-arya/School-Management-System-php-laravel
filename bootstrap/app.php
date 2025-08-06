<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

    
    
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
            $exceptions->renderable(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json(['message' => 'Unauthorized - No token provided'], 401);
    }
    });
})->create();