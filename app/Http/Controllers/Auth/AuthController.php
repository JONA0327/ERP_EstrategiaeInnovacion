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
            return redirect()->route('welcome')->with('success', '¡Bienvenido de vuelta!');
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
