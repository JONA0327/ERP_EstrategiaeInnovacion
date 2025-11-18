<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'correo',
        'area',
        'posicion',
        'telefono',
        'direccion',
        'correo_personal',
        'foto_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
