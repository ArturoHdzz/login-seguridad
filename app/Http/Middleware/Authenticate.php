<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Check if the request does not have a valid 'user_session' cookie
        if (!$request->hasCookie('user_session')) {
            // If no session cookie is found, redirect the user to the login page
            return route('login');
        }
        // If the user is authenticated (session cookie exists), no redirection occurs
        return null;
    }
}
