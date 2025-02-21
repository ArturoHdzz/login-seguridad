<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HandleUserSession
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the user is not authenticated but has a valid session cookie (`user_session`).
     * If a valid `user_session` cookie is found, it attempts to decrypt the user ID from the cookie.
     * If successful, the user is logged in using the decrypted ID.
     * In case of any error while decrypting the cookie, the session cookie is removed and the user is redirected to the login page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is not authenticated and the request has the 'user_session' cookie
        if (!Auth::check() && $request->hasCookie('user_session')) {
            try {
                // Attempt to decrypt the user ID from the cookie
                $userId = decrypt($request->cookie('user_session'));
                
                // Find the user by the decrypted ID
                $user = User::find($userId);
                
                // If the user is found, log them in
                if ($user) {
                    Auth::login($user);
                }
            } catch (\Exception $e) {
                // If there is an error while decrypting the cookie, remove the 'user_session' cookie
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('user_session'));
            }
        }

        // Proceed to the next middleware if the user is authenticated or no valid cookie is found
        return $next($request);
    }
}