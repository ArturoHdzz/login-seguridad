<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;

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
        // Clear all sessions at the start of the application
        // This check ensures that the action is only executed in a web request context
        if (!app()->runningInConsole()) {
            // Check if the session does not already contain a '_token' key
            if (!Session::has('_token')) {
                // Flush all session data
                Session::flush();
                
                // Regenerate the session ID to ensure security
                Session::regenerate();
            }
        }
    }
}