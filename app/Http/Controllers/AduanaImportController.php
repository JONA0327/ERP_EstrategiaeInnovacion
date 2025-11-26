<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AduanaImportService;
use App\Models\Logistica\Aduana;
use Illuminate\Support\Facades\Storage;

class AduanaImportController extends Controller
{
    protected $aduanaImportService;

    public function __construct(AduanaImportService $aduanaImportService)
    {
        $this->aduanaImportService = $aduanaImportService;
    }

    /**
     * Importar aduanas desde archivo Word
     */
    public function import(Request $request)
    {
        try {
            // Validar el archivo subido
            $request->validate([
                'file' => 'required|file|mimes:docx,doc|max:10240' // Máximo 10MB
            ]);

            // Guardar el archivo temporalmente
            $file = $request->file('file');
            $path = $file->storeAs('temp/imports', 'aduanas_' . time() . '.' . $file->getClientOriginalExtension());
            $fullPath = storage_path('app/' . $path);

            // Procesar la importación
            $result = $this->aduanaImportService->import($fullPath);

            // Limpiar el archivo temporal
            Storage::delete($path);

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo inválido: ' . implode(', ', $e->validator->errors()->all()),
                'total_processed' => 0,
                'total_imported' => 0
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la importación: ' . $e->getMessage(),
                'total_processed' => 0,
                'total_imported' => 0
            ], 500);
        }
    }

    /**
     * Obtener lista de aduanas
     */
    public function index(Request $request)
    {
        try {
            $query = Aduana::query();

            // Filtro por búsqueda
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('denominacion', 'LIKE', "%{$search}%")
                      ->orWhere('aduana', 'LIKE', "%{$search}%")
                      ->orWhere('patente', 'LIKE', "%{$search}%");
                });
            }

            // Filtro por país
            if ($request->has('pais') && !empty($request->pais)) {
                $query->where('pais', $request->pais);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'aduana');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $aduanas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'aduanas' => $aduanas,
                'stats' => $this->aduanaImportService->getStats()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las aduanas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una aduana
     */
    public function destroy($id)
    {
        try {
            $aduana = Aduana::findOrFail($id);
            $aduana->delete();

            return response()->json([
                'success' => true,
                'message' => 'Aduana eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la aduana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar todas las aduanas
     */
    public function clear()
    {
        try {
            $count = Aduana::count();
            Aduana::truncate();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} aduanas exitosamente",
                'deleted_count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar las aduanas: ' . $e->getMessage()
            ], 500);
        }
    }
}
