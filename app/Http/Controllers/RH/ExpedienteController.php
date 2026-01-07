<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\EmpleadoDocumento;
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

        // Usamos 'with' para traer los documentos y calcular el % óptimamente
        $empleados = $query->with('documentos')->paginate(12);

        return view('Recursos_Humanos.expedientes.index', compact('empleados'));
    }

    public function show($id)
    {
        $empleado = Empleado::with('documentos')->findOrFail($id);
        
        // Agrupamos documentos por categoría para la vista
        $docsGrouped = $empleado->documentos->groupBy('categoria');

        // Preparamos la lista de checklist para la vista según el tipo
        if ($empleado->es_practicante) {
            $checklistDocs = ['INE', 'CURP', 'Comprobante de Domicilio', 'Estado de Cuenta', 'Formato ID', 'Contrato'];
        } else {
            $checklistDocs = ['INE', 'CURP', 'Comprobante de Domicilio', 'NSS', 'Titulo', 'Constancia de Situacion Fiscal', 'Formato ID', 'Contrato'];
        }

        return view('Recursos_Humanos.expedientes.show', compact('empleado', 'docsGrouped', 'checklistDocs'));
    }

    /**
     * Método UPDATE para actualizar datos del expediente.
     * Utiliza la ruta existente: Route::put('/{empleado}', ...)->name('update');
     */
    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        // Si estamos enviando el formulario del switch de practicante
        if ($request->has('toggle_practicante')) {
            // El checkbox envía '1' si está marcado. Si no está marcado, Laravel $request->boolean maneja el false si usamos input hidden o validamos.
            // Aquí usamos la técnica de input hidden previo con valor 0 para asegurar el envío.
            $empleado->es_practicante = $request->boolean('es_practicante');
            $empleado->save();

            return back()->with('success', 'Tipo de expediente actualizado correctamente.');
        }

        // Si hubiera lógica para actualización general de datos del empleado, iría aquí.
        $empleado->update($request->all());

        return back()->with('success', 'Información actualizada.');
    }

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
                $rawLabel = $row[0] ?? '';
                $value = $row[1] ?? null;

                $label = str_replace('-', '', Str::slug($rawLabel)); 
                
                if (!$value || $value == 'No llenar - sera llenado por Administracion y RH') continue;

                if (str_contains($label, 'direccionactual')) $data['direccion'] = $value;
                if ($label == 'ciudad') $data['ciudad'] = $value;
                if ($label == 'estado') $data['estado_federativo'] = $value;
                if (str_contains($label, 'codigopostal')) $data['codigo_postal'] = $value;
                
                if (str_contains($label, 'telefonocelular')) $data['telefono'] = $value;
                if (str_contains($label, 'telefonodecasa')) $data['telefono_casa'] = $value;
                
                if (str_contains($label, 'alergias')) $data['alergias'] = $value;
                if (str_contains($label, 'enfermedades')) $data['enfermedades_cronicas'] = $value;
                
                if (str_contains($label, 'nodecontacto')) {
                    $data['contacto_emergencia_numero'] = $value;
                } elseif (str_contains($label, 'contactodeemergencia')) {
                    $data['contacto_emergencia_nombre'] = $value;
                }
                
                if (str_contains($label, 'parentesco')) {
                    $data['contacto_emergencia_parentesco'] = $value;
                }
            }

            $empleado->update($data);

            return back()->with('success', '¡Información importada correctamente!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el Excel: ' . $e->getMessage());
        }
    }
}