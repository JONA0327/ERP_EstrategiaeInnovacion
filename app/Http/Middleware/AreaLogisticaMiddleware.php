<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AreaLogisticaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Permitir acceso a administradores
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }
        
        // Verificar si la posición contiene "logistica" (más flexible)
        $posicion = $user?->empleado?->posicion;
        $norm = $posicion ? mb_strtolower(preg_replace('/\s+/u',' ',$posicion),'UTF-8') : null;
        
        if (!$user || !$norm || (stripos($norm, 'logistic') === false)) {
            return redirect()->route('login')->with('info','Acceso restringido a Logística');
        }
        
        return $next($request);
    }
}
