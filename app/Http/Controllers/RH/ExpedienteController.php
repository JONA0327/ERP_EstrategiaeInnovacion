<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\EmpleadoDocumento; // Modelo nuevo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::query();
        
        if ($request->search) {
            $query->where('nombre', 'like', "%{$request->search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$request->search}%");
        }

        // Cargamos documentos para contar cuantos tiene cada uno rápido
        $empleados = $query->withCount('documentos')->paginate(12);

        return view('Recursos_Humanos.expedientes.index', compact('empleados'));
    }

    public function show($id)
    {
        // Cargamos al empleado con sus documentos ordenados
        $empleado = Empleado::with('documentos')->findOrFail($id);
        
        // Agrupamos documentos por categoría para la vista
        $docsGrouped = $empleado->documentos->groupBy('categoria');

        return view('Recursos_Humanos.expedientes.show', compact('empleado', 'docsGrouped'));
    }

    // --- NUEVA FUNCIÓN: SUBIR DOCUMENTO ---
    public function uploadDocument(Request $request, $empleadoId)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120', // Max 5MB
            'nombre' => 'required|string',
            'categoria' => 'required|string',
            'fecha_vencimiento' => 'nullable|date'
        ]);

        $empleado = Empleado::findOrFail($empleadoId);
        $file = $request->file('documento');
        
        // Guardar en carpeta privada o publica según seguridad (aquí ejemplo pública)
        $path = $file->storeAs(
            "expedientes/{$empleado->id}", 
            \Str::slug($request->nombre) . '_' . time() . '.' . $file->getClientOriginalExtension(), 
            'public'
        );

        EmpleadoDocumento::create([
            'empleado_id' => $empleado->id,
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'ruta_archivo' => $path,
            'fecha_vencimiento' => $request->fecha_vencimiento
        ]);

        return back()->with('success', 'Documento archivado correctamente.');
    }

    public function deleteDocument($id)
    {
        $doc = EmpleadoDocumento::findOrFail($id);
        Storage::disk('public')->delete($doc->ruta_archivo);
        $doc->delete();
        
        return back()->with('success', 'Documento eliminado.');
    }

    public function importFormatoId(Request $request, $id)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $empleado = Empleado::findOrFail($id);
        $file = $request->file('archivo_excel');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $data = [];

            foreach ($rows as $row) {
                // 1. Obtenemos la etiqueta y el valor
                $rawLabel = $row[0] ?? '';
                $value = $row[1] ?? null;

                // 2. Limpieza PROFUNDA de la etiqueta
                // "Teléfono de Casa:" -> "telefono-de-casa" -> "telefonodecasa"
                $label = str_replace('-', '', Str::slug($rawLabel)); 
                
                if (!$value || $value == 'No llenar - sera llenado por Administracion y RH') continue;

                // 3. Mapeo exacto basado en tu CSV
                
                // Dirección
                if (str_contains($label, 'direccionactual')) $data['direccion'] = $value;
                if ($label == 'ciudad') $data['ciudad'] = $value;
                if ($label == 'estado') $data['estado_federativo'] = $value;
                if (str_contains($label, 'codigopostal')) $data['codigo_postal'] = $value;
                
                // Teléfonos
                if (str_contains($label, 'telefonocelular')) $data['telefono'] = $value;
                if (str_contains($label, 'telefonodecasa')) $data['telefono_casa'] = $value;
                
                // Salud
                if (str_contains($label, 'alergias')) $data['alergias'] = $value;
                if (str_contains($label, 'enfermedades')) $data['enfermedades_cronicas'] = $value;
                
                // Emergencia
                // Buscamos "no de contacto" primero para que no se confunda con el nombre
                if (str_contains($label, 'nodecontacto')) {
                    $data['contacto_emergencia_numero'] = $value;
                } elseif (str_contains($label, 'contactodeemergencia')) {
                    $data['contacto_emergencia_nombre'] = $value;
                }
                
                if (str_contains($label, 'parentesco')) {
                    $data['contacto_emergencia_parentesco'] = $value;
                }
            }

            // Guardamos
            $empleado->update($data);

            return back()->with('success', '¡Información importada correctamente! Se actualizaron campos de contacto y domicilio.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el Excel: ' . $e->getMessage());
        }
    }
}