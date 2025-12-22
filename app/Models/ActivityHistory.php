<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    // Indicamos que no use los timestamps por defecto (created_at, updated_at)
    // porque nosotros usamos 'fecha_cambio' manual en la migración.
    public $timestamps = false;

    protected $fillable = [
        'activity_id',
        'user_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    // Relación para saber quién hizo el cambio
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación de vuelta a la actividad
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}