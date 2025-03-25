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
     * This middleware ensures the presence and validity of the 'user_session' cookie,
     * and that the authenticated user session matches it.
     */
    public function handle(Request $request, Closure $next)
    {
        $currentUserSession = $request->cookie('user_session');
        $storedUserSession = session('user_session_token');

        // Caso 1: El usuario no está autenticado pero la cookie 'user_session' existe → inválido
        if (!Auth::check() && $currentUserSession) {
            return redirect()->route('login')
                ->withCookie(cookie()->forget('user_session'))
                ->withErrors(['error' => 'La sesión ha expirado o no es válida.']);
        }

        // Caso 2: La cookie está ausente mientras hay sesión activa → cerrar sesión
        if (Auth::check() && !$currentUserSession) {
            Auth::logout();
            return redirect()->route('login')
                ->withCookie(cookie()->forget('laravel_session'))
                ->withErrors(['error' => 'Sesión inválida, vuelve a iniciar sesión.']);
        }

        // Caso 3: La cookie cambió (posible manipulación)
        if ($storedUserSession && $currentUserSession !== $storedUserSession) {
            Auth::logout();
            return redirect()->route('login')
                ->withCookie(cookie()->forget('user_session'))
                ->withCookie(cookie()->forget('laravel_session'))
                ->withErrors(['error' => 'Tu sesión ha sido modificada o ha expirado.']);
        }

        // Caso válido: todo bien, almacenar la sesión actual
        session(['user_session_token' => $currentUserSession]);

        return $next($request);
    }
}