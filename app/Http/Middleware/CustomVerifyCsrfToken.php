<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class CustomVerifyCsrfToken extends Middleware
{
    protected $addHttpCookie = false;

    protected $except = [];

    public function handle($request, Closure $next)
    {
        // Verificar CSRF
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            $response = $next($request);
            
            // Generar nuevo token CSRF
            $token = $request->session()->token() ?? $this->getTokenFromSession($request);
            
            // Guardar token en la sesión
            $request->session()->put('_token', $token);
            
            // Asegurar que no se establezca la cookie XSRF-TOKEN
            if (method_exists($response, 'withoutCookie')) {
                $response->withoutCookie('XSRF-TOKEN');
            }
            
            return $response;
        }

        throw new TokenMismatchException('CSRF token mismatch.');
    }

    protected function getTokenFromSession($request)
    {
        return $request->session()->token() ?? $this->generateToken();
    }

    protected function generateToken()
    {
        return bin2hex(random_bytes(32));
    }
}