<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is used to bind services into the application's service container.
     * In this case, no services are registered.
     *
     * @return void
     */
    public function register()
    {
        // No services are being registered in this method.
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other services have been registered.
     * It is used to perform any necessary bootstrapping tasks for the application.
     * In this case, it ensures that all sessions are cleared when the application starts.
     *
     * @return void
     */
    public function boot()
    {
        if (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
            $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        ) {
            URL::forceScheme('https');
        }
    }
}