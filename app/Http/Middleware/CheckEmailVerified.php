<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the authenticated user has verified their email.
     * If the email is not verified, the user will be logged out and redirected to the login page.
     * A session cookie will also be cleared in this case.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // If the user's email is not verified, log them out
            if (!$user->email_verified) {
                // Log out the user
                Auth::logout();
                
                // Forget the 'user_session' cookie
                return redirect()
                    ->route('login')
                    ->withCookie(cookie()->forget('user_session'))
                    ->with('error', 'Your account is not verified. Please verify your email address.');
            }
        }

        // Proceed to the next middleware if the user is authenticated and email is verified
        return $next($request);
    }
}