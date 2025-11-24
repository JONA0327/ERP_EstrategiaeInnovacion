<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Empleado;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'cliente',
        'ejecutivo_asignado_id',
    ];

    public function ejecutivoAsignado()
    {
        return $this->belongsTo(Empleado::class, 'ejecutivo_asignado_id');
    }

    public function operaciones()
    {
        return $this->hasMany(OperacionLogistica::class, 'cliente_id');
    }
}
