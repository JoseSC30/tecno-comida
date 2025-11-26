<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'rol_id';
    public $timestamps = false;

    protected $fillable = ['rol_nombre',];
    protected $appends = ['name', 'id'];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['rol_id'] ?? null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['rol_nombre'] ?? null,
            set: fn ($value) => ['rol_nombre' => $value],
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id', 'rol_id');
    }

    public const ADMIN = 'Administrador';
    public const CAJERO = 'Cajero';
    public const COCINERO = 'Cocinero';
    public const MESERO = 'Mesero';
    public const CLIENTE = 'Cliente';
    public const VENDEDOR = self::CAJERO; // compatibilidad con código existente
}
