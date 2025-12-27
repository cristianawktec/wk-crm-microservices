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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(remove: [
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        // Ensure JSON bodies are parsed for all API requests
        $middleware->api(prepend: [
            // Add CORS first so preflight requests are handled quickly
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\EnsureJsonBodyIsParsed::class,
        ]);

        // Register custom middlewares
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'auth.query.token' => \App\Http\Middleware\AuthenticateQueryToken::class,
            'auth.bearer.or.query' => \App\Http\Middleware\AuthBearerOrQueryToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\CrmServiceProvider::class,
    ])
    ->create();