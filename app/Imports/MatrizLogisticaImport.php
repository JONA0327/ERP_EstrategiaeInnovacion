<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\Sheets\MatrizHojaUnoImport;

class MatrizLogisticaImport implements WithMultipleSheets
{
    protected $empleadoId;

    public function __construct($empleadoId = null)
    {
        $this->empleadoId = $empleadoId;
    }

    public function sheets(): array
    {
        // Solo lee la hoja con índice 0 (la primera: "Matriz de Operación")
        return [
            0 => new MatrizHojaUnoImport($this->empleadoId),
        ];
    }
}
