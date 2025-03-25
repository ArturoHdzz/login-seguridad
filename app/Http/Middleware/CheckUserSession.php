<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class CheckUserSession
{
      /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return $this->handleUnauthenticated($request);
            }

            // Retrieve the 'user_session' cookie
            $userSessionCookie = $request->cookie('user_session');

            // If no cookie is present, force logout
            if (!$userSessionCookie) {
                return $this->handleUnauthenticated($request);
            }

            // Decrypt the cookie value
            try {
                $decryptedUserId = Crypt::decrypt($userSessionCookie);
            } catch (\Exception $e) {
                Log::error('User session cookie decryption failed: ' . $e->getMessage());
                return $this->handleUnauthenticated($request);
            }

            // Verify that the decrypted user ID matches the currently authenticated user
            if ($decryptedUserId !== Auth::id()) {
                return $this->handleUnauthenticated($request);
            }

            return $next($request);
        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Unexpected error in CheckUserSession middleware: ' . $e->getMessage());
            return $this->handleUnauthenticated($request);
        }
    }

    /**
     * Handle unauthenticated requests
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleUnauthenticated(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['error' => 'Your session has expired. Please log in again.'])
            ->withCookie(cookie()->forget('user_session'))
            ->withCookie(cookie()->forget('laravel_session'));
    }
}