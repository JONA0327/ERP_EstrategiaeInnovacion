<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Seguridad: si por alguna razón no hay usuario autenticado, vuelve a login
        if (!$user) {
            return redirect()->route('login');
        }

        // Normaliza el área desde el perfil de empleado (si existe)
        $areaRaw = optional($user->empleado)->area;
        $area = $areaRaw ? mb_strtolower(preg_replace('/\s+/u', ' ', $areaRaw), 'UTF-8') : null;

        // Invitados: solo portada ERP/Tickets
        if ($user->role === 'invitado') {
            return redirect()->route('welcome');
        }

        // Redirecciones por área con prioridad a vistas propias
        if ($area === 'rh' || $area === 'recursos humanos') {
            return redirect()->route('recursos-humanos.index');
        }

        if ($area === 'logistica' || $area === 'logística') {
            return redirect()->route('logistica.index');
        }

        if ($area === 'sistemas') {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('tickets.mis-tickets');
        }

        // Comercio Exterior
        if ($area === 'comercio exterior') {
            return redirect()->route('tickets.mis-tickets');
        }

        // Cualquier otra área aprobada o sin área: ir al centro de tickets
        return redirect()->route('tickets.mis-tickets');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
