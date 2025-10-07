<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\SupportOption;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        // Explicit route model binding for support options
        Route::bind('support', function ($value) {
            return SupportOption::findOrFail($value);
        });

        // You can also use implicit binding by matching the route parameter name with the method parameter name
        // But explicit binding gives you more control
    }
}