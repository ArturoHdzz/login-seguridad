<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks the presence of the `user_session` cookie in the request.
     * If the cookie is missing or its value has changed, the user's session is logged out and they are redirected to the login page.
     * If the cookie is present and its value has not changed, the request is allowed to proceed.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the 'user_session' cookie is present
        if (!$request->hasCookie('user_session')) {
            // If the cookie is not present, log the user out
            Auth::logout();

            // Redirect to the login page and forget the session cookie
            return redirect()->route('login')->withCookie(cookie()->forget('laravel_session'));
        }

        // Retrieve the current value of the 'user_session' cookie
        $currentUserSession = $request->cookie('user_session');
        
        // Retrieve the stored session token
        $storedUserSession = session('user_session_token');

        // Check if the stored session token is available and if it has changed
        if ($storedUserSession && $currentUserSession !== $storedUserSession) {
            // If the session token has changed, log the user out
            Auth::logout();

            // Redirect to the login page, forget the session cookie, and display an error message
            return redirect()->route('login')
                ->withCookie(cookie()->forget('laravel_session'))
                ->withErrors(['error' => 'The session has expired or been modified.']);
        }

        // If the cookie has not changed, store its value for future comparisons
        session(['user_session_token' => $currentUserSession]);

        // If the cookie is present and has not changed, proceed with the request
        return $next($request);
    }
}