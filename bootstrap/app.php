<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        // Register custom service providers
        \App\Providers\RateLimiterServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function () {
            Route::middleware(['web', 'auth', 'admin'])
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
        // Register global middleware
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);
        $middleware->append(\App\Http\Middleware\ThreatDetectionMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();