<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedimento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'pedimentos';

    /**
     * Atributos que se pueden asignar de forma masiva
     */
    protected $fillable = [
        'categoria',
        'subcategoria', 
        'clave',
        'descripcion'
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'categoria' => 'string',
        'subcategoria' => 'string',
        'clave' => 'string',
        'descripcion' => 'string'
    ];

    /**
     * Scope para buscar por clave
     */
    public function scopePorClave($query, $clave)
    {
        return $query->where('clave', 'like', "%{$clave}%");
    }

    /**
     * Scope para buscar por descripción
     */
    public function scopePorDescripcion($query, $descripcion)
    {
        return $query->where('descripcion', 'like', "%{$descripcion}%");
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para filtrar por subcategoría
     */
    public function scopePorSubcategoria($query, $subcategoria)
    {
        return $query->where('subcategoria', $subcategoria);
    }

    /**
     * Obtener todas las categorías únicas
     */
    public static function getCategorias()
    {
        return self::whereNotNull('categoria')
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Obtener subcategorías por categoría
     */
    public static function getSubcategoriasPorCategoria($categoria)
    {
        return self::where('categoria', $categoria)
            ->whereNotNull('subcategoria')
            ->distinct()
            ->pluck('subcategoria')
            ->filter()
            ->sort()
            ->values();
    }
}