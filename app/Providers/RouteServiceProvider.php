<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * This method is used to register any necessary routes for the application and apply middleware to the routes.
     *
     * @return void
     */
    public function boot()
    {
        // Configure the rate limiters for the application
        $this->configureRateLimiting();

        // Define the routes for the application
        $this->routes(function () {
            // API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * This method sets up the rate limiting for the API routes based on the user's ID or IP address.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Define rate limiting for the API
        RateLimiter::for('api', function (Request $request) {
            // Allow 60 requests per minute, and identify users by their user ID or IP address
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}