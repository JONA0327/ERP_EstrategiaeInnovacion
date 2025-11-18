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
        $area = $user?->empleado?->area;
        $norm = $area ? mb_strtolower(preg_replace('/\s+/u',' ',$area),'UTF-8') : null;
        if (!$user || ($norm !== 'logistica' && $norm !== 'logística')) {
            return redirect()->route('login')->with('info','Acceso restringido a Logística');
        }
        return $next($request);
    }
}
