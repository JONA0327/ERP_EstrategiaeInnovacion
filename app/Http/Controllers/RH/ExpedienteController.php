<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::query()->with('user');
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('correo', 'like', "%$search%")
                  ->orWhere('area', 'like', "%$search%");
            });
        }
        $empleados = $query->orderBy('nombre')->paginate(15)->withQueryString();
        return view('Recursos_Humanos.expedientes.index', compact('empleados'));
    }

    public function show(Empleado $empleado)
    {
        return view('Recursos_Humanos.expedientes.show', compact('empleado'));
    }

    public function edit(Empleado $empleado)
    {
        return view('Recursos_Humanos.expedientes.edit', compact('empleado'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'area' => ['nullable','string','max:100'],
            'id_empleado' => ['nullable','string','max:30'],
        ]);
        $empleado->update($data);
        return redirect()->route('rh.expedientes.index')->with('success', 'Expediente actualizado');
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();
        return redirect()->route('rh.expedientes.index')->with('success', 'Expediente eliminado');
    }

    public function refresh()
    {
        // Crear expedientes para usuarios sin registro en empleados
        $existingUserIds = Empleado::pluck('user_id')->all();
        $nuevos = User::whereNotIn('id', $existingUserIds)->get();
        DB::transaction(function () use ($nuevos) {
            foreach ($nuevos as $user) {
                Empleado::create([
                    'user_id' => $user->id,
                    'nombre' => $user->name,
                    'correo' => $user->email,
                    'area' => null,
                ]);
            }
        });
        return redirect()->route('rh.expedientes.index')->with('success', 'Expedientes sincronizados');
    }
}
