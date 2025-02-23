<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificationStateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that users are redirected back to the login page
     * if they have not started the login process properly. If the route is for
     * login verification and the email verification session is not set,
     * the user will be asked to re-initiate the login process.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the current route is for login verification
        if ($request->routeIs('login.verification')) {
            // Check if the session does not have the 'login_email' key, meaning the login process was not started
            if (!session()->has('login_email')) {
                // Redirect the user to the login page with an error message
                return redirect()->route('login')
                    ->withErrors(['error' => 'You must restart the login process.']);
            }
        }

        // If the conditions are not met, continue with the request
        return $next($request);
    }
}