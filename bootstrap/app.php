<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Retire le CORS natif
        $middleware->remove(\Illuminate\Http\Middleware\HandleCors::class);

        // ✅ Ajoute CORS sur TOUT (web + api)
        $middleware->prepend(\App\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function ($exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return response()->json(['message' => 'Non authentifié'], 401);
        });
    })
    ->create();