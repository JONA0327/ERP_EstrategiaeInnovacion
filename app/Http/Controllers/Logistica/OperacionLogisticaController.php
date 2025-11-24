<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\Cliente;
use App\Models\Logistica\AgenteAduanal;
use App\Models\Logistica\Transporte;
use App\Models\Empleado;

class OperacionLogisticaController extends Controller
{
    public function index()
    {
        $operaciones = OperacionLogistica::with(['ejecutivo', 'cliente', 'agenteAduanal', 'transporte'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Obtener datos para los selects del modal
        $clientes = Cliente::orderBy('cliente')->get();
        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->get();
        $empleados = Empleado::orderBy('nombre')->get();
        $transportes = Transporte::orderBy('transporte')->get();

        return view('Logistica.matriz-seguimiento', compact('operaciones', 'clientes', 'agentesAduanales', 'empleados', 'transportes'));
    }

    public function create()
    {
        // Obtener datos para los selects
        $clientes = Cliente::orderBy('cliente')->get();
        $agentesAduanales = AgenteAduanal::orderBy('agente_aduanal')->get();
        $ejecutivos = Empleado::where('area', 'LIKE', '%Logist%')->orderBy('nombre')->get();
        
        return response()->json([
            'clientes' => $clientes,
            'agentesAduanales' => $agentesAduanales,
            'ejecutivos' => $ejecutivos,
            'tiposOperacion' => ['Aerea', 'Terrestre', 'Maritima', 'Ferrocarril'],
            'operaciones' => ['Exportacion', 'Importacion'],
            'statusOptions' => ['In Process', 'Done', 'Out of Metric']
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ejecutivo_empleado_id' => 'required|exists:empleados,id',
            'operacion' => 'required|string|max:255',
            'operacion_tipo' => 'required|in:Exportacion,Importacion',
            'fecha_embarque' => 'required|date',
            // Agregar más validaciones según sea necesario
        ]);

        // Manejar cliente (existente o nuevo)
        if ($request->filled('cliente_existente_id')) {
            $clienteId = $request->cliente_existente_id;
        } elseif ($request->filled('cliente_nuevo')) {
            $cliente = Cliente::create([
                'cliente' => $request->cliente_nuevo,
                'ejecutivo_asignado_id' => $request->ejecutivo_empleado_id,
            ]);
            $clienteId = $cliente->id;
        } else {
            $clienteId = null;
        }

        // Manejar agente aduanal
        if ($request->filled('agente_existente_id')) {
            $agenteId = $request->agente_existente_id;
        } elseif ($request->filled('agente_nuevo')) {
            $agente = AgenteAduanal::create([
                'agente_aduanal' => $request->agente_nuevo,
            ]);
            $agenteId = $agente->id;
        } else {
            $agenteId = null;
        }

        // Manejar transporte
        $transporteId = null;
        if ($request->filled('transporte_nuevo') && $request->filled('tipo_operacion_enum')) {
            $transporte = Transporte::create([
                'transporte' => $request->transporte_nuevo,
                'tipo_operacion' => $request->tipo_operacion_enum,
            ]);
            $transporteId = $transporte->id;
        }

        // Calcular resultado automáticamente
        $resultado = null;
        if ($request->filled('fecha_arribo_aduana') && $request->filled('fecha_modulacion')) {
            $fechaArribo = \Carbon\Carbon::parse($request->fecha_arribo_aduana);
            $fechaModulacion = \Carbon\Carbon::parse($request->fecha_modulacion);
            $resultado = $fechaArribo->diffInDays($fechaModulacion);
        }

        // Crear la operación
        $operacion = OperacionLogistica::create([
            'ejecutivo_empleado_id' => $request->ejecutivo_empleado_id,
            'cliente_id' => $clienteId,
            'agente_aduanal_id' => $agenteId,
            'transporte_id' => $transporteId,
            'operacion' => $request->operacion,
            'operacion_tipo' => $request->operacion_tipo,
            'proveedor_o_cliente' => $request->proveedor_o_cliente,
            'fecha_embarque' => $request->fecha_embarque,
            'no_factura' => $request->no_factura,
            'tipo_operacion_enum' => $request->tipo_operacion_enum,
            'clave' => $request->clave,
            'referencia_interna' => $request->referencia_interna,
            'aduana' => $request->aduana,
            'referencia_aa' => $request->referencia_aa,
            'no_pedimento' => $request->no_pedimento,
            'fecha_arribo_aduana' => $request->fecha_arribo_aduana,
            'guia_bl' => $request->guia_bl,
            'status_enum' => $request->status_enum,
            'fecha_modulacion' => $request->fecha_modulacion,
            'fecha_arribo_planta' => $request->fecha_arribo_planta,
            'resultado' => $resultado,
            'target' => $request->target,
            'pendientes_pos_operaciones' => $request->boolean('pendientes_pos_operaciones'),
            'comentarios' => $request->comentarios,
        ]);

        // Calcular días en tránsito
        $operacion->calcularDiasTransito();
        $operacion->save();

        return response()->json([
            'success' => true,
            'message' => 'Operación creada exitosamente',
            'operacion' => $operacion->load(['ejecutivo', 'cliente', 'agenteAduanal', 'transporte'])
        ]);
    }

    public function getTransportesPorTipo(Request $request)
    {
        $transportes = Transporte::where('tipo_operacion', $request->tipo)
            ->orderBy('transporte')
            ->get();

        return response()->json($transportes);
    }

    public function storeCliente(Request $request)
    {
        try {
            $request->validate([
                'cliente' => 'required|string|max:255|unique:clientes,cliente'
            ]);

            $cliente = Cliente::create([
                'cliente' => $request->cliente
            ]);

            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'message' => 'Cliente creado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeAgente(Request $request)
    {
        try {
            $request->validate([
                'agente_aduanal' => 'required|string|max:255|unique:agentes_aduanales,agente_aduanal'
            ]);

            $agente = AgenteAduanal::create([
                'agente_aduanal' => $request->agente_aduanal
            ]);

            return response()->json([
                'success' => true,
                'agente' => $agente,
                'message' => 'Agente aduanal creado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el agente aduanal: ' . $e->getMessage()
            ], 500);
        }
    }
}
