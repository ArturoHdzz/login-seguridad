<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;

class CustomStartSession extends StartSession
{
    protected $publicRoutes = [
        'login',
        'registro.mostrar',
        'login.paso1',
        'registro'
    ];

    public function handle($request, Closure $next)
    {
        if (!$this->sessionConfigured()) {
            return $next($request);
        }

        // Generar ID único para la sesión
        $sessionId = $this->generateSessionId();
        
        // Configurar el driver de sesión a file
        config(['session.driver' => 'file']);
        
        $session = $this->getSession($request);
        $session->setId($sessionId);
        
        // Iniciar la sesión
        $request->setLaravelSession($session);
        $session->start();

        // Procesar la solicitud
        $response = $next($request);

        // Guardar la sesión
        if ($this->shouldSaveSession($request, $response)) {
            $session->save();
        }

        // Eliminar cookie de sesión de Laravel
        if (method_exists($response, 'withoutCookie')) {
            $response->withoutCookie('laravel_session');
        }

        return $response;
    }

    protected function shouldSaveSession($request, $response): bool
    {
        return (!$this->isReading($request) || $this->sessionHasChanges($request));
    }

    protected function sessionHasChanges($request): bool
    {
        $session = $request->session();
        return !empty($session->all()) || $session->hasOldInput();
    }

    protected function generateSessionId(): string
    {
        return sha1(uniqid('', true));
    }

    protected function isReading($request): bool
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }
}