<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BlockedEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required','string','email','max:255',
                'unique:users,email','unique:blocked_emails,email',
            ],
            'password' => [
                'required','confirmed','string','min:8','max:16','regex:/^(?=.*[0-9])(?=.*[\W_]).+$/',
            ],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => User::STATUS_PENDING,
        ]);

        return redirect()->route('login')
            ->with('status', 'Tu solicitud de registro fue enviada exitosamente. Un administrador revisará tu cuenta.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (BlockedEmail::where('email', $credentials['email'])->exists()) {
            return back()->withErrors(['email' => 'Este correo ha sido bloqueado.'])->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();
        if ($user && $user->status === User::STATUS_PENDING) {
            return back()->withErrors(['email' => 'Tu cuenta está en revisión.'])->onlyInput('email');
        }
        if ($user && $user->status === User::STATUS_REJECTED) {
            return back()->withErrors(['email' => 'Tu solicitud fue rechazada.'])->onlyInput('email');
        }

        if ($user && Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $area = optional($user->empleado)->area;

            // Invitados no ingresan a módulos; redirige a portada
            if ($user->role === 'invitado') {
                return redirect()->route('welcome')->with('success', 'Acceso como invitado');
            }

            // Área Sistemas: distingue admin vs resto (tickets panel separado)
            if ($area === 'Sistemas') {
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Acceso administrativo Sistemas');
                }
                return redirect()->route('tickets.mis-tickets')->with('success', 'Centro de tickets');
            }

            // Áreas con vista dedicada
            if ($area === 'Logistica') {
                return redirect()->route('logistica.index')->with('success', 'Acceso Logística');
            }

            if ($area === 'RH') {
                return redirect()->route('recursos-humanos.index')->with('success', 'Acceso Recursos Humanos');
            }

            // Área Comercio Exterior u otras: por ahora tickets si existe módulo mantenimiento; fallback welcome
            if ($area === 'Comercio Exterior') {
                return redirect()->route('tickets.mis-tickets')->with('success', 'Acceso módulo de tickets');
            }

            // Otras áreas aprobadas sin interfaz o sin área definida: tickets
            if ($area) {
                return redirect()->route('tickets.mis-tickets')->with('success', 'Centro de tickets');
            }

            // Sin área asignada: tickets
            return redirect()->route('tickets.mis-tickets')->with('success', 'Centro de tickets');
        }

        return back()->withErrors(['email' => 'Credenciales inválidas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('welcome')->with('success', 'Sesión cerrada correctamente.');
    }
}
