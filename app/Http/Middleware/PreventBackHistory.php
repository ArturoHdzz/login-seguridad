<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * This middleware prevents users from using the back button in the browser 
     * to access cached pages, ensuring that they are redirected to the login page 
     * or other routes when they try to go back in their browser history.
     * 
     * It achieves this by setting headers that prevent caching of the response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        
        return $response;
    }
}