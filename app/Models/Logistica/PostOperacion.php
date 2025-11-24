<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostOperacion extends Model
{
    use HasFactory;

    protected $table = 'post_operaciones';

    protected $fillable = [
        'post_operacion',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function operaciones()
    {
        return $this->hasMany(OperacionLogistica::class);
    }
}
