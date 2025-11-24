<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transporte extends Model
{
    use HasFactory;

    protected $table = 'transportes';

    protected $fillable = [
        'transporte',
        'tipo_operacion',
    ];

    public function operaciones()
    {
        return $this->hasMany(OperacionLogistica::class, 'transporte_id');
    }

    public function scopePorTipoOperacion($query, $tipo)
    {
        return $query->where('tipo_operacion', $tipo);
    }
}
