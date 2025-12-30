<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Capacitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CapacitacionController extends Controller
{
    // Vista para TODOS los empleados (Galería)
    public function index()
    {
        $videos = Capacitacion::where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('Recursos_Humanos.capacitacion.index', compact('videos'));
    }

    // Vista para VER un video específico
    public function show($id)
    {
        $video = Capacitacion::findOrFail($id);
        return view('Recursos_Humanos.capacitacion.show', compact('video'));
    }

    // --- ÁREA DE ADMINISTRACIÓN (SOLO RH) ---

    public function manage()
    {
        $videos = Capacitacion::orderBy('created_at', 'desc')->get();
        return view('Recursos_Humanos.capacitacion.manage', compact('videos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'video' => 'required|mimes:mp4,mov,ogg,qt|max:200000', // Máx 200MB (ajustar php.ini)
        ]);

        try {
            // Guardar el archivo en storage/app/public/capacitacion
            $path = $request->file('video')->store('capacitacion', 'public');

            Capacitacion::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'archivo_path' => $path,
                'subido_por' => Auth::id(),
            ]);

            return redirect()->route('rh.capacitacion.manage')->with('success', 'Video subido correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir el video: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $video = Capacitacion::findOrFail($id);
        
        // Eliminar archivo físico
        if (Storage::disk('public')->exists($video->archivo_path)) {
            Storage::disk('public')->delete($video->archivo_path);
        }
        
        $video->delete();
        return back()->with('success', 'Video eliminado.');
    }
}