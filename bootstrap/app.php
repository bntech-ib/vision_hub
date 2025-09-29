<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function () {
            // Define rate limiter for 'auth'
            RateLimiter::for('auth', function ($request) {
                return Limit::perMinute(5)->by(optional($request->user())->id ?: $request->ip());
            });
            
            // Define rate limiter for 'api'
            RateLimiter::for('api', function ($request) {
                return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
            });
            
            // Define rate limiter for 'uploads'
            RateLimiter::for('uploads', function ($request) {
                return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
            });

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