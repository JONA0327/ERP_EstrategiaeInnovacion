<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacitacion extends Model
{
    use HasFactory;

    protected $table = 'capacitaciones';

    protected $fillable = [
        'titulo',
        'descripcion',
        'archivo_path',
        'thumbnail_path',
        'subido_por',
        'activo',
        'youtube_url',
    ];

    /**
     * Determina si la capacitación es un video de YouTube.
     */
    public function isYoutube()
    {
        return !empty($this->youtube_url);
    }

    /**
     * Extrae el ID del video de YouTube desde la URL.
     * Soporta formatos: youtu.be/ID, youtube.com/watch?v=ID, youtube.com/embed/ID
     */
    public function getYoutubeId()
    {
        if (!$this->youtube_url)
            return null;

        // Patrón regex para extraer ID
        // Patrón regex mejorado para soportar Shorts, Embeds y URLs estándar
        // Explicación:
        // (?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/) -> Prefijos válidos
        // ([^"&?\/\s]{11}) -> ID de 11 caracteres
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';

        if (preg_match($pattern, $this->youtube_url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class , 'subido_por');
    }

    public function adjuntos()
    {
        return $this->hasMany(CapacitacionAdjunto::class);
    }
}