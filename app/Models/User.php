<?php

namespace App\Models;

use App\Models\Sistemas_IT\Ticket;
use App\Models\Empleado;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'approved_at',
        'rejected_at',
        // 'area', // Descomenta si agregas la columna a la tabla users
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Helper privado para normalizar texto (minusculas, sin acentos)
     */
    private function normalizeString(?string $value): string
    {
        if (empty($value))
            return '';
        // Convierte a minúsculas y reemplaza acentos comunes
        $value = mb_strtolower(trim($value), 'UTF-8');
        $replacements = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
        return strtr($value, $replacements);
    }

    /**
     * Determina si el usuario está aprobado para acceder al sistema.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Palabras clave normalizadas (sin acentos, minúsculas)
    public const KEYWORDS_RH = ['rh', 'recursos humanos', 'capital humano', 'administracion rh', 'human resources'];
    public const KEYWORDS_LOGISTICA = ['logistica', 'operaciones', 'aduana', 'trafico', 'comercio exterior'];

    /**
     * Verificar si el usuario tiene un rol/puesto que coincida con las palabras clave.
     */
    public function hasPositionLike(array $keywords): bool
    {
        // 1. Valores a checar (Rol, Area)
        $valores = [$this->role, $this->area];

        // 2. Si tiene empleado, agregar sus datos
        if ($this->empleado) {
            $valores = array_merge($valores, [
                $this->empleado->departamento,
                $this->empleado->puesto,
                $this->empleado->posicion,
                $this->empleado->area
            ]);
        }

        // 3. Normalizar y verificar
        foreach ($valores as $valor) {
            if (empty($valor))
                continue;

            $normalizado = $this->normalizeString($valor);

            foreach ($keywords as $keyword) {
                // Coincidencia exacta o parcial segura
                if (str_contains($normalizado, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario es de RH
     */
    public function isRh(): bool
    {
        return $this->hasPositionLike(self::KEYWORDS_RH);
    }

    /**
     * Verificar si el usuario es de Logística
     */
    public function isLogistica(): bool
    {
        return $this->hasPositionLike(self::KEYWORDS_LOGISTICA);
    }

    /**
     * Obtener información del panel según la posición del usuario
     * Retorna un array con 'route', 'label' y 'available'
     */
    public function getPanelInfo(): array
    {
        // Obtener posición normalizada
        $posicion = $this->normalizeString($this->empleado->posicion ?? '');

        // Determinar panel según posición (orden específico para evitar coincidencias parciales)
        // Logística: cualquier usuario con posición "logistica" puede acceder (no requiere admin)
        if (str_contains($posicion, 'logistica') || str_contains($posicion, 'operaciones') || str_contains($posicion, 'aduana')) {
            return [
                'available' => true,
                'route' => route('logistica.index'),
                'label' => 'Panel Logística',
            ];
        }

        // Los demás paneles SÍ requieren ser admin
        if (!$this->isAdmin()) {
            return [
                'available' => false,
                'route' => null,
                'label' => null,
            ];
        }

        // IT/TI con búsqueda más específica
        if (str_contains($posicion, ' ti') || str_contains($posicion, 'ti ') || $posicion === 'ti' || $posicion === 'it' || str_contains($posicion, 'sistemas')) {
            return [
                'available' => true,
                'route' => route('admin.dashboard'),
                'label' => 'Panel IT',
            ];
        }

        // RH
        if (str_contains($posicion, 'administracion rh')) {
            return [
                'available' => true,
                'route' => route('recursos-humanos.index'),
                'label' => 'Panel RH',
            ];
        }

        // Si es admin pero no tiene posición específica (IT, RH o Logística), NO mostrar panel
        return [
            'available' => false,
            'route' => null,
            'label' => null,
        ];
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Relación con tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Relación con empleado
     */
    public function empleado()
    {
        return $this->hasOne(Empleado::class);
    }
}