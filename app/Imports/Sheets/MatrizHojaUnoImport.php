<?php

namespace App\Imports\Sheets;

use App\Models\Logistica\OperacionLogistica;
use App\Models\Logistica\CampoPersonalizadoMatriz;
use App\Models\Logistica\ValorCampoPersonalizado;
use App\Models\Logistica\ColumnaVisibleEjecutivo;
use App\Models\Empleado;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MatrizHojaUnoImport implements ToCollection, WithHeadingRow
{
    protected $empleadoId;
    protected $columnasActivadas = [];
    protected $camposPersonalizadosCreados = [];

    public function __construct($empleadoId = null)
    {
        $this->empleadoId = $empleadoId;
    }

    /**
     * Mapa de columnas: "Encabezado Excel (slug)" => "Columna BD"
     * Los encabezados se convierten a slug automáticamente (ej: "Invoice Number" -> "invoice_number")
     */
    private function getMapaColumnas(): array
    {
        return [
            // Columnas principales de la BD
            'no_folio'               => 'folio',
            'folio'                  => 'folio',
            'process'                => 'operacion',
            'operacion'              => 'operacion',
            'customer'               => 'cliente',
            'cliente'                => 'cliente',
            'customer_broker'        => 'agente_aduanal',
            'agente_aduanal'         => 'agente_aduanal',
            'modalidad_previo'       => 'tipo_previo',
            'tipo_previo'            => 'tipo_previo',
            'invoice_number'         => 'no_factura',
            'no_factura'             => 'no_factura',
            'factura'                => 'no_factura',
            'supplier_name'          => 'proveedor',
            'proveedor'              => 'proveedor',
            'customs_mx'             => 'aduana',
            'aduana'                 => 'aduana',
            'in_charge'              => 'in_charge',
            'responsable'            => 'in_charge',
            'ejecutivo'              => 'ejecutivo',
            'freight'                => 'tipo_operacion_enum',
            'tipo_operacion'         => 'tipo_operacion_enum',
            'trackingbl'             => 'guia_bl',
            'tracking_bl'            => 'guia_bl',
            'guia_bl'                => 'guia_bl',
            'bl'                     => 'guia_bl',
            'shipp_date_etd'         => 'fecha_etd',
            'fecha_etd'              => 'fecha_etd',
            'etd'                    => 'fecha_etd',
            'shipp_date_zarpe'       => 'fecha_zarpe',
            'fecha_zarpe'            => 'fecha_zarpe',
            'zarpe'                  => 'fecha_zarpe',
            'arriving_date'          => 'fecha_arribo_aduana',
            'eta'                    => 'fecha_arribo_aduana',
            'fecha_eta'              => 'fecha_arribo_aduana',
            'fecha_arribo'           => 'fecha_arribo_aduana',
            'salida_de_aduana'       => 'fecha_modulacion',
            'fecha_salida_aduana'    => 'fecha_modulacion',
            'eta_planta_origen'      => 'fecha_arribo_planta',
            'fecha_arribo_planta'    => 'fecha_arribo_planta',
            'fecha_pedimento_pagado' => 'fecha_pago_pedimento',
            'status'                 => 'status_manual',
            'estatus'                => 'status_manual',
            'pedimento'              => 'no_pedimento',
            'no_pedimento'           => 'no_pedimento',
            'ref'                    => 'referencia_cliente',
            'referencia'             => 'referencia_cliente',
            'referencia_cliente'     => 'referencia_cliente',
            'mail_subject'           => 'mail_subject',
            'asunto_correo'          => 'mail_subject',
            'pedimento_en_carpeta'   => 'pedimento_en_carpeta',
            'transporte'             => 'transporte',
            'referencia_aa'          => 'referencia_aa',
            'referencia_interna'     => 'referencia_interna',
            'clave'                  => 'clave',
            'puerto_salida'          => 'puerto_salida',
            'port_of_origin'         => 'puerto_salida',
            'tipo_carga'             => 'tipo_carga',
            'load_type'              => 'tipo_carga',
            'tipo_incoterm'          => 'tipo_incoterm',
            'incoterm'               => 'tipo_incoterm',
            'proveedor_o_cliente'    => 'proveedor_o_cliente',
            'supplier_customer'      => 'proveedor_o_cliente',
            'comentarios'            => 'comentarios',
            'comments'               => 'comentarios',
            'target'                 => 'target',
            'dias_transito'          => 'dias_transito',
            'resultado'              => 'resultado',
            'fecha_embarque'         => 'fecha_embarque',
        ];
    }

    /**
     * Columnas opcionales que pueden activarse para un ejecutivo
     */
    private function getColumnasOpcionales(): array
    {
        return [
            'tipo_carga',
            'tipo_incoterm',
            'puerto_salida',
            'in_charge',
            'proveedor',
            'tipo_previo',
            'fecha_etd',
            'fecha_zarpe',
            'pedimento_en_carpeta',
            'referencia_cliente',
            'mail_subject',
        ];
    }

    public function collection(Collection $rows)
    {
        $mapaColumnas = $this->getMapaColumnas();
        $columnasOpcionales = $this->getColumnasOpcionales();

        // Obtenemos todos los campos personalizados existentes para no hacer queries en cada fila
        $camposPersonalizadosDb = CampoPersonalizadoMatriz::all();
        $camposPorSlug = [];
        foreach ($camposPersonalizadosDb as $campo) {
            $slug = Str::slug($campo->nombre, '_');
            $camposPorSlug[$slug] = $campo;
        }

        $operacionesImportadas = 0;
        $errores = [];

        foreach ($rows as $index => $row) {
            try {
                // Validar que la fila tenga datos mínimos (Folio)
                $folio = $row['no_folio'] ?? $row['folio'] ?? null;
                if (empty($folio)) {
                    continue;
                }

                // Datos para la tabla principal OperacionLogistica
                $datosPrincipales = [];
                
                // Datos que no encajan en la tabla principal (potenciales campos personalizados)
                $datosExtra = [];

                foreach ($row as $header => $valor) {
                    if (empty($header) || $valor === null) continue;
                    
                    $headerSlug = Str::slug($header, '_');
                    
                    // 1. Si está en nuestro mapa, va a la tabla principal
                    if (array_key_exists($headerSlug, $mapaColumnas)) {
                        $columnaDb = $mapaColumnas[$headerSlug];
                        $valorTransformado = $this->transformarValor($columnaDb, $valor);
                        
                        if ($valorTransformado !== null && $valorTransformado !== '') {
                            $datosPrincipales[$columnaDb] = $valorTransformado;
                        }

                        // Registrar columna opcional usada
                        if (in_array($columnaDb, $columnasOpcionales) && !empty($valorTransformado)) {
                            $this->columnasActivadas[$columnaDb] = true;
                        }
                    } 
                    // 2. Si NO está en el mapa, verificar si es campo personalizado
                    else {
                        if (!empty($valor)) {
                            $datosExtra[$headerSlug] = [
                                'header_original' => $header,
                                'valor' => $valor
                            ];
                        }
                    }
                }

                // Aseguramos que tenga folio
                if (empty($datosPrincipales['folio'])) {
                    $datosPrincipales['folio'] = $folio;
                }

                // --- PASO A: Guardar/Actualizar Operación Logística ---
                $operacion = OperacionLogistica::updateOrCreate(
                    ['folio' => $datosPrincipales['folio']], 
                    $datosPrincipales
                );

                // --- PASO B: Guardar Campos Personalizados ---
                foreach ($datosExtra as $slugHeader => $data) {
                    // Buscar si existe un campo personalizado con ese nombre
                    $campo = $camposPorSlug[$slugHeader] ?? null;

                    // Si no existe, lo creamos automáticamente como tipo texto
                    if (!$campo) {
                        $campo = $this->crearCampoPersonalizado($data['header_original'], $slugHeader);
                        $camposPorSlug[$slugHeader] = $campo;
                    }

                    if ($campo) {
                        ValorCampoPersonalizado::updateOrCreate(
                            [
                                'operacion_logistica_id' => $operacion->id,
                                'campo_personalizado_id' => $campo->id,
                            ],
                            [
                                'valor' => $data['valor']
                            ]
                        );
                    }
                }

                $operacionesImportadas++;

            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                Log::error('Error importando fila', [
                    'fila' => $index + 2,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // --- PASO C: Activar columnas opcionales para el ejecutivo ---
        if ($this->empleadoId) {
            $this->activarColumnasParaEjecutivo();
            $this->asignarCamposPersonalizadosAEjecutivo();
        }

        Log::info('Importación completada', [
            'operaciones_importadas' => $operacionesImportadas,
            'columnas_activadas' => array_keys($this->columnasActivadas),
            'campos_personalizados_creados' => $this->camposPersonalizadosCreados,
            'errores' => count($errores)
        ]);
    }

    /**
     * Crea un campo personalizado nuevo
     */
    private function crearCampoPersonalizado($nombreOriginal, $slug)
    {
        try {
            // Determinar el tipo basado en el nombre
            $tipo = $this->inferirTipoCampo($nombreOriginal);

            $campo = CampoPersonalizadoMatriz::firstOrCreate(
                ['nombre' => $nombreOriginal],
                [
                    'tipo' => $tipo,
                    'activo' => true,
                    'requerido' => false,
                    'orden' => CampoPersonalizadoMatriz::max('orden') + 1,
                ]
            );

            $this->camposPersonalizadosCreados[] = [
                'id' => $campo->id,
                'nombre' => $nombreOriginal,
                'tipo' => $tipo
            ];

            return $campo;
        } catch (\Exception $e) {
            Log::error('Error creando campo personalizado', [
                'nombre' => $nombreOriginal,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Infiere el tipo de campo basado en el nombre
     */
    private function inferirTipoCampo($nombre)
    {
        $nombreLower = strtolower($nombre);

        if (Str::contains($nombreLower, ['fecha', 'date', 'eta', 'etd'])) {
            return 'fecha';
        }
        if (Str::contains($nombreLower, ['email', 'correo', 'mail'])) {
            return 'email';
        }
        if (Str::contains($nombreLower, ['telefono', 'phone', 'tel'])) {
            return 'telefono';
        }
        if (Str::contains($nombreLower, ['url', 'link', 'enlace', 'web'])) {
            return 'url';
        }
        if (Str::contains($nombreLower, ['monto', 'precio', 'costo', 'total', 'amount', 'price'])) {
            return 'moneda';
        }
        if (Str::contains($nombreLower, ['cantidad', 'numero', 'qty', 'quantity', 'num'])) {
            return 'numero';
        }
        if (Str::contains($nombreLower, ['descripcion', 'comentario', 'observacion', 'notas', 'notes'])) {
            return 'descripcion';
        }
        if (Str::contains($nombreLower, ['si', 'no', 'activo', 'yes', 'active', 'enabled'])) {
            return 'booleano';
        }

        return 'texto';
    }

    /**
     * Activa las columnas opcionales usadas para el ejecutivo seleccionado
     */
    private function activarColumnasParaEjecutivo()
    {
        if (!$this->empleadoId || empty($this->columnasActivadas)) {
            return;
        }

        foreach (array_keys($this->columnasActivadas) as $columna) {
            ColumnaVisibleEjecutivo::updateOrCreate(
                [
                    'empleado_id' => $this->empleadoId,
                    'columna' => $columna,
                ],
                [
                    'visible' => true,
                ]
            );

            Log::info("Columna opcional activada", [
                'empleado_id' => $this->empleadoId,
                'columna' => $columna
            ]);
        }
    }

    /**
     * Asigna los campos personalizados creados al ejecutivo
     */
    private function asignarCamposPersonalizadosAEjecutivo()
    {
        if (!$this->empleadoId || empty($this->camposPersonalizadosCreados)) {
            return;
        }

        $empleado = Empleado::find($this->empleadoId);
        if (!$empleado) {
            return;
        }

        foreach ($this->camposPersonalizadosCreados as $campoData) {
            $campo = CampoPersonalizadoMatriz::find($campoData['id']);
            if ($campo) {
                // Verificar si ya está asignado
                if (!$campo->ejecutivos()->where('empleado_id', $this->empleadoId)->exists()) {
                    $campo->ejecutivos()->attach($this->empleadoId);
                    
                    Log::info("Campo personalizado asignado a ejecutivo", [
                        'campo_id' => $campo->id,
                        'campo_nombre' => $campo->nombre,
                        'empleado_id' => $this->empleadoId
                    ]);
                }
            }
        }
    }

    /**
     * Limpia y formatea valores según el tipo de columna
     */
    private function transformarValor($columna, $valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // Transformar fechas
        if (Str::contains($columna, ['fecha', 'date']) || in_array($columna, ['fecha_etd', 'fecha_zarpe', 'fecha_arribo_aduana', 'fecha_modulacion', 'fecha_arribo_planta', 'fecha_embarque'])) {
            return $this->transformarFecha($valor);
        }

        // Transformar tipo de operación (freight)
        if ($columna === 'tipo_operacion_enum') {
            return $this->transformarTipoOperacion($valor);
        }

        // Transformar status
        if ($columna === 'status_manual') {
            return $this->transformarStatus($valor);
        }

        // Transformar booleanos
        if ($columna === 'pedimento_en_carpeta') {
            return $this->transformarBooleano($valor);
        }

        // Limpiar espacios
        if (is_string($valor)) {
            return trim($valor);
        }

        return $valor;
    }

    /**
     * Transforma valores de fecha
     */
    private function transformarFecha($valor)
    {
        if (empty($valor)) return null;
        
        try {
            // Si es un número de Excel (ej. 45061)
            if (is_numeric($valor)) {
                return Date::excelToDateTimeObject($valor)->format('Y-m-d');
            }
            // Si es texto
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Error transformando fecha', ['valor' => $valor, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Transforma tipo de operación (Aerea, Terrestre, Maritima, Ferrocarril)
     */
    private function transformarTipoOperacion($valor)
    {
        $valorUpper = strtoupper(trim($valor));
        
        $mapeo = [
            'SEA' => 'Maritima',
            'MARITIME' => 'Maritima',
            'MARITIMA' => 'Maritima',
            'MAR' => 'Maritima',
            'AIR' => 'Aerea',
            'AEREA' => 'Aerea',
            'AEREO' => 'Aerea',
            'LAND' => 'Terrestre',
            'TERRESTRE' => 'Terrestre',
            'GROUND' => 'Terrestre',
            'RAIL' => 'Ferrocarril',
            'FERROCARRIL' => 'Ferrocarril',
            'TRAIN' => 'Ferrocarril',
        ];

        return $mapeo[$valorUpper] ?? $valor;
    }

    /**
     * Transforma valores de status
     */
    private function transformarStatus($valor)
    {
        $valorUpper = strtoupper(trim($valor));
        
        $mapeo = [
            'IN PROCESS' => 'In Process',
            'EN PROCESO' => 'In Process',
            'PENDIENTE' => 'In Process',
            'DONE' => 'Done',
            'COMPLETADO' => 'Done',
            'TERMINADO' => 'Done',
            'OUT OF METRIC' => 'Out of Metric',
            'FUERA DE METRICA' => 'Out of Metric',
        ];

        return $mapeo[$valorUpper] ?? 'In Process';
    }

    /**
     * Transforma valores booleanos
     */
    private function transformarBooleano($valor)
    {
        if (is_bool($valor)) return $valor;
        
        $valorUpper = strtoupper(trim($valor));
        
        return in_array($valorUpper, ['SI', 'SÍ', 'YES', '1', 'TRUE', 'VERDADERO', 'X']);
    }

    /**
     * Obtener columnas activadas durante la importación
     */
    public function getColumnasActivadas()
    {
        return array_keys($this->columnasActivadas);
    }

    /**
     * Obtener campos personalizados creados durante la importación
     */
    public function getCamposPersonalizadosCreados()
    {
        return $this->camposPersonalizadosCreados;
    }
}
