<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware is responsible for ensuring that users are verified
     * either after registration or login. It checks if the user has completed
     * the necessary verification step, such as verifying their email.
     *
     * If verification is required, the user is redirected to the appropriate page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // For registration verification
        // If the route is for registration verification and the email is not verified, redirect to registration form
        if ($request->routeIs('verification.registration') && !session('verify_email')) {
            return redirect()->route('registration.show');
        }
        
        // For login verification
        // If the route is for login verification and the email is not verified, redirect to the login page
        if ($request->routeIs('login.verification') && !session('login_email')) {
            return redirect()->route('login');
        }

        // If no conditions are met, continue with the request
        return $next($request);
    }
}